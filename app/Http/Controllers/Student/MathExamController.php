<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\MathExamQuestion;
use App\Models\MathExamUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MathExamController extends Controller
{
    public function index()
    {
        // MATIKAN FILTER SCHOOL_ID SEMENTARA (DEBUGGING)
        $examUsers = MathExamUser::withoutGlobalScopes()
            ->with(['exam' => function ($query) {
                $query->withoutGlobalScopes(); // Matikan juga di tabel induk
            }])
            ->where('student_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        // BUKA KOMENTAR DI BAWAH INI UNTUK MELIHAT ISI DATANYA MENTAH-MENTAH
        // dd($examUsers);

        $exams = $examUsers->map(function ($examUser) {
            $exam = $examUser->exam;

            if ($exam !== null) {
                $exam->status = $examUser->status;
                $exam->score = $examUser->score;
                $exam->assigned_at = $examUser->created_at;

                return $exam;
            }

            return null;
        })->filter();

        return view('student.math.index', compact('exams'));
    }

    public function run($id)
    {
        $userId = Auth::id();

        // 1. Cari Sesi Ujian Siswa ini (Gunakan first, bukan firstOrFail)
        $examUser = MathExamUser::with('exam')
            ->where('math_exam_id', $id)
            ->where('student_id', $userId)
            ->first();
        $math = MathExamUser::with('exam')->get();

        // --- PENGAMAN 1: Jika Siswa tidak terdaftar di ujian ini ---
        if (! $examUser) {
            return redirect()->route('student.dashboard') // Sesuaikan route kembalinya jika perlu
                ->with('error', 'Akses ditolak! Anda belum terdaftar dalam ujian ini atau ID ujian salah.');
        }

        // --- PENGAMAN 2: Jika Data Ujian Utama (Induk) tidak ada ---
        if (! $examUser->exam) {
            return redirect()->route('student.dashboard')
                ->with('info', 'Maaf, ujian ini tidak valid, sudah ditutup, atau telah dihapus.');
        }

        // --- PENGAMAN 3: Jika Siswa sudah selesai ujian ---
        if ($examUser->status === 'completed') {
            return redirect()->route('student.dashboard')
                ->with('info', 'Anda sudah menyelesaikan ujian matematika ini.');
        }

        $now = Carbon::now('Asia/Jakarta');

        // 2. Set waktu mulai (started_at) jika siswa baru pertama kali buka
        if ($examUser->status === 'not_started' || $examUser->started_at === null) {
            $examUser->update([
                'status' => 'ongoing',
                'started_at' => $now,
            ]);
            $startTime = $now;
        } else {
            // Jika siswa refresh halaman/keluar masuk, ambil waktu mulai yang awal
            $startTime = Carbon::parse($examUser->started_at)->timezone('Asia/Jakarta');
        }

        // 3. Hitung sisa waktu berdasarkan duration_minutes
        $duration = (int) $examUser->exam->duration_minutes;
        $deadline = $startTime->copy()->addMinutes($duration);
        $timeLeftSeconds = $now->diffInSeconds($deadline, false);

        // Beri toleransi delay jaringan 1 menit (-60 detik)
        if ($timeLeftSeconds <= 0 && $timeLeftSeconds > -60) {
            $timeLeftSeconds = 60; // Paksa beri 60 detik terakhir untuk ngumpulin
        } elseif ($timeLeftSeconds <= -60) {
            // Jika lewat dari 1 menit, paksa submit otomatis dari backend
            return $this->submitForm($examUser);
        }

        // 4. PENTING: Ambil khusus soal yang di-generate untuk student_id ini
        $questions = MathExamQuestion::where('math_exam_id', $id)
            ->where('student_id', $userId)
            ->get();

        $exam = $examUser->exam;

        // Tampilkan halaman ujian CBT
        return view('student.math.run', compact('exam', 'questions', 'timeLeftSeconds'));
    }

    public function submit(Request $request, $id)
    {
        $userId = Auth::id();

        // Cari sesi siswa
        $examUser = MathExamUser::where('math_exam_id', $id)
            ->where('student_id', $userId)
            ->firstOrFail();

        if ($examUser->status === 'completed') {
            return redirect()->route('student.dashboard');
        }

        $answers = json_decode($request->answers, true) ?? [];
        $correctCount = 0;

        // Ambil soal yang menjadi milik siswa ini
        $questions = MathExamQuestion::where('math_exam_id', $id)
            ->where('student_id', $userId)
            ->get();

        $totalQuestions = $questions->count();

        foreach ($questions as $q) {
            // Ambil jawaban, pastikan jika kosong dianggap null
            $studentAns = isset($answers[$q->id]) && $answers[$q->id] !== '' ? (int) $answers[$q->id] : null;
            $isCorrect = ($studentAns === $q->correct_answer && $studentAns !== null);

            if ($isCorrect) {
                $correctCount++;
            }

            // Simpan jawaban & status benar/salah ke database
            $q->update([
                'student_answer' => $studentAns,
                'is_correct' => $isCorrect,
            ]);
        }

        // Hitung Nilai Skala 100
        $score = ($totalQuestions > 0) ? ($correctCount / $totalQuestions) * 100 : 0;

        // Tutup sesi ujian dan simpan skor
        $examUser->update([
            'status' => 'completed',
            'finished_at' => Carbon::now('Asia/Jakarta'),
            'score' => round($score, 2),
        ]);

        return redirect()->route('student.math.index')->with('success', 'Ujian Selesai! Nilai Anda: '.round($score, 2));
    }

    private function submitForm($examUser)
    {
        $examUser->update([
            'status' => 'completed',
            'finished_at' => Carbon::now('Asia/Jakarta'),
        ]);

        return redirect()->route('student.math.index')->with('info', 'Waktu habis! Ujian otomatis ditutup.');
    }
}
