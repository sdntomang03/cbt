<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\ExamSessionUser;
use App\Models\Question;
use App\Models\RegistrationSetting;
use App\Models\StudentAnswer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentExamController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Ambil sesi ujian via relasi Many-to-Many
        $mySessions = $user->examSessions()
            ->withPivot('status', 'score')
            ->with(['exam' => function ($query) {
                $query->withCount('questions');
            }])
            ->orderBy('start_time', 'asc')
            ->get()
            ->map(function ($session) {
                $session->is_open = now()->between($session->start_time, $session->end_time);
                $session->user_status = $session->pivot->status;
                $session->user_score = $session->pivot->score;

                return $session;
            });

        return view('student.exams.index', compact('mySessions'));
    }

    public function run(Exam $exam)
    {
        // -----------------------------------------------------------------
        // [DINAMIS] CEK TOKEN: Jika butuh token tapi belum verifikasi, tolak.
        // Jika tidak butuh token, abaikan pengecekan session ini.
        // -----------------------------------------------------------------
        if ($exam->require_token && ! session()->has('verified_exam_'.$exam->id)) {
            // Catatan: Gunakan $exam (tanpa ->id) agar Hashids berfungsi baik
            return redirect()->route('student.exam.verify.show', $exam)
                ->with('error', 'Akses Ditolak! Silakan masukkan Token Ujian terlebih dahulu.');
        }

        $user = Auth::user();
        $now = Carbon::now('Asia/Jakarta');

        $session = ExamSession::where('exam_id', $exam->id)
            ->whereHas('students', fn ($q) => $q->where('users.id', $user->id))
            ->with('exam')
            ->firstOrFail();

        $pivot = $session->students()->where('users.id', $user->id)->first()->pivot;
        $examUser = ExamSessionUser::where('exam_session_id', $session->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if (request()->ajax()) {
            return response()->json([
                'status' => $pivot->status,
                'is_locked' => (bool) $examUser->is_locked,
            ]);
        }

        // BLOKIR 1: Jika terkunci
        if ($examUser->is_locked) {
            session()->forget('verified_exam_'.$exam->id);

            return redirect()->route('student.index')->with('error', 'AKSES DITOLAK: Ujian Anda telah dikunci karena pelanggaran.');
        }

        // BLOKIR 2: KUNCI UTAMA JIKA SUDAH SELESAI
        if ($pivot->status === 'completed' || $pivot->finished_at !== null) {
            session()->forget('verified_exam_'.$exam->id);

            return redirect()->route('student.index')->with('info', 'Ujian ini telah ditutup atau sudah Anda selesaikan.');
        }

        // LOGIKA WAKTU MULAI
        if ($pivot->started_at === null) {
            $user->examSessions()->updateExistingPivot($session->id, [
                'started_at' => $now,
                'status' => 'ongoing',
                'finished_at' => null,
            ]);
            $startTime = $now;
        } else {
            $startTime = Carbon::parse($pivot->started_at)->timezone('Asia/Jakarta');
            if ($pivot->status === 'not_started') {
                $user->examSessions()->updateExistingPivot($session->id, ['status' => 'ongoing']);
            }
        }

        // Perhitungan Deadline
        $duration = (int) $session->exam->duration_minutes;
        $deadlinePersonal = $startTime->copy()->addMinutes($duration);
        $deadlineSession = Carbon::parse($session->end_time)->timezone('Asia/Jakarta');
        $realDeadline = $deadlinePersonal->min($deadlineSession);
        $timeLeftSeconds = $now->diffInSeconds($realDeadline, false);

        if ($timeLeftSeconds <= 0 && $timeLeftSeconds > -60) {
            $timeLeftSeconds = 60;
        } elseif ($timeLeftSeconds <= -60) {
            return $this->forceFinish($session);
        }

        $questionsQuery = Question::where('exam_id', $exam->id)->with(['options', 'matches']);

        if ($session->exam->random_question) {
            $questionsQuery->inRandomOrder(Auth::id());
        }

        $questions = $questionsQuery->get();

        if ($session->exam->random_answer) {
            $questions->map(function ($question) use ($user) {
                $seed = $user->id + $question->id;
                $optionsArray = $question->options->all();
                srand($seed);
                shuffle($optionsArray);
                srand();
                $question->setRelation('options', collect($optionsArray));

                return $question;
            });
        }

        $existingAnswers = StudentAnswer::where('exam_session_id', $session->id)
            ->where('user_id', $user->id)
            ->pluck('answer', 'question_id')
            ->toArray();

        // -----------------------------------------------------------------
        // [DINAMIS] CONFIG: Kirim status pelanggaran ke JavaScript (Blade)
        // -----------------------------------------------------------------
        $config = [
            'random_question' => $session->exam->random_question ?? false,
            'random_answer' => $session->exam->random_answer ?? false,
            'enable_violation' => $session->exam->enable_violation ?? true,
            'max_tolerances' => $session->exam->max_tolerances ?? 3,
        ];

        return view('student.exams.run', [
            'exam' => $session->exam,
            'questions' => $questions,
            'config' => $config,
            'timeLeftSeconds' => (int) $timeLeftSeconds,
            'existingAnswers' => $existingAnswers,
            'pivot' => $examUser,
        ]);
    }

    public function saveAnswer(Request $request)
    {
        $request->validate([
            'exam_id' => 'required',
            'question_id' => 'required',
        ]);

        $user = Auth::user();

        $examUser = ExamSessionUser::whereHas('session', function ($q) use ($request) {
            $q->where('exam_id', $request->exam_id);
        })->where('user_id', $user->id)->firstOrFail();

        if ($examUser->is_locked) {
            return response()->json([
                'status' => 'error',
                'message' => 'UJIAN TERKUNCI! Jawaban tidak disimpan.',
            ], 403);
        }

        StudentAnswer::updateOrCreate(
            [
                'exam_session_id' => $examUser->exam_session_id,
                'user_id' => $user->id,
                'question_id' => $request->question_id,
            ],
            [
                'answer' => $request->answer,
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

    private function forceFinish($session)
    {
        $user = Auth::user();
        $pivot = $session->students()->where('users.id', $user->id)->first()->pivot;
        $finalScore = $pivot->score ?? 0;

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
                $studentAns = $ans->answer;

                if (is_string($studentAns) && in_array($q->type, ['complex_choice', 'matching', 'true_false', 'true_false_multi'])) {
                    $decoded = json_decode($studentAns, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $studentAns = $decoded;
                    }
                }

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

            $finalScore = ($totalQuestions > 0) ? ($totalScore / $totalQuestions) * 100 : 0;
            $finalScore = round($finalScore, 2);

            $user->examSessions()->updateExistingPivot($session->id, [
                'status' => 'completed',
                'finished_at' => now(),
                'score' => $finalScore,
            ]);
        }

        session()->forget('verified_exam_'.$session->exam_id);

        return redirect()->route('student.index')->with('success', 'Ujian berhasil dikumpulkan! Nilai Anda: '.$finalScore);
    }

    public function recordViolation(Request $request)
    {
        $request->validate(['exam_id' => 'required']);
        $user = Auth::user();

        // 1. Cari Exam Session dengan relasi exam
        $session = ExamSession::where('exam_id', $request->exam_id)
            ->whereHas('students', fn ($q) => $q->where('users.id', $user->id))
            ->with('exam')
            ->firstOrFail();

        $examUser = ExamSessionUser::where('exam_session_id', $session->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // -----------------------------------------------------------------
        // [DINAMIS] CEK PELANGGARAN: Tolak catatan jika fitur OFF
        // -----------------------------------------------------------------
        $enableViolation = $session->exam->enable_violation ?? true;

        if (! $enableViolation) {
            return response()->json([
                'violation_count' => $examUser->violation_count,
                'is_locked' => false,
                'message' => 'Sensor pelanggaran dinonaktifkan oleh guru.',
            ]);
        }

        // 3. Tambah hitungan jika fitur ON
        $newCount = $examUser->violation_count + 1;
        $maxTolerances = $session->exam->max_tolerances ?? 3;
        $isLocked = $examUser->is_locked;

        if ($newCount >= $maxTolerances) {
            $isLocked = true;
        }

        $examUser->update([
            'violation_count' => $newCount,
            'is_locked' => $isLocked,
        ]);

        return response()->json([
            'violation_count' => $newCount,
            'max_tolerances' => $maxTolerances,
            'is_locked' => (bool) $isLocked,
        ]);
    }

    public function showVerifyPage(Exam $exam)
    {
        $user = Auth::user();

        $session = ExamSession::where('exam_id', $exam->id)
            ->whereHas('students', fn ($q) => $q->where('users.id', $user->id))
            ->with('exam')
            ->firstOrFail();

        $pivot = $session->students()->where('users.id', $user->id)->first()->pivot;

        if ($pivot->is_locked) {
            return redirect()->route('student.index')->with('error', 'AKSES DITOLAK: Ujian Anda telah dikunci karena pelanggaran.');
        }

        if ($pivot->status === 'completed' || $pivot->finished_at !== null) {
            return redirect()->route('student.index')->with('info', 'Anda sudah menyelesaikan ujian ini.');
        }

        $now = now();
        if (! $now->between($session->start_time, $session->end_time)) {
            return redirect()->route('student.index')->with('error', 'Sesi ujian belum dibuka atau sudah ditutup.');
        }

        // -----------------------------------------------------------------
        // [DINAMIS] BYPASS TOKEN: Jika tidak butuh token, langsung masuk
        // -----------------------------------------------------------------
        if (! $session->exam->require_token) {
            session()->put('verified_exam_'.$exam->id, true);

            return redirect()->route('student.exam.run', $exam);
        }

        if (session()->has('verified_exam_'.$exam->id) && $pivot->status === 'ongoing') {
            return redirect()->route('student.exam.run', $exam);
        }

        $isAutoToken = RegistrationSetting::where('school_id', $user->school_id)->exists();
        $defaultToken = $isAutoToken ? $session->token : null;

        return view('student.exams.verify', compact('session', 'defaultToken'));
    }

    public function processToken(Request $request, Exam $exam)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $user = Auth::user();

        $session = ExamSession::where('exam_id', $exam->id)
            ->whereHas('students', fn ($q) => $q->where('users.id', $user->id))
            ->firstOrFail();

        if (strtoupper(trim($request->token)) !== strtoupper(trim($session->token))) {
            return back()->with('error', 'Token ujian tidak valid atau salah!');
        }

        session()->put('verified_exam_'.$exam->id, true);

        return redirect()->route('student.exam.run', $exam);
    }

    public function dashboard()
    {
        $user = auth()->user();

        $stats = [
            'total_ujian' => 0,
            'ujian_selesai' => 0,
        ];

        try {
            $stats['total_ujian'] = ExamSession::whereHas('students', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })->count();
        } catch (\Throwable $th) {
            $stats['total_ujian'] = 0;
        }

        return view('student.dashboard', compact('user', 'stats'));
    }

    public function checkStatus(Exam $exam)
    {
        $user = Auth::user();
        $examUser = ExamSessionUser::whereHas('session', fn ($q) => $q->where('exam_id', $exam->id))
            ->where('user_id', $user->id)
            ->select('status', 'is_locked')
            ->firstOrFail();

        return response()->json([
            'status' => $examUser->status,
            'is_locked' => (bool) $examUser->is_locked,
        ]);
    }
}
