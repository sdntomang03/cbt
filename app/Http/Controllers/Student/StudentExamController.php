<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ExamSession;
use App\Models\Question;
use App\Models\StudentAnswer; // Pastikan Model ini ada
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentExamController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Ambil sesi ujian via relasi Many-to-Many
        $mySessions = $user->examSessions()
            ->with('exam')
            ->orderBy('start_time', 'asc')
            ->get()
            ->map(function ($session) {
                // Cek apakah server sedang buka (berdasarkan jadwal)
                $session->is_open = now()->between($session->start_time, $session->end_time);

                // Cek status pribadi siswa (dari pivot)
                $session->user_status = $session->pivot->status;
                $session->user_score = $session->pivot->score;

                return $session;
            });

        return view('student.exams.index', compact('mySessions'));
    }

    public function run($exam_id)
    {
        $user = Auth::user();
        // Paksa timezone ke Jakarta agar sinkron dengan jadwal di database
        $now = \Carbon\Carbon::now('Asia/Jakarta');

        $session = ExamSession::where('exam_id', $exam_id)
            ->whereHas('students', fn ($q) => $q->where('users.id', $user->id))
            ->with('exam')
            ->firstOrFail();

        $pivot = $session->students()->where('users.id', $user->id)->first()->pivot;

        // 1. Cek jika sudah benar-benar selesai
        if ($pivot->status === 'completed' || $pivot->finished_at !== null) {
            return redirect()->route('student.dashboard')->with('info', 'Anda sudah menyelesaikan ujian ini.');
        }

        // 2. Set Waktu Mulai jika status masih 'not_started'
        if ($pivot->status === 'not_started' || $pivot->started_at === null) {
            $user->examSessions()->updateExistingPivot($session->id, [
                'started_at' => $now,
                'status' => 'ongoing',
                'finished_at' => null,
            ]);
            $startTime = $now;
        } else {
            $startTime = \Carbon\Carbon::parse($pivot->started_at)->timezone('Asia/Jakarta');
        }

        // 3. Perhitungan Deadline
        $duration = (int) $session->exam->duration_minutes; // Durasi dalam menit
        // dd($duration);
        $deadlinePersonal = $startTime->copy()->addMinutes($duration);
        $deadlineSession = \Carbon\Carbon::parse($session->end_time)->timezone('Asia/Jakarta');

        // Ambil waktu tersingkat antara durasi personal vs jadwal sesi
        $realDeadline = $deadlinePersonal->min($deadlineSession);

        // Hitung sisa detik
        $timeLeftSeconds = $now->diffInSeconds($realDeadline, false);

        // 4. Proteksi Terakhir: Jika selisih sangat tipis/negatif sedikit karena lag, berikan 1 menit
        if ($timeLeftSeconds <= 0 && $timeLeftSeconds > -60) {
            $timeLeftSeconds = 60;
        } elseif ($timeLeftSeconds <= -60) {
            // Jika sudah lewat lebih dari 1 menit, baru anggap habis
            return $this->forceFinish($session);
        }

        $questions = Question::where('exam_id', $exam_id)
            ->with(['options', 'matches']) // Load matches yang baru
            ->get();
        $existingAnswers = StudentAnswer::where('exam_session_id', $session->id)
            ->where('user_id', $user->id)
            ->pluck('answer', 'question_id')
            ->toArray();

        $config = [
            'random_question' => $exam->random_question ?? false, // true/false
            'random_answer' => $exam->random_answer ?? false,   // true/false
        ];

        return view('student.exams.run', [
            'exam' => $session->exam,
            'questions' => $questions,
            'config' => $config,
            'timeLeftSeconds' => (int) $timeLeftSeconds,
            'existingAnswers' => $existingAnswers,
        ]);
    }

    public function saveAnswer(Request $request)
    {
        $request->validate([
            'exam_id' => 'required',
            'question_id' => 'required',
            // 'answer' tidak divalidasi string agar bisa menerima array/object
        ]);

        $user = Auth::user();

        // Cari sesi aktif
        $session = ExamSession::where('exam_id', $request->exam_id)
            ->whereHas('students', fn ($q) => $q->where('users.id', $user->id))
            ->firstOrFail();

        // Update atau Create jawaban
        StudentAnswer::updateOrCreate(
            [
                'exam_session_id' => $session->id,
                'user_id' => $user->id,
                'question_id' => $request->question_id,
            ],
            [
                'answer' => $request->answer, // Disimpan sebagai JSON berkat casting di Model
                'is_doubtful' => $request->is_doubtful ?? false,
            ]
        );

        return response()->json(['status' => 'success']);
    }

    public function finish($exam_id)
    {
        $user = Auth::user();

        $session = ExamSession::where('exam_id', $exam_id)
            ->whereHas('students', fn ($q) => $q->where('users.id', $user->id))
            ->firstOrFail();

        return $this->forceFinish($session);
    }

    // Helper Private untuk Hitung Nilai & Tutup Sesi
    private function forceFinish($session)
    {
        $user = Auth::user();

        // Ambil data pivot (tabel penghubung user dan exam_session)
        $pivot = $session->students()->where('users.id', $user->id)->first()->pivot;

        // FIX: Inisialisasi $finalScore dari database.
        // Jika sudah ada nilainya ambil, jika belum set 0.
        $finalScore = $pivot->score ?? 0;

        // Cek apakah ujian belum selesai. Jika belum, baru hitung ulang.
        if ($pivot->status !== 'completed') {

            $answers = StudentAnswer::where('exam_session_id', $session->id)
                ->where('user_id', $user->id)
                ->with(['question.options', 'question.matches'])
                ->get();

            $totalScore = 0;
            $totalQuestions = Question::where('exam_id', $session->exam_id)->count();

            foreach ($answers as $ans) {
                $q = $ans->question;
                $poin = 0;

                // Decode JSON jika perlu
                $studentAns = $ans->answer;
                if (is_string($studentAns) && in_array($q->type, ['complex_choice', 'matching', 'true_false', 'true_false_multi'])) {
                    $decoded = json_decode($studentAns, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $studentAns = $decoded;
                    }
                }

                // --- LOGIKA SKORING ---
                if ($q->type === 'single_choice') {
                    $correctOption = $q->options->where('is_correct', true)->first();
                    if ($correctOption && $studentAns == $correctOption->id) {
                        $poin = 1;
                    }
                } elseif ($q->type === 'complex_choice') {
                    $correctIds = $q->options->where('is_correct', true)->pluck('id')->sort()->values()->toArray();
                    $studentIds = is_array($studentAns) ? $studentAns : [];
                    sort($studentIds);
                    if ($correctIds == $studentIds) {
                        $poin = 1;
                    }
                } elseif (in_array($q->type, ['true_false', 'true_false_multi'])) {
                    $correctCount = 0;
                    $totalOptions = $q->options->count();
                    $userAnswers = is_array($studentAns) ? $studentAns : [];
                    foreach ($q->options as $opt) {
                        $expectedKey = $opt->is_correct ? 'benar' : 'salah';
                        $userValue = isset($userAnswers[$opt->id]) ? strtolower($userAnswers[$opt->id]) : null;
                        if ($userValue === $expectedKey) {
                            $correctCount++;
                        }
                    }
                    if ($totalOptions > 0) {
                        $poin = $correctCount / $totalOptions;
                    }
                } elseif ($q->type === 'matching') {
                    $matches = is_array($studentAns) ? $studentAns : [];
                    $totalPairs = $q->matches->count();
                    $correctPairs = 0;
                    if ($totalPairs > 0) {
                        foreach ($matches as $premiseId => $targetId) {
                            if ($premiseId == $targetId) {
                                $correctPairs++;
                            }
                        }
                        $poin = $correctPairs / $totalPairs;
                    }
                } elseif ($q->type === 'essay') {
                    $correctRaw = $q->options->first()->option_text ?? '';
                    $cleanCorrect = trim(strip_tags(html_entity_decode($correctRaw)));
                    $cleanUser = trim(strip_tags($studentAns));
                    if (strcasecmp($cleanCorrect, $cleanUser) === 0) {
                        $poin = 1;
                    } elseif (is_numeric($cleanCorrect) && is_numeric($cleanUser)) {
                        if ((float) $cleanCorrect === (float) $cleanUser) {
                            $poin = 1;
                        }
                    }
                }

                $ans->update(['score' => $poin]);
                $totalScore += $poin;
            }

            // Update variabel $finalScore dengan hasil perhitungan baru
            $finalScore = ($totalQuestions > 0) ? ($totalScore / $totalQuestions) * 100 : 0;
            $finalScore = round($finalScore, 2);

            $user->examSessions()->updateExistingPivot($session->id, [
                'status' => 'completed',
                'finished_at' => now(),
                'score' => $finalScore,
            ]);
        }

        return redirect()->route('student.dashboard')->with('success', 'Ujian berhasil dikumpulkan! Nilai Anda: '.$finalScore);
    }
}
