<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\PublicExamResult;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ApiPublicExamController extends Controller
{
    // 1. Mengambil Daftar Ujian
    public function index(Request $request)
    {
        $publicExams = Exam::query()
            ->where('is_public', true)
            ->whereHas('examType', fn ($q) => $q->where('name', 'TKA'))
            ->with(['subject', 'level', 'examType'])
            ->latest()
            ->paginate(9);

        return response()->json(['success' => true, 'data' => ['exams' => $publicExams]]);
    }

    // 2. Memberikan Kode Verifikasi
    public function getVerificationCode(Exam $exam)
    {
        if (! $exam->is_public) {
            return response()->json(['success' => false, 'message' => 'Ujian tidak valid'], 403);
        }

        $token = strtoupper(Str::random(6));
        Cache::put('verify_code_'.$exam->id.'_'.request()->ip(), $token, now()->addMinutes(15));

        return response()->json(['success' => true, 'data' => ['verification_code' => $token]]);
    }

    // 3. Memproses Form Verifikasi
    public function verify(Request $request, Exam $exam)
    {
        $request->validate([
            'nama_peserta' => 'required|string',
            'asal_sekolah' => 'required|string',
            'verification_code' => 'required|string',
        ]);

        $savedCode = Cache::get('verify_code_'.$exam->id.'_'.$request->ip());

        if (! $savedCode || strtoupper($request->verification_code) !== $savedCode) {
            return response()->json(['success' => false, 'message' => 'Token verifikasi salah atau kadaluarsa'], 400);
        }

        $sessionToken = (string) Str::uuid();

        Cache::put('api_user_'.$exam->id.'_'.$sessionToken, [
            'nama_peserta' => $request->nama_peserta,
            'asal_sekolah' => $request->asal_sekolah,
        ], now()->addHours(6));

        Cache::forget('verify_code_'.$exam->id.'_'.$request->ip());

        return response()->json(['success' => true, 'data' => ['session_token' => $sessionToken]]);
    }

    // 4. Memulai Ujian
    public function start(Request $request, Exam $exam)
    {
        $token = $request->input('session_token');
        if (! $token) {
            return response()->json(['success' => false, 'message' => 'Token tidak ditemukan'], 401);
        }

        $userData = Cache::get('api_user_'.$exam->id.'_'.$token);
        if (! $userData) {
            return response()->json(['success' => false, 'message' => 'Silakan verifikasi ulang'], 401);
        }

        $cacheKey = 'api_exam_state_'.$exam->id.'_'.$token;
        $state = Cache::get($cacheKey);
        $now = Carbon::now('Asia/Jakarta');

        if (! $state) {
            $state = [
                'started_at' => $now->toDateTimeString(),
                'status' => 'ongoing',
                'is_locked' => false,
                'violation_count' => 0,
                'answers' => [],
                'flags' => [],
                'finished_at' => null,
                'user_data' => $userData,
            ];
            Cache::put($cacheKey, $state, now()->addHours(6));
        }

        if ($state['is_locked']) {
            return response()->json(['success' => false, 'message' => 'Ujian dikunci karena pelanggaran'], 403);
        }
        if ($state['status'] === 'completed') {
            return response()->json(['success' => false, 'message' => 'Ujian sudah diselesaikan'], 403);
        }

        // Kalkulasi waktu
        $startTime = Carbon::parse($state['started_at'])->timezone('Asia/Jakarta');
        $deadlinePersonal = $startTime->copy()->addMinutes((int) $exam->duration_minutes);
        $realDeadline = $exam->end_time
            ? $deadlinePersonal->min(Carbon::parse($exam->end_time)->timezone('Asia/Jakarta'))
            : $deadlinePersonal;
        $timeLeftSeconds = $now->diffInSeconds($realDeadline, false);

        if ($timeLeftSeconds <= -10) {
            return $this->finish($request, $exam);
        }

        // Soal TANPA is_correct (aman dikirim ke client)
        $questions = $exam->questions()
            ->with([
                'options' => fn ($q) => $q->select(['id', 'question_id', 'option_text']),
                'matches' => fn ($q) => $q->select(['id', 'question_id', 'premise_text', 'target_text']),
            ])
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'questions' => $questions,
                'state' => [
                    'time_left_seconds' => max($timeLeftSeconds, 10),
                    'answers' => empty($state['answers']) ? (object) [] : $state['answers'],
                    'flags' => $state['flags'] ?? [],
                ],
            ],
        ]);
    }

    // 5. Menyimpan Jawaban Real-time
    public function storeAnswer(Request $request, Exam $exam)
    {
        $token = $request->input('session_token');
        $cacheKey = 'api_exam_state_'.$exam->id.'_'.$token;
        $state = Cache::get($cacheKey);

        if (! $state || $state['is_locked'] || $state['status'] !== 'ongoing') {
            return response()->json(['success' => false], 403);
        }

        $qId = $request->question_id;

        if ($request->has('answer')) {
            $state['answers'][$qId] = $request->answer;
        }

        $isDoubtful = filter_var($request->is_doubtful, FILTER_VALIDATE_BOOLEAN);
        if ($isDoubtful && ! in_array($qId, $state['flags'])) {
            $state['flags'][] = $qId;
        } elseif (! $isDoubtful && in_array($qId, $state['flags'])) {
            $state['flags'] = array_values(array_diff($state['flags'], [$qId]));
        }

        Cache::put($cacheKey, $state, now()->addHours(6));

        return response()->json(['success' => true]);
    }

    // 6. Menyelesaikan Ujian & Kalkulasi Nilai
    public function finish(Request $request, Exam $exam)
    {
        $token = $request->input('session_token');
        $cacheKey = 'api_exam_state_'.$exam->id.'_'.$token;
        $state = Cache::get($cacheKey);

        if (! $state || $state['status'] === 'completed') {
            return response()->json(['success' => false, 'message' => 'Ujian sudah selesai atau tidak ditemukan']);
        }

        $state['status'] = 'completed';
        $state['finished_at'] = Carbon::now('Asia/Jakarta')->toDateTimeString();
        Cache::put($cacheKey, $state, now()->addHours(6));

        $userData = $state['user_data'] ?? null;

        if ($userData) {
            $questions = $exam->questions()->with(['options', 'matches'])->get();
            $answers = $state['answers'] ?? [];
            $totalScore = 0;
            $correctCount = 0;
            $wrongCount = 0;
            $unansweredCount = 0;
            $totalQuestions = $questions->count();

            foreach ($questions as $q) {
                $userAnswer = $answers[$q->id] ?? null;

                // Normalisasi: decode JSON string jika perlu
                if (is_string($userAnswer) && in_array($userAnswer[0] ?? '', ['{', '['])) {
                    $userAnswer = json_decode($userAnswer, true) ?? $userAnswer;
                }

                // Normalisasi: object → array (dari JSON Flutter)
                if (is_object($userAnswer)) {
                    $userAnswer = (array) $userAnswer;
                }

                // Cek tidak dijawab
                if ($userAnswer === null || $userAnswer === '' || (is_array($userAnswer) && empty($userAnswer))) {
                    $unansweredCount++;

                    continue;
                }

                $poin = 0;

                if ($q->type === 'single_choice') {
                    // Flutter kirim integer opt id
                    $correctOption = $q->options->firstWhere('is_correct', true);
                    if ($correctOption && $userAnswer == $correctOption->id) {
                        $poin = 1;
                    }

                } elseif ($q->type === 'complex_choice') {
                    // Flutter kirim List of integer opt id
                    $correctIds = $q->options->where('is_correct', true)->pluck('id')->sort()->values()->toArray();
                    $userIds = is_array($userAnswer) ? array_map('intval', $userAnswer) : [];
                    sort($userIds);
                    if ($correctIds == $userIds) {
                        $poin = 1;
                    }

                } elseif (in_array($q->type, ['true_false', 'true_false_multi'])) {
                    // Flutter kirim Map<String optId, String 'benar'/'salah'>
                    // Poin parsial: per opsi yang benar
                    $totalOptions = $q->options->count();
                    $correctMatches = 0;
                    $userAnswers = is_array($userAnswer) ? $userAnswer : [];

                    foreach ($q->options as $opt) {
                        $expected = filter_var($opt->is_correct, FILTER_VALIDATE_BOOLEAN) ? 'benar' : 'salah';
                        $userValue = isset($userAnswers[(string) $opt->id])
                            ? strtolower(trim((string) $userAnswers[(string) $opt->id]))
                            : null;
                        if ($userValue === $expected) {
                            $correctMatches++;
                        }
                    }

                    if ($totalOptions > 0) {
                        $poin = $correctMatches / $totalOptions;
                    }

                } elseif ($q->type === 'matching') {
                    // Flutter kirim Map<String premiseId, int targetId>
                    // Poin parsial: per pasangan yang benar
                    $totalPairs = $q->matches->count();
                    $correctPairs = 0;
                    $userAnswers = is_array($userAnswer) ? $userAnswer : [];

                    if ($totalPairs > 0) {
                        foreach ($q->matches as $match) {
                            $answered = $userAnswers[(string) $match->id] ?? null;
                            if ($answered !== null && (int) $answered === (int) $match->correct_target_id) {
                                $correctPairs++;
                            }
                        }
                        $poin = $correctPairs / $totalPairs;
                    }

                } elseif ($q->type === 'essay') {
                    // Flutter kirim string dari TextField
                    $cleanUser = preg_replace('/\s+/', ' ', trim(strip_tags(is_string($userAnswer) ? $userAnswer : '')));

                    $poin = $q->options
                        ->where('is_correct', 1)
                        ->contains(function ($opt) use ($cleanUser) {
                            $cleanCorrect = preg_replace('/\s+/', ' ', trim(strip_tags(html_entity_decode($opt->option_text ?? ''))));

                            return strcasecmp($cleanCorrect, $cleanUser) === 0
                                || (is_numeric($cleanCorrect) && is_numeric($cleanUser) && (float) $cleanCorrect === (float) $cleanUser);
                        }) ? 1 : 0;
                }

                $totalScore += $poin;
                $poin === 1 ? $correctCount++ : $wrongCount++;
            }

            $score = $totalQuestions > 0 ? round(($totalScore / $totalQuestions) * 100, 2) : 0;
            $durationSeconds = Carbon::parse($state['finished_at'])->diffInSeconds(Carbon::parse($state['started_at']));

            PublicExamResult::create([
                'exam_id' => $exam->id,
                'nama_peserta' => $userData['nama_peserta'],
                'asal_sekolah' => $userData['asal_sekolah'],
                'score' => $score,
                'correct_count' => $correctCount,
                'wrong_count' => $wrongCount,
                'unanswered_count' => $unansweredCount,
                'duration_seconds' => $durationSeconds,
            ]);
        }

        return response()->json(['success' => true]);
    }

    // 7. Mencatat Pelanggaran
    public function recordViolation(Request $request, Exam $exam)
    {
        $token = $request->input('session_token');
        $cacheKey = 'api_exam_state_'.$exam->id.'_'.$token;
        $state = Cache::get($cacheKey);

        if (! $state || $state['is_locked']) {
            return response()->json(['success' => false, 'error' => 'Gagal mencatat pelanggaran'], 400);
        }

        $state['violation_count'] += 1;
        $max = $exam->max_tolerances ?? 3;
        $isLocked = $state['violation_count'] >= $max;

        if ($isLocked) {
            $state['is_locked'] = true;
        }

        Cache::put($cacheKey, $state, now()->addHours(6));

        return response()->json([
            'success' => true,
            'violation_count' => $state['violation_count'],
            'max_tolerances' => $max,
            'is_locked' => $state['is_locked'],
        ]);
    }
}
