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
        // 1. Ambil data dari tabel Sesi Siswa (math_exam_users)
        $examUsers = MathExamUser::with('exam')
            ->where('student_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        // 2. Mapping agar format variabelnya tetap dikenali oleh view Blade lama
        $exams = $examUsers->map(function ($examUser) {
            $exam = $examUser->exam;
            $exam->status = $examUser->status; // Pakai status dari sesi siswa
            $exam->score = $examUser->score;   // Pakai skor dari sesi siswa
            $exam->assigned_at = $examUser->created_at; // Waktu ujian ditugaskan

            return $exam;
        });

        return view('student.math.index', compact('exams'));
    }

    public function run($id)
    {
        $userId = Auth::id();

        // 1. Cari Sesi Ujian Siswa ini
        $examUser = MathExamUser::with('exam')
            ->where('math_exam_id', $id)
            ->where('student_id', $userId)
            ->firstOrFail();

        if ($examUser->status === 'completed') {
            return redirect()->route('student.dashboard')->with('info', 'Anda sudah menyelesaikan ujian matematika ini.');
        }

        $now = Carbon::now('Asia/Jakarta');

        // 2. Set waktu mulai (started_at) jika belum mulai
        if ($examUser->status === 'not_started' || $examUser->started_at === null) {
            $examUser->update([
                'status' => 'ongoing',
                'started_at' => $now,
            ]);
            $startTime = $now;
        } else {
            $startTime = Carbon::parse($examUser->started_at)->timezone('Asia/Jakarta');
        }

        // 3. Hitung sisa waktu
        $duration = (int) $examUser->exam->duration_minutes;
        $deadline = $startTime->copy()->addMinutes($duration);
        $timeLeftSeconds = $now->diffInSeconds($deadline, false);

        // Beri toleransi delay jaringan 1 menit (-60)
        if ($timeLeftSeconds <= 0 && $timeLeftSeconds > -60) {
            $timeLeftSeconds = 60;
        } elseif ($timeLeftSeconds <= -60) {
            return $this->submitForm($examUser);
        }

        // 4. PENTING: Ambil khusus soal yang dibuat untuk student_id ini
        $questions = MathExamQuestion::where('math_exam_id', $id)
            ->where('student_id', $userId)
            ->get();

        $exam = $examUser->exam;

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
