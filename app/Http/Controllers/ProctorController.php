<?php

namespace App\Http\Controllers;

use App\Exports\ButirSoalExport;
use App\Models\DetailNilai;
use App\Models\ExamSession;
use App\Models\ExamSessionUser;
use App\Models\ExamAttempt;
use App\Models\School;
use App\Models\StudentAnswer;
use App\Models\User;
use App\Services\AttemptScoringService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ProctorController extends Controller
{
    /**
     * Menampilkan daftar jadwal ujian yang bisa diawasi hari ini.
     */
    public function index()
    {
        $user = auth()->user();

        // Mulai Query dengan eager loading relasi 'exam'
        $query = ExamSession::with('exam')->orderBy('start_time', 'asc');

        // =========================================================
        // FILTER HAK AKSES PENGAWAS (PROCTOR)
        // =========================================================
        // Jika bukan admin, batasi sesi ujian yang tampil
        if (! $user->hasRole('admin')) {
            $query->whereHas('exam', function ($q) use ($user) {
                // Tampilkan jika user adalah pembuat ujian
                $q->where('teacher_id', $user->id)
                  // ATAU jika user terdaftar sebagai undangan di ujian tersebut
                    ->orWhereHas('invitedTeachers', function ($subQuery) use ($user) {
                        $subQuery->where('user_id', $user->id);
                    });
            });
        }

        $sessions = $query->get();

        return view('proctor.index', compact('sessions'));
    }

    /**
     * Halaman Monitoring (Dashboard Pengawas) untuk sesi tertentu.
     */
    public function show(Request $request, ExamSession $examSession)
    {
        // 1. --- [AUTO-SWEEP / PENYAPU OTOMATIS] ---
        // Mencari siswa offline yang waktunya sudah habis dan menutup paksa ujiannya.
        $now = Carbon::now('Asia/Jakarta');
        $durationMinutes = (int) $examSession->exam->duration_minutes;
        $sessionEnd = Carbon::parse($examSession->end_time)->timezone('Asia/Jakarta');

        $ongoingStudents = $examSession->students()->wherePivot('status', 'ongoing')->get();

        foreach ($ongoingStudents as $student) {
            if ($student->pivot->started_at) {
                $startedAt = Carbon::parse($student->pivot->started_at)->timezone('Asia/Jakarta');
                $personalDeadline = $startedAt->copy()->addMinutes($durationMinutes);

                // Real deadline adalah waktu tersingkat antara durasi siswa vs jadwal akhir sesi
                $realDeadline = $personalDeadline->min($sessionEnd);

                // Jika waktu sekarang melebihi deadline (diberi toleransi telat 1 menit)
                if ($now->greaterThan($realDeadline->addMinutes(1))) {
                    // Panggil fungsi skoring dan penutupan
                    $this->forceFinishLogic($examSession, $student);
                }
            }
        }
        // ------------------------------------------

        // 2. Ambil data siswa beserta status ujian (setelah disapu bersih) DAN asal sekolahnya
        $students = $examSession->students()
            ->with('school')
            ->orderBy('name', 'asc')
            ->get();

        // 3. LOGIKA LIVE UPDATE (AJAX)
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'students' => $students,
            ]);
        }

        // 4. Ambil data sekolah khusus untuk filter Super Admin
        $schools = auth()->user()->hasRole('admin') ? School::orderBy('name')->get() : [];

        // 5. Tampilkan view pertama kali dimuat
        return view('proctor.monitoring', compact('examSession', 'students', 'schools'));
    }

    /**
     * Fungsi Helper Private untuk menghitung nilai dan menutup sesi
     * Digunakan oleh fitur Auto-Sweep dan tombol manual Selesaikan Paksa.
     */
    private function forceFinishLogic(ExamSession $examSession, User $student)
    {
        $attempt = ExamAttempt::where('exam_session_id', $examSession->id)
            ->where('user_id', $student->id)
            ->first();

        if ($attempt) {
            $this->completeAttempt($attempt);
        }
    }

    private function completeAttempt(ExamAttempt $attempt): array
    {
        if ($attempt->status === 'completed') {
            return ['finalScore' => (float) $attempt->final_score];
        }

        return app(AttemptScoringService::class)->scoreAttempt($attempt);
    }

    /**
     * Membuka kunci (Unlock) siswa yang terkena pelanggaran (is_locked = true).
     */
    public function unlock(Request $request, ExamSession $examSession, User $student)
    {
        $examUser = ExamSessionUser::where('exam_session_id', $examSession->id)
            ->where('user_id', $student->id)
            ->firstOrFail();

        // Buka kunci dan reset jumlah pelanggaran
        $examUser->update([
            'is_locked' => false,
            'violation_count' => 0,
        ]);

        return back()->with('success', "Kunci ujian siswa {$student->name} berhasil dibuka.");
    }

    /**
     * Memaksa selesai ujian siswa (Force Finish).
     */
    public function forceFinish(Request $request, ExamSession $examSession, User $student)
    {
        $attempt = ExamAttempt::where('exam_session_id', $examSession->id)
            ->where('user_id', $student->id)
            ->firstOrFail();

        $result = $this->completeAttempt($attempt);

        return response()->json([
            'success' => true,
            'message' => "Ujian siswa {$student->name} diselesaikan. Nilai akhir: {$result['finalScore']}",
        ]);
    }

    public function forceFinishAll(Request $request, ExamSession $examSession)
    {
        $completedCount = DB::transaction(function () use ($examSession) {
            $attempts = ExamAttempt::where('exam_session_id', $examSession->id)
                ->where('status', '!=', 'completed')
                ->get();

            foreach ($attempts as $attempt) {
                $this->completeAttempt($attempt);
            }

            return $attempts->count();
        });

        return response()->json([
            'success' => true,
            'message' => "{$completedCount} peserta berhasil diselesaikan. Nilai akhir sudah dihitung.",
            'completed' => $completedCount,
        ]);
    }

    /**
     * Mereset login/ujian siswa (Mulai dari awal).
     */
    public function reset(Request $request, ExamSession $examSession, User $student)
    {
        $examUser = ExamSessionUser::where('exam_session_id', $examSession->id)
            ->where('user_id', $student->id)
            ->firstOrFail();

        $attemptIds = ExamAttempt::where('exam_session_id', $examSession->id)
            ->where('user_id', $student->id)
            ->pluck('id');

        // Hapus semua jawaban siswa untuk sesi ini
        StudentAnswer::where('exam_attempt_id', $examUser->id)
            ->delete();

        if ($attemptIds->isNotEmpty()) {
            DetailNilai::whereIn('exam_attempt_id', $attemptIds)
                ->where('user_id', $student->id)
                ->delete();
        }

        // Kembalikan status ke belum mulai
        $examUser->update([
            'status' => 'not_started',
            'started_at' => null,
            'finished_at' => null,
            'raw_score' => 0,
            'final_score' => 0,
            'is_locked' => false,
            'violation_count' => 0,
        ]);

        return back()->with('success', "Ujian siswa {$student->name} berhasil direset.");
    }

    public function exportAnalysis(ExamSession $examSession)
    {
        // Pastikan user memiliki akses
        $user = auth()->user();
        if (! $user->hasRole('admin')) {
            $hasAccess = $examSession->exam->teacher_id === $user->id ||
                         $examSession->exam->invitedTeachers()->where('user_id', $user->id)->exists();

            if (! $hasAccess) {
                abort(403, 'Akses ditolak.');
            }
        }

        $fileName = 'Analisis_Butir_Soal_'.Str::slug($examSession->exam->title).'_'.date('Ymd_His').'.xlsx';

        return Excel::download(new ButirSoalExport($examSession->id), $fileName);
    }

    public function exportJson(ExamSession $examSession)
    {
        $user = auth()->user();
        if (! $user->hasRole('admin') && $examSession->exam->teacher_id !== $user->id
            && ! $examSession->exam->invitedTeachers()->where('user_id', $user->id)->exists()) {
            abort(403, 'Akses ditolak.');
        }

        $attempts = ExamAttempt::with(['user:id,name,username', 'answers.question:id,content,type'])
            ->where('exam_session_id', $examSession->id)
            ->orderBy('user_id')
            ->get()
            ->map(fn ($attempt) => [
                'attempt_id' => $attempt->id,
                'student' => [
                    'id' => $attempt->user_id,
                    'name' => $attempt->user?->name,
                    'username' => $attempt->user?->username,
                ],
                'status' => $attempt->status,
                'started_at' => $attempt->started_at?->toISOString(),
                'finished_at' => $attempt->finished_at?->toISOString(),
                'raw_score' => (float) $attempt->raw_score,
                'final_score' => (float) $attempt->final_score,
                'violation_count' => $attempt->violation_count,
                'answers' => $attempt->answers->map(fn ($answer) => [
                    'question_id' => $answer->question_id,
                    'question_type' => $answer->question?->type,
                    'answer' => $answer->answer,
                    'score' => (float) $answer->score,
                    'is_doubtful' => (bool) $answer->is_doubtful,
                ])->values()->all(),
            ])->values()->all();

        $payload = [
            'exam' => [
                'id' => $examSession->exam->id,
                'title' => $examSession->exam->title,
            ],
            'session' => [
                'id' => $examSession->id,
                'name' => $examSession->session_name,
                'start_time' => $examSession->start_time?->toISOString(),
                'end_time' => $examSession->end_time?->toISOString(),
            ],
            'exported_at' => now()->toISOString(),
            'participants' => $attempts,
        ];

        return response()->streamDownload(function () use ($payload) {
            echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }, 'nilai_ujian_'.Str::slug($examSession->exam->title).'_'.now()->format('Ymd_His').'.json', [
            'Content-Type' => 'application/json; charset=UTF-8',
        ]);
    }

    /**
     * Menampilkan halaman analisis jawaban siswa secara detail.
     */
    public function showStudentAnalysis(ExamSession $examSession, User $student)
    {
        $examUser = ExamSessionUser::where('exam_session_id', $examSession->id)
            ->where('user_id', $student->id)
            ->firstOrFail();

        // Cegah akses jika ujian belum selesai
        if ($examUser->status !== 'completed') {
            return redirect()->back()->with('error', 'Siswa belum menyelesaikan ujian.');
        }

        // Ambil data jawaban beserta relasinya
        $answers = StudentAnswer::where('exam_attempt_id', $examUser->id)
            ->with(['question.options', 'question.matches'])
            ->get()
            ->map(function ($answer) {
                // Proses JSON jika jawaban berupa array (pilihan ganda kompleks, menjodohkan)
                $decoded = is_string($answer->answer) ? json_decode($answer->answer, true) : $answer->answer;
                $answer->formatted_answer = (json_last_error() === JSON_ERROR_NONE) ? $decoded : $answer->answer;

                return $answer;
            });

        return view('proctor.student-analysis', compact('examSession', 'student', 'examUser', 'answers'));
    }
}
