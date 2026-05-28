<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\MathExam;
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

        // ========================================================================
        // 🛠️ BLOK DEBUGGING (Hapus tanda // pada dd() di bawah ini jika masih error)
        // Ini akan membongkar apa sebenarnya yang sedang dibaca oleh sistem Laravel
        // ========================================================================
        /*
        dd([
            '1_URL_ID_Ujian' => $id,
            '2_ID_User_Login' => $userId,
            '3_Apakah_Ujian_Ada?' => \App\Models\MathExam::find($id) ? 'ADA' : 'TIDAK ADA',
            '4_Apakah_Siswa_Terdaftar?' => \App\Models\MathExamUser::where('math_exam_id', $id)->where('student_id', $userId)->exists() ? 'TERDAFTAR' : 'TIDAK TERDAFTAR',
        ]);
        */

        // --- CEK LAPIS 1: Apakah Ujian (Induk) dengan ID tersebut ada di sistem? ---
        $exam = MathExam::find($id);
        if (! $exam) {
            return redirect()->route('student.dashboard')
                ->with('error', 'Ujian tidak ditemukan. Pastikan link atau ID ujian benar.');
        }

        // --- CEK LAPIS 2: Apakah Siswa yang sedang login terdaftar di ujian ini? ---
        $examUser = MathExamUser::with('exam')
            ->where('math_exam_id', $id)
            ->where('student_id', $userId)
            ->first();

        if (! $examUser) {
            return redirect()->route('student.dashboard')
                ->with('error', 'Akses ditolak! Anda tidak terdaftar sebagai peserta pada ujian ini.');
        }

        // --- CEK LAPIS 3: Apakah siswa sudah pernah menyelesaikan ujian ini? ---
        if ($examUser->status === 'completed') {
            return redirect()->route('student.dashboard')
                ->with('info', 'Anda sudah menyelesaikan ujian ini. Ujian tidak dapat diulang.');
        }

        // --- MANAJEMEN WAKTU & STATUS PENGERJAAN ---
        $now = Carbon::now('Asia/Jakarta');

        // Jika statusnya belum mulai, set waktu mulainya (started_at) sekarang
        if ($examUser->status === 'not_started' || $examUser->started_at === null) {
            $examUser->update([
                'status' => 'ongoing',
                'started_at' => $now,
            ]);
            $startTime = $now;
        } else {
            // Jika sudah mulai (misal refresh halaman), ambil waktu start aslinya
            $startTime = Carbon::parse($examUser->started_at)->timezone('Asia/Jakarta');
        }

        // --- CEK LAPIS 4: Kalkulasi Sisa Waktu Ujian ---
        $duration = (int) $examUser->exam->duration_minutes;
        $deadline = $startTime->copy()->addMinutes($duration);
        $timeLeftSeconds = $now->diffInSeconds($deadline, false); // false agar bisa bernilai minus

        // Toleransi delay jaringan 1 menit (-60 detik)
        if ($timeLeftSeconds <= 0 && $timeLeftSeconds > -60) {
            $timeLeftSeconds = 60; // Beri waktu 1 menit terakhir untuk mengumpulkan
        } elseif ($timeLeftSeconds <= -60) {
            // Jika lewat dari 1 menit, langsung paksa submit otomatis dari backend
            return $this->submitForm($examUser);
        }

        // --- CEK LAPIS 5: Ambil soal khusus untuk siswa ini ---
        $questions = MathExamQuestion::where('math_exam_id', $id)
            ->where('student_id', $userId)
            ->get();

        // Jika karena suatu hal soal untuk siswa ini belum di-generate oleh admin
        if ($questions->isEmpty()) {
            return redirect()->route('student.dashboard')
                ->with('error', 'Soal ujian belum disiapkan untuk Anda. Silakan lapor ke guru.');
        }

        // Lolos semua pengecekan, tampilkan halaman ujian CBT!
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

    public function autosave(Request $request, $id)
    {
        try {
            $userId = Auth::id();
            $questionId = $request->question_id;
            $answer = $request->answer;

            // Memastikan data ujian dicari dengan mengabaikan global scope
            $examUser = MathExamUser::withoutGlobalScopes()
                ->where('math_exam_id', $id)
                ->where('student_id', $userId)
                ->where('status', 'ongoing')
                ->first();

            if (! $examUser) {
                return response()->json(['status' => 'error', 'message' => 'Ujian sudah ditutup atau tidak valid.'], 403);
            }

            // Cari soal
            $question = MathExamQuestion::withoutGlobalScopes()
                ->where('id', $questionId)
                ->where('math_exam_id', $id)
                ->where('student_id', $userId)
                ->first();

            if ($question) {
                $studentAns = ($answer !== null && $answer !== '') ? (int) $answer : null;

                // PERBAIKAN: Hapus kata 'clone' di sini
                $isCorrect = ($studentAns === $question->correct_answer && $studentAns !== null);

                // Update data
                $question->update([
                    'student_answer' => $studentAns,
                    'is_correct' => $isCorrect,
                ]);

                return response()->json(['status' => 'success']);
            }

            return response()->json(['status' => 'error', 'message' => 'Soal tidak ditemukan.'], 404);

        } catch (\Throwable $e) {
            // Menggunakan \Throwable akan menangkap Fatal Error PHP sekalipun
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 400);
        }
    }

    public function result($id)
    {
        $userId = Auth::id();

        // 1. Ambil data sesi ujian siswa
        $examUser = MathExamUser::with('exam')
            ->where('math_exam_id', $id)
            ->where('student_id', $userId)
            ->firstOrFail();

        // 2. Pastikan ujian sudah berstatus 'completed'
        if ($examUser->status !== 'completed') {
            return redirect()->route('student.math.index')
                ->with('info', 'Anda belum menyelesaikan ujian ini.');
        }

        // 3. Ambil data soal dan jawaban siswa tersebut
        $questions = MathExamQuestion::where('math_exam_id', $id)
            ->where('student_id', $userId)
            ->get();

        return view('student.math.result', compact('examUser', 'questions'));
    }
}
