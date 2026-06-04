<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\ExamSessionUser;
use App\Models\MathExamQuestion;
use App\Models\MathExamUser;
use App\Models\StudentAnswer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiStudentExamController extends Controller
{
    /* ============================================================================
       BAGIAN 1: UJIAN UMUM (GENERAL EXAM)
       ============================================================================ */

    public function index()
    {
        $user = Auth::user();
        $mySessions = $user->examSessions()
            ->withPivot('status', 'score', 'is_locked')
            ->with(['exam' => function ($query) {
                $query->withCount('questions');
            }])
            ->orderBy('start_time', 'asc')
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'exam_id' => $session->exam_id,
                    'title' => $session->exam->title,
                    'duration_minutes' => $session->exam->duration_minutes,
                    'start_time' => $session->start_time,
                    'end_time' => $session->end_time,
                    'is_open' => now()->between($session->start_time, $session->end_time),
                    'require_token' => (bool) $session->exam->require_token,
                    'status' => $session->pivot->status,
                    'score' => $session->pivot->score,
                    'is_locked' => (bool) $session->pivot->is_locked,
                    'total_questions' => $session->exam->questions_count,
                ];
            });

        return response()->json(['status' => 'success', 'data' => $mySessions]);
    }

    public function verifyToken(Request $request, Exam $exam)
    {
        $request->validate(['token' => 'required|string']);
        $user = Auth::user();

        $session = ExamSession::where('exam_id', $exam->id)
            ->whereHas('students', fn ($q) => $q->where('users.id', $user->id))
            ->firstOrFail();

        if (strtoupper(trim($request->token)) !== strtoupper(trim($session->token))) {
            return response()->json(['status' => 'error', 'message' => 'Token ujian tidak valid atau salah!'], 400);
        }

        return response()->json(['status' => 'success', 'message' => 'Token valid']);
    }

    public function startExam(Exam $exam)
    {
        $user = Auth::user();
        $now = Carbon::now('Asia/Jakarta');

        $session = ExamSession::where('exam_id', $exam->id)
            ->whereHas('students', fn ($q) => $q->where('users.id', $user->id))
            ->with('exam')
            ->firstOrFail();

        $examUser = ExamSessionUser::where('exam_session_id', $session->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($examUser->is_locked) {
            return response()->json(['status' => 'error', 'message' => 'AKSES DITOLAK: Ujian Anda telah dikunci karena pelanggaran.'], 403);
        }

        if ($examUser->status === 'completed' || $examUser->finished_at !== null) {
            return response()->json(['status' => 'error', 'message' => 'Ujian ini telah ditutup atau sudah Anda selesaikan.'], 403);
        }

        // Mulai waktu ujian
        if ($examUser->started_at === null) {
            $examUser->update([
                'started_at' => $now,
                'status' => 'ongoing',
                'finished_at' => null,
            ]);
            $startTime = $now;
        } else {
            $startTime = Carbon::parse($examUser->started_at)->timezone('Asia/Jakarta');
            if ($examUser->status === 'not_started') {
                $examUser->update(['status' => 'ongoing']);
            }
        }

        // Kalkulasi sisa waktu
        $duration = (int) $session->exam->duration_minutes;
        $deadlinePersonal = $startTime->copy()->addMinutes($duration);
        $deadlineSession = Carbon::parse($session->end_time)->timezone('Asia/Jakarta');
        $realDeadline = $deadlinePersonal->min($deadlineSession);
        $timeLeftSeconds = $now->diffInSeconds($realDeadline, false);

        if ($timeLeftSeconds <= 0 && $timeLeftSeconds > -60) {
            $timeLeftSeconds = 60;
        } elseif ($timeLeftSeconds <= -60) {
            return $this->forceFinishJSON($session);
        }

        // Ambil Data State Ujian
        $questionIds = $exam->questions()->pluck('questions.id')->toArray();
        $existingAnswers = StudentAnswer::where('exam_session_id', $session->id)
            ->where('user_id', $user->id)->pluck('answer', 'question_id')->toArray();
        $flags = StudentAnswer::where('exam_session_id', $session->id)
            ->where('user_id', $user->id)->where('is_doubtful', true)->pluck('question_id')->toArray();

        return response()->json([
            'status' => 'success',
            'data' => [
                'time_left_seconds' => (int) max(0, $timeLeftSeconds),
                'question_ids' => $questionIds,
                'existing_answers' => $existingAnswers,
                'flags' => $flags,
                'violation_count' => $examUser->violation_count,
                'config' => [
                    'random_question' => $session->exam->random_question ?? false,
                    'random_answer' => $session->exam->random_answer ?? false,
                    'enable_violation' => $session->exam->enable_violation ?? true,
                    'max_tolerances' => $session->exam->max_tolerances ?? 3,
                ],
            ],
        ]);
    }

    public function getQuestion(Exam $exam, $question_id)
    {
        $user = Auth::user();
        $session = ExamSession::where('exam_id', $exam->id)->whereHas('students', fn ($q) => $q->where('users.id', $user->id))->firstOrFail();
        $examUser = ExamSessionUser::where('exam_session_id', $session->id)->where('user_id', $user->id)->firstOrFail();

        if ($examUser->is_locked || $examUser->status === 'completed') {
            return response()->json(['status' => 'error', 'message' => 'Akses ditolak'], 403);
        }

        $question = $exam->questions()->where('questions.id', $question_id)
            ->select(['questions.id', 'questions.type', 'questions.content'])
            ->with([
                'options' => fn ($q) => $q->select(['id', 'question_id', 'option_text']),
                'matches' => fn ($q) => $q->select(['id', 'question_id', 'premise_text', 'target_text']),
            ])->firstOrFail();

        return response()->json(['status' => 'success', 'data' => $question]);
    }

    public function saveAnswer(Request $request)
    {
        $request->validate(['exam_id' => 'required', 'question_id' => 'required']);
        $user = Auth::user();

        $examUser = ExamSessionUser::whereHas('session', fn ($q) => $q->where('exam_id', $request->exam_id))
            ->where('user_id', $user->id)->firstOrFail();

        if ($examUser->is_locked || $examUser->status === 'completed') {
            return response()->json(['status' => 'error', 'message' => 'Ujian terkunci/selesai.'], 403);
        }

        StudentAnswer::updateOrCreate(
            ['exam_session_id' => $examUser->exam_session_id, 'user_id' => $user->id, 'question_id' => $request->question_id],
            ['answer' => $request->answer, 'is_doubtful' => $request->is_doubtful ?? false]
        );

        return response()->json(['status' => 'success']);
    }

    public function recordViolation(Request $request)
    {
        $request->validate(['exam_id' => 'required']);
        $user = Auth::user();

        $session = ExamSession::where('exam_id', $request->exam_id)->whereHas('students', fn ($q) => $q->where('users.id', $user->id))->with('exam')->firstOrFail();
        $examUser = ExamSessionUser::where('exam_session_id', $session->id)->where('user_id', $user->id)->firstOrFail();

        if (! ($session->exam->enable_violation ?? true)) {
            return response()->json(['status' => 'success', 'data' => ['violation_count' => $examUser->violation_count, 'is_locked' => false]]);
        }

        $newCount = $examUser->violation_count + 1;
        $maxTolerances = $session->exam->max_tolerances ?? 3;
        $isLocked = $newCount >= $maxTolerances;

        $examUser->update(['violation_count' => $newCount, 'is_locked' => $isLocked]);

        return response()->json(['status' => 'success', 'data' => ['violation_count' => $newCount, 'max_tolerances' => $maxTolerances, 'is_locked' => $isLocked]]);
    }

    public function checkStatus(Exam $exam)
    {
        $examUser = ExamSessionUser::whereHas('session', fn ($q) => $q->where('exam_id', $exam->id))
            ->where('user_id', Auth::id())->firstOrFail();

        return response()->json(['status' => 'success', 'data' => ['status' => $examUser->status, 'is_locked' => (bool) $examUser->is_locked]]);
    }

    public function finishExam(Exam $exam)
    {
        $session = ExamSession::where('exam_id', $exam->id)->whereHas('students', fn ($q) => $q->where('users.id', Auth::id()))->firstOrFail();

        return $this->forceFinishJSON($session);
    }

    private function forceFinishJSON($session)
    {
        $user = Auth::user();
        $pivot = $session->students()->where('users.id', $user->id)->first()->pivot;
        $finalScore = $pivot->score ?? 0;

        // COPAS LOGIC PENILAIAN DARI CONTROLLER LAMA ANDA DI SINI
        // (Logika foreach $answers, hitung $totalScore, dsb)
        // ... (Kode penilaian persis seperti di StudentExamController lama) ...

        if ($pivot->status !== 'completed') {
            // [TEMPEL LOGIKA HITUNG SKOR DI SINI]
            $finalScore = 100; // Contoh statis, gunakan logika asli Anda

            $user->examSessions()->updateExistingPivot($session->id, [
                'status' => 'completed',
                'finished_at' => now(),
                'score' => $finalScore,
            ]);
        }

        return response()->json(['status' => 'success', 'message' => 'Ujian selesai.', 'data' => ['score' => $finalScore]]);
    }

    /* ============================================================================
       BAGIAN 2: UJIAN MATEMATIKA (MATH EXAM)
       ============================================================================ */

    public function mathIndex()
    {
        $examUsers = MathExamUser::withoutGlobalScopes()
            ->with(['exam' => fn ($q) => $q->withoutGlobalScopes()])
            ->where('student_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        $exams = $examUsers->map(function ($examUser) {
            $exam = $examUser->exam;
            if ($exam !== null) {
                return [
                    'id' => $exam->id,
                    'title' => $exam->title,
                    'duration_minutes' => $exam->duration_minutes,
                    'status' => $examUser->status,
                    'score' => $examUser->score,
                    'assigned_at' => $examUser->created_at,
                ];
            }

            return null;
        })->filter()->values();

        return response()->json(['status' => 'success', 'data' => $exams]);
    }

    public function mathStart($id)
    {
        $userId = Auth::id();
        $examUser = MathExamUser::with('exam')->where('math_exam_id', $id)->where('student_id', $userId)->first();

        if (! $examUser) {
            return response()->json(['status' => 'error', 'message' => 'Akses ditolak.'], 403);
        }
        if ($examUser->status === 'completed') {
            return response()->json(['status' => 'error', 'message' => 'Ujian sudah diselesaikan.'], 403);
        }

        $now = Carbon::now('Asia/Jakarta');
        if ($examUser->status === 'not_started' || $examUser->started_at === null) {
            $examUser->update(['status' => 'ongoing', 'started_at' => $now]);
            $startTime = $now;
        } else {
            $startTime = Carbon::parse($examUser->started_at)->timezone('Asia/Jakarta');
        }

        $duration = (int) $examUser->exam->duration_minutes;
        $timeLeftSeconds = $now->diffInSeconds($startTime->copy()->addMinutes($duration), false);

        if ($timeLeftSeconds <= -60) {
            return $this->forceFinishMathJSON($examUser, $id, $userId);
        }

        // Ambil soal, TAPI JANGAN KIRIM `correct_answer` KE FLUTTER AGAR TIDAK DICONTEK!
        $questions = MathExamQuestion::where('math_exam_id', $id)->where('student_id', $userId)
            ->get(['id', 'num1', 'operator', 'num2', 'student_answer']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'time_left_seconds' => max(0, $timeLeftSeconds),
                'questions' => $questions,
            ],
        ]);
    }

    public function mathSaveAnswer(Request $request, $id)
    {
        try {
            $question = MathExamQuestion::withoutGlobalScopes()
                ->where('id', $request->question_id)
                ->where('math_exam_id', $id)
                ->where('student_id', Auth::id())
                ->first();

            if ($question) {
                $studentAns = ($request->answer !== null && $request->answer !== '') ? (int) $request->answer : null;
                $isCorrect = ($studentAns === $question->correct_answer && $studentAns !== null);

                $question->update([
                    'student_answer' => $studentAns,
                    'is_correct' => $isCorrect,
                ]);

                return response()->json(['status' => 'success']);
            }

            return response()->json(['status' => 'error', 'message' => 'Soal tidak ditemukan.'], 404);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    public function mathFinish(Request $request, $id)
    {
        $userId = Auth::id();
        $examUser = MathExamUser::where('math_exam_id', $id)->where('student_id', $userId)->firstOrFail();

        return $this->forceFinishMathJSON($examUser, $id, $userId);
    }

    private function forceFinishMathJSON($examUser, $examId, $userId)
    {
        if ($examUser->status === 'completed') {
            return response()->json(['status' => 'success', 'data' => ['score' => $examUser->score]]);
        }

        $questions = MathExamQuestion::where('math_exam_id', $examId)->where('student_id', $userId)->get();
        $correctCount = $questions->where('is_correct', true)->count();
        $totalQuestions = $questions->count();
        $score = ($totalQuestions > 0) ? ($correctCount / $totalQuestions) * 100 : 0;

        $examUser->update([
            'status' => 'completed',
            'finished_at' => Carbon::now('Asia/Jakarta'),
            'score' => round($score, 2),
        ]);

        return response()->json(['status' => 'success', 'message' => 'Ujian Matematika selesai', 'data' => ['score' => round($score, 2)]]);
    }

    public function mathResult($id)
    {
        $userId = Auth::id();
        $examUser = MathExamUser::with('exam')->where('math_exam_id', $id)->where('student_id', $userId)->firstOrFail();

        if ($examUser->status !== 'completed') {
            return response()->json(['status' => 'error', 'message' => 'Belum diselesaikan'], 400);
        }

        // Di hasil, kita boleh kirimkan correct_answer untuk review
        $questions = MathExamQuestion::where('math_exam_id', $id)->where('student_id', $userId)
            ->get(['id', 'num1', 'operator', 'num2', 'student_answer', 'correct_answer', 'is_correct']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'exam' => [
                    'title' => $examUser->exam->title,
                    'score' => $examUser->score,
                    'finished_at' => $examUser->finished_at,
                ],
                'questions' => $questions,
            ],
        ]);
    }
}
