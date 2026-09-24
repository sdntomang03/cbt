<?php

namespace App\Http\Controllers\Api\V1\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamSession;
use App\Models\StudentAnswer;
use App\Services\AttemptScoringService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StudentExamApiController extends Controller
{
    public function index(Request $request)
    {
        $sessions = $request->user()->examSessions()
            ->withPivot(['status', 'final_score', 'is_locked'])
            ->with(['exam' => fn ($query) => $query->withCount('questions')])
            ->orderBy('start_time')
            ->get();

        $data = $sessions->map(fn ($session) => $this->sessionData($session))->values();

        return $this->success('Daftar ujian berhasil diambil.', $data);
    }

    public function show(Request $request, Exam $exam)
    {
        $session = $this->studentSession($request, $exam)->load('exam');

        return $this->success('Detail ujian berhasil diambil.', [
            'id' => $session->exam->hashid,
            'session_id' => $session->id,
            'title' => $session->exam->title,
            'duration_minutes' => (int) $session->exam->duration_minutes,
            'start_time' => $session->start_time,
            'end_time' => $session->end_time,
            'require_token' => (bool) $session->exam->require_token,
            'status' => $session->pivot->status,
            'is_locked' => (bool) $session->pivot->is_locked,
            'total_questions' => $session->exam->questions()->count(),
        ]);
    }

    public function start(Request $request, Exam $exam)
    {
        $session = $this->studentSession($request, $exam)->load('exam');
        $attempt = $this->attemptFor($session, $request->user()->id);

        if ($attempt->is_locked) {
            return $this->error('Akses ditolak: ujian telah dikunci.', 403);
        }
        if ($attempt->status === 'completed' || $attempt->finished_at !== null) {
            return $this->error('Ujian ini sudah diselesaikan.', 403);
        }
        if ($session->exam->require_token) {
            $request->validate(['token' => ['required', 'string']]);
            if (strtoupper(trim($request->token)) !== strtoupper(trim((string) $session->token))) {
                return $this->error('Token ujian tidak valid.', 422);
            }
        }

        $now = Carbon::now('Asia/Jakarta');
        if ($attempt->started_at === null) {
            $attempt->update(['started_at' => $now, 'status' => 'ongoing', 'finished_at' => null]);
        } elseif ($attempt->status === 'not_started') {
            $attempt->update(['status' => 'ongoing']);
        }

        $remaining = $this->remainingSeconds($attempt, $session);
        if ($remaining < -60) {
            return $this->completeAttempt($attempt, 'Waktu ujian telah habis.');
        }

        $answers = StudentAnswer::where('exam_attempt_id', $attempt->id)
            ->pluck('answer', 'question_id');
        $flags = StudentAnswer::where('exam_attempt_id', $attempt->id)
            ->where('is_doubtful', true)->pluck('question_id')->values();

        return $this->success('Ujian siap dikerjakan.', [
            'exam' => [
                'id' => $exam->hashid,
                'title' => $exam->title,
                'duration_minutes' => (int) $exam->duration_minutes,
            ],
            'attempt' => [
                'id' => $attempt->id,
                'status' => $attempt->status,
                'started_at' => $attempt->started_at,
                'end_at' => $this->deadline($attempt, $session),
                'remaining_seconds' => max(0, $remaining),
            ],
            'sections' => $exam->sections()->with('section')->orderBy('order')->orderBy('id')->get()
                ->map(fn ($section) => [
                    'id' => $section->id,
                    'name' => $section->section?->name ?? 'Sesi Utama',
                    'question_ids' => $section->questions()->orderBy('id')->pluck('id')->values(),
                ])->values(),
            'question_ids' => $exam->questions()->orderBy('questions.id')->pluck('questions.id')->values(),
            'existing_answers' => $answers,
            'flags' => $flags,
            'config' => [
                'random_question' => (bool) $exam->random_question,
                'random_answer' => (bool) $exam->random_answer,
                'enable_violation' => $exam->enable_violation ?? true,
                'max_tolerances' => $exam->max_tolerances ?? 3,
            ],
            'server_time' => $now,
        ]);
    }

    public function question(Request $request, ExamAttempt $attempt, $question)
    {
        $attempt = $this->ownedAttempt($request, $attempt);
        $this->ensureOpen($attempt);
        $item = $attempt->session->exam->questions()
            ->where('questions.id', $question)
            ->select(['questions.id', 'questions.type', 'questions.content'])
            ->with([
                'options' => fn ($query) => $query->select(['id', 'question_id', 'option_text']),
                'matches' => fn ($query) => $query->select(['id', 'question_id', 'premise_text', 'target_text']),
            ])->first();

        if (! $item) {
            return $this->error('Soal tidak ditemukan dalam ujian ini.', 404);
        }

        $answer = StudentAnswer::where('exam_attempt_id', $attempt->id)
            ->where('question_id', $item->id)->first();

        return $this->success('Soal berhasil diambil.', [
            'attempt_id' => $attempt->id,
            'question' => $item,
            'answer' => $answer?->answer,
            'is_doubtful' => (bool) ($answer?->is_doubtful ?? false),
            'server_time' => Carbon::now('Asia/Jakarta'),
            'remaining_seconds' => max(0, $this->remainingSeconds($attempt, $attempt->session)),
        ]);
    }

    public function attempt(Request $request, ExamAttempt $attempt)
    {
        $attempt = $this->ownedAttempt($request, $attempt);

        return $this->success('Detail attempt berhasil diambil.', [
            'id' => $attempt->id,
            'exam_id' => $attempt->session->exam->hashid,
            'status' => $attempt->status,
            'started_at' => $attempt->started_at,
            'finished_at' => $attempt->finished_at,
            'is_locked' => (bool) $attempt->is_locked,
            'violation_count' => (int) $attempt->violation_count,
            'remaining_seconds' => max(0, $this->remainingSeconds($attempt, $attempt->session)),
        ]);
    }

    public function answer(Request $request, ExamAttempt $attempt)
    {
        $validated = $request->validate([
            'question_id' => ['required', 'integer'],
            'answer' => ['nullable'],
            'is_doubtful' => ['sometimes', 'boolean'],
        ]);
        $attempt = $this->ownedAttempt($request, $attempt);
        $this->ensureOpen($attempt);

        if (! $attempt->session->exam->questions()->where('questions.id', $validated['question_id'])->exists()) {
            return $this->error('Soal tidak ditemukan dalam ujian ini.', 404);
        }

        $saved = StudentAnswer::updateOrCreate(
            ['exam_attempt_id' => $attempt->id, 'question_id' => $validated['question_id']],
            ['answer' => $validated['answer'] ?? null, 'is_doubtful' => $validated['is_doubtful'] ?? false]
        );

        return $this->success('Jawaban berhasil disimpan.', [
            'question_id' => $saved->question_id,
            'saved' => true,
            'is_doubtful' => (bool) $saved->is_doubtful,
        ]);
    }

    public function progress(Request $request, ExamAttempt $attempt)
    {
        $attempt = $this->ownedAttempt($request, $attempt);
        $total = $attempt->session->exam->questions()->count();
        $answers = $attempt->answers;
        $answered = $answers->filter(fn ($answer) => $answer->answer !== null && $answer->answer !== '' && $answer->answer !== [])->count();

        return $this->success('Progress berhasil diambil.', [
            'total_questions' => $total,
            'answered' => $answered,
            'unanswered' => max(0, $total - $answered),
            'doubtful' => $answers->where('is_doubtful', true)->count(),
            'percentage' => $total > 0 ? round(($answered / $total) * 100, 2) : 0,
            'status' => $attempt->status,
            'remaining_seconds' => max(0, $this->remainingSeconds($attempt, $attempt->session)),
        ]);
    }

    public function violation(Request $request, ExamAttempt $attempt)
    {
        $attempt = $this->ownedAttempt($request, $attempt);
        $exam = $attempt->session->exam;
        if (! ($exam->enable_violation ?? true)) {
            return $this->success('Pelanggaran dinonaktifkan untuk ujian ini.', [
                'violation_count' => $attempt->violation_count,
                'max_tolerances' => $exam->max_tolerances ?? 3,
                'is_locked' => false,
            ]);
        }

        $max = (int) ($exam->max_tolerances ?? 3);
        $attempt->update([
            'violation_count' => $attempt->violation_count + 1,
            'is_locked' => ($attempt->violation_count + 1) >= $max,
        ]);

        return $this->success('Pelanggaran berhasil dicatat.', [
            'violation_count' => $attempt->violation_count,
            'max_tolerances' => $max,
            'is_locked' => (bool) $attempt->is_locked,
        ]);
    }

    public function submit(Request $request, ExamAttempt $attempt)
    {
        $attempt = $this->ownedAttempt($request, $attempt);
        if ($attempt->is_locked) {
            return $this->error('Ujian terkunci dan tidak dapat diselesaikan.', 403);
        }
        if ($attempt->status === 'completed') {
            return $this->success('Ujian sudah diselesaikan.', $this->resultData($attempt));
        }

        return $this->completeAttempt($attempt, 'Ujian berhasil diselesaikan.');
    }

    public function result(Request $request, ExamAttempt $attempt)
    {
        $attempt = $this->ownedAttempt($request, $attempt);
        if ($attempt->status !== 'completed') {
            return $this->error('Hasil belum tersedia karena ujian belum selesai.', 400);
        }

        return $this->success('Hasil ujian berhasil diambil.', $this->resultData($attempt));
    }

    public function status(Request $request, Exam $exam)
    {
        $session = $this->studentSession($request, $exam);
        $attempt = $this->attemptFor($session, $request->user()->id);

        return $this->success('Status ujian berhasil diambil.', [
            'status' => $attempt->status,
            'is_locked' => (bool) $attempt->is_locked,
            'server_time' => Carbon::now('Asia/Jakarta'),
            'remaining_seconds' => max(0, $this->remainingSeconds($attempt, $session)),
        ]);
    }

    public function dashboard(Request $request)
    {
        $sessions = $request->user()->examSessions()
            ->withPivot(['status', 'final_score', 'is_locked', 'finished_at'])
            ->with('exam:id,title,duration_minutes')->orderBy('start_time')->get();
        $now = now();
        $completed = $sessions->where('pivot.status', 'completed');

        return $this->success('Dashboard berhasil diambil.', [
            'stats' => [
                'total_ujian' => $sessions->count(),
                'ujian_selesai' => $completed->count(),
                'ujian_aktif' => $sessions->filter(fn ($session) => $now->between($session->start_time, $session->end_time)
                    && $session->pivot->status !== 'completed' && ! $session->pivot->is_locked)->count(),
                'rata_nilai' => round((float) ($completed->avg(fn ($session) => $session->pivot->final_score) ?? 0), 2),
            ],
            'upcoming_sessions' => $sessions->filter(fn ($session) => $session->end_time->isFuture()
                && $session->pivot->status !== 'completed' && ! $session->pivot->is_locked)->take(4)->values(),
            'recent_results' => $completed->sortByDesc(fn ($session) => $session->pivot->finished_at ?? $session->end_time)->take(4)->values(),
            'classrooms' => $request->user()->classrooms()->pluck('name'),
        ]);
    }

    private function studentSession(Request $request, Exam $exam): ExamSession
    {
        return $request->user()->examSessions()->where('exam_sessions.exam_id', $exam->id)
            ->withPivot(['status', 'started_at', 'finished_at', 'raw_score', 'final_score', 'is_locked', 'violation_count'])
            ->firstOrFail();
    }

    private function attemptFor(ExamSession $session, int $userId): ExamAttempt
    {
        return ExamAttempt::where('exam_session_id', $session->id)->where('user_id', $userId)
            ->with(['session.exam'])->firstOrFail();
    }

    private function ownedAttempt(Request $request, ExamAttempt $attempt): ExamAttempt
    {
        abort_unless((int) $attempt->user_id === (int) $request->user()->id, 404);

        return $attempt->loadMissing(['session.exam']);
    }

    private function ensureOpen(ExamAttempt $attempt): void
    {
        abort_if($attempt->is_locked, 403, 'Ujian terkunci.');
        abort_if($attempt->status === 'completed', 403, 'Ujian sudah selesai.');
        abort_if($this->remainingSeconds($attempt, $attempt->session) < -60, 403, 'Waktu ujian telah habis.');
    }

    private function deadline(ExamAttempt $attempt, ExamSession $session): Carbon
    {
        return Carbon::parse($attempt->started_at)->timezone('Asia/Jakarta')
            ->addMinutes((int) $session->exam->duration_minutes)
            ->min(Carbon::parse($session->end_time)->timezone('Asia/Jakarta'));
    }

    private function remainingSeconds(ExamAttempt $attempt, ExamSession $session): int
    {
        if (! $attempt->started_at) {
            return 0;
        }

        return (int) Carbon::now('Asia/Jakarta')->diffInSeconds($this->deadline($attempt, $session), false);
    }

    private function completeAttempt(ExamAttempt $attempt, string $message)
    {
        $result = app(AttemptScoringService::class)->scoreAttempt($attempt);

        return $this->success($message, [
            'attempt_id' => $attempt->id,
            'status' => 'completed',
            'finished_at' => $attempt->fresh()->finished_at,
            'score' => $result['finalScore'],
        ]);
    }

    private function resultData(ExamAttempt $attempt): array
    {
        $attempt->loadMissing(['session.exam.scoringProfile']);
        $scoring = app(AttemptScoringService::class);
        $sections = $scoring->sectionResults($attempt);
        $mode = $attempt->session->exam->scoringProfile
            ? $scoring->resultMode($attempt->session->exam->scoringProfile)
            : ($sections->first()['result_mode'] ?? 'average');

        return [
            'attempt_id' => $attempt->id,
            'exam' => ['id' => $attempt->session->exam->hashid, 'title' => $attempt->session->exam->title],
            'status' => $attempt->status,
            'average_score' => round((float) ($sections->avg('score') ?? 0), 2),
            'result_mode' => $mode,
            'score' => (float) $attempt->final_score,
            'sections' => $sections->values(),
        ];
    }

    private function sessionData($session): array
    {
        return [
            'id' => $session->exam->hashid,
            'session_id' => $session->id,
            'title' => $session->exam->title,
            'start_time' => $session->start_time,
            'end_time' => $session->end_time,
            'duration_minutes' => (int) $session->exam->duration_minutes,
            'status' => $session->pivot->status,
            'is_open' => now()->between($session->start_time, $session->end_time),
            'final_score' => $session->pivot->final_score,
            'is_locked' => (bool) $session->pivot->is_locked,
            'total_questions' => $session->exam->questions_count,
        ];
    }

    private function success(string $message, mixed $data)
    {
        return response()->json(['success' => true, 'message' => $message, 'data' => $data]);
    }

    private function error(string $message, int $status)
    {
        return response()->json(['success' => false, 'message' => $message, 'errors' => (object) []], $status);
    }
}
