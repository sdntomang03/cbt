<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Question;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PublicExamController extends Controller
{
    /**
     * Menampilkan Halaman Ujian Publik
     */
    public function publicRun(Exam $exam)
    {
        // 1. BLOKIR JIKA UJIAN BUKAN UNTUK PUBLIK
        abort_if(! $exam->is_public, 403, 'AKSES DITOLAK: Ujian ini hanya untuk siswa internal yang terdaftar.');

        // 2. CEK TOKEN UJIAN (Jika fitur token diaktifkan)
        if ($exam->require_token && ! session()->has('public_verified_exam_'.$exam->id)) {
            // Asumsi Anda memiliki rute 'public.exam.verify' untuk memasukkan token
            return redirect()->route('public.exam.verify', $exam->id)
                ->with('error', 'Akses Ditolak! Silakan masukkan Token Ujian terlebih dahulu.');
        }

        $now = Carbon::now('Asia/Jakarta');
        $sessionKey = 'public_exam_state_'.$exam->id;

        // 3. INISIALISASI STATE UJIAN DI SESSION (Khusus Guest)
        if (! session()->has($sessionKey)) {
            session()->put($sessionKey, [
                'started_at' => $now->toDateTimeString(),
                'status' => 'ongoing',
                'is_locked' => false,
                'violation_count' => 0,
                'answers' => [],
                'flags' => [],
                'finished_at' => null,
            ]);
        }

        $state = session()->get($sessionKey);

        // 4. RESPON UNTUK AJAX POLLING (Pengecekan berkala dari frontend)
        if (request()->ajax()) {
            return response()->json([
                'status' => $state['status'],
                'is_locked' => (bool) $state['is_locked'],
            ]);
        }

        // 5. BLOKIR JIKA TERKUNCI ATAU SELESAI
        if ($state['is_locked']) {
            session()->forget('public_verified_exam_'.$exam->id);

            return redirect()->route('welcome')->with('error', 'AKSES DITOLAK: Ujian Anda telah dikunci karena melanggar aturan keamanan layar.');
        }
        if ($state['status'] === 'completed' || $state['finished_at'] !== null) {
            session()->forget('public_verified_exam_'.$exam->id);

            return redirect()->route('welcome')->with('info', 'Ujian ini telah ditutup atau sudah Anda kumpulkan.');
        }

        // 6. PERHITUNGAN DEADLINE WAKTU GUEST
        $startTime = Carbon::parse($state['started_at'])->timezone('Asia/Jakarta');
        $duration = (int) $exam->duration_minutes;

        // Batas waktu berdasarkan durasi personal guest
        $deadlinePersonal = $startTime->copy()->addMinutes($duration);

        // Batas waktu berdasarkan jadwal tutup ujian (jika ada)
        if ($exam->end_time) {
            $deadlineSession = Carbon::parse($exam->end_time)->timezone('Asia/Jakarta');
            $realDeadline = $deadlinePersonal->min($deadlineSession);
        } else {
            $realDeadline = $deadlinePersonal;
        }

        $timeLeftSeconds = $now->diffInSeconds($realDeadline, false);

        // 7. TOLERANSI & PAKSA SELESAI
        if ($timeLeftSeconds <= 0 && $timeLeftSeconds > -60) {
            $timeLeftSeconds = 60; // Beri waktu 60 detik untuk auto-submit
        } elseif ($timeLeftSeconds <= -60) {
            return $this->publicFinish($exam); // Paksa kumpulkan jika sudah sangat lewat
        }

        // 8. SIAPKAN DATA UNTUK VIEW
        $questionIds = Question::where('exam_id', $exam->id)->pluck('id')->toArray();

        // Objek tiruan (mock) agar Alpine.js tidak error mencari properti pivot
        $pivotMock = new \stdClass;
        $pivotMock->status = $state['status'];
        $pivotMock->is_locked = $state['is_locked'];
        $pivotMock->violation_count = $state['violation_count'];

        $config = [
            'random_question' => $exam->random_question ?? false,
            'random_answer' => $exam->random_answer ?? false,
            'enable_violation' => $exam->enable_violation ?? true,
            'max_tolerances' => $exam->max_tolerances ?? 3,
        ];

        return view('public.exams.run', [
            'exam' => $exam,
            'questionIds' => $questionIds,
            'config' => $config,
            'timeLeftSeconds' => (int) $timeLeftSeconds,
            'existingAnswers' => $state['answers'] ?? [],
            'flags' => $state['flags'] ?? [],
            'pivot' => $pivotMock,
        ]);
    }

    /**
     * Menyimpan Jawaban Ujian Publik (Via AJAX)
     */
    public function publicSaveAnswer(Request $request, Exam $exam)
    {
        abort_if(! $exam->is_public, 403);

        $sessionKey = 'public_exam_state_'.$exam->id;
        $state = session()->get($sessionKey);

        if ($state && ! $state['is_locked'] && $state['status'] === 'ongoing') {
            $qId = $request->question_id;

            // Simpan atau perbarui jawaban
            if ($request->has('answer')) {
                $state['answers'][$qId] = $request->answer;
            }

            // Simpan status ragu-ragu (flags)
            $isDoubtful = filter_var($request->is_doubtful, FILTER_VALIDATE_BOOLEAN);
            if ($isDoubtful && ! in_array($qId, $state['flags'])) {
                $state['flags'][] = $qId;
            } elseif (! $isDoubtful && in_array($qId, $state['flags'])) {
                $state['flags'] = array_diff($state['flags'], [$qId]);
                $state['flags'] = array_values($state['flags']); // Re-index array
            }

            // Kembalikan ke session
            session()->put($sessionKey, $state);

            return response()->json(['success' => true]);
        }

        return response()->json(['error' => 'Sesi tidak valid atau ujian terkunci'], 403);
    }

    /**
     * Mencatat Pelanggaran Publik (Via AJAX)
     */
    public function publicViolation(Request $request, Exam $exam)
    {
        abort_if(! $exam->is_public, 403);

        $sessionKey = 'public_exam_state_'.$exam->id;
        $state = session()->get($sessionKey);

        if ($state && ! $state['is_locked']) {
            $state['violation_count'] += 1;
            $max = $exam->max_tolerances ?? 3;

            // Kunci ujian jika melampaui batas
            if ($state['violation_count'] >= $max) {
                $state['is_locked'] = true;
            }

            session()->put($sessionKey, $state);

            return response()->json([
                'violation_count' => $state['violation_count'],
                'max_tolerances' => $max,
                'is_locked' => $state['is_locked'],
            ]);
        }

        return response()->json(['error' => 'Sesi tidak valid'], 400);
    }

    /**
     * Menyelesaikan dan Mengumpulkan Ujian Publik
     */
    public function publicFinish(Exam $exam)
    {
        abort_if(! $exam->is_public, 403);

        $sessionKey = 'public_exam_state_'.$exam->id;
        $state = session()->get($sessionKey);

        if ($state && $state['status'] !== 'completed') {
            $state['status'] = 'completed';
            $state['finished_at'] = Carbon::now('Asia/Jakarta')->toDateTimeString();

            // Simpan status selesai ke session
            session()->put($sessionKey, $state);

            // Opsional: Hapus sesi autentikasi token jika ingin memaksa mereka minta token baru jika mencoba lagi
            // session()->forget('public_verified_exam_' . $exam->id);

            return redirect()->route('welcome')->with('success', 'Selamat! Ujian publik telah berhasil dikumpulkan.');
        }

        return redirect()->route('welcome')->with('info', 'Ujian ini sudah diselesaikan sebelumnya.');
    }

    public function index()
    {
        // Ambil semua ujian yang is_public = true, urutkan dari yang terbaru
        // Gunakan withCount('questions') agar kita bisa menampilkan jumlah soalnya
        $publicExams = Exam::where('is_public', true)
            ->withCount('questions')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('public.exams.index', compact('publicExams'));
    }
}
