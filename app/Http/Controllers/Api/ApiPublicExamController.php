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

        // ========================================================
        // TAMBAHAN: PENGECEKAN UJIAN PREMIUM
        // ========================================================
        if ($exam->is_premium) {
            // Coba ambil data user dari Token Sanctum yang dikirim Flutter
            $user = auth('sanctum')->user();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda harus login untuk mengakses ujian premium ini.',
                ], 401);
            }

            // Memanggil Accessor getIsPremiumAttribute() di model User
            if (! $user->is_premium) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ujian ini khusus member Premium. Yuk, langganan sekarang!',
                ], 403);
            }
        }
        // ========================================================

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

        // ========================================================
        // 1. SINKRONISASI JAWABAN AKHIR DARI FLUTTER
        // ========================================================
        if ($request->has('final_answers') && ! empty($request->final_answers)) {
            $decodedAnswers = is_string($request->final_answers) ? json_decode($request->final_answers, true) : $request->final_answers;
            if (is_array($decodedAnswers)) {
                $state['answers'] = $decodedAnswers;
            }
        }

        $state['status'] = 'completed';
        $state['finished_at'] = Carbon::now('Asia/Jakarta')->toDateTimeString();
        Cache::put($cacheKey, $state, now()->addHours(6));

        $userData = $state['user_data'] ?? null;

        if ($userData) {
            $questions = $exam->questions()->with(['options', 'matches'])->get();
            $answers = $state['answers'] ?? [];

            $correctCount = 0;
            $wrongCount = 0;
            $unansweredCount = 0;
            $objectiveCount = 0;

            // ========================================================
            // 2. KOREKSI UNIVERSAL (PERSIS SEPERTI DI WEB)
            // ========================================================
            foreach ($questions as $q) {
                $userAnswer = isset($answers[$q->id]) ? $answers[$q->id] : null;

                // A. Decode JSON string ke Array jika perlu
                if (is_string($userAnswer) && (strpos(trim($userAnswer), '{') === 0 || strpos(trim($userAnswer), '[') === 0)) {
                    $decoded = json_decode($userAnswer, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $userAnswer = $decoded;
                    }
                }
                if (is_object($userAnswer)) {
                    $userAnswer = (array) $userAnswer;
                }

                // Tentukan apakah soal ini bisa dikoreksi otomatis (punya kunci jawaban)
                $hasKey = false;
                if (in_array($q->type, ['single_choice', 'complex_choice', 'true_false', 'matching'])) {
                    $hasKey = true;
                } elseif (in_array($q->type, ['essay', 'isian', 'short_answer'])) {
                    if ($q->options && $q->options->where('is_correct', 1)->count() > 0) {
                        $hasKey = true;
                    }
                }

                // Abaikan dari perhitungan jika murni essay manual (tanpa kunci jawaban sama sekali)
                if (! $hasKey) {
                    continue;
                }

                $objectiveCount++;

                // B. Hitung "Tidak Dijawab"
                if ($userAnswer === null || $userAnswer === '' || (is_array($userAnswer) && empty($userAnswer))) {
                    $unansweredCount++;

                    continue;
                }

                // C. Logika Koreksi Berdasarkan Tipe
                if ($q->type === 'single_choice') {
                    $correctOptionId = null;
                    foreach ($q->options as $opt) {
                        if (filter_var($opt->is_correct, FILTER_VALIDATE_BOOLEAN)) {
                            $correctOptionId = (string) $opt->id;
                            break;
                        }
                    }
                    if ($correctOptionId !== null && (string) $userAnswer === $correctOptionId) {
                        $correctCount++;
                    } else {
                        $wrongCount++;
                    }
                } elseif ($q->type === 'true_false' && is_array($userAnswer)) {
                    $isAllCorrect = true;
                    foreach ($q->options as $opt) {
                        $isOptCorrect = filter_var($opt->is_correct, FILTER_VALIDATE_BOOLEAN);
                        $expectedAnswer = $isOptCorrect ? 'benar' : 'salah';

                        $optId = (string) $opt->id;
                        $givenAnswer = isset($userAnswer[$optId]) ? strtolower(trim((string) $userAnswer[$optId])) : null;

                        if ($givenAnswer !== $expectedAnswer) {
                            $isAllCorrect = false;
                            break;
                        }
                    }
                    $isAllCorrect ? $correctCount++ : $wrongCount++;
                } elseif ($q->type === 'complex_choice' && is_array($userAnswer)) {
                    $correctOptionIds = [];
                    foreach ($q->options as $opt) {
                        if (filter_var($opt->is_correct, FILTER_VALIDATE_BOOLEAN)) {
                            $correctOptionIds[] = (string) $opt->id;
                        }
                    }
                    $userStr = array_map('strval', array_values($userAnswer));
                    sort($correctOptionIds);
                    sort($userStr);

                    if ($correctOptionIds === $userStr) {
                        $correctCount++;
                    } else {
                        $wrongCount++;
                    }
                } elseif ($q->type === 'matching' && is_array($userAnswer)) {
                    $isAllCorrect = true;
                    foreach ($q->matches as $match) {
                        $mId = (string) $match->id;
                        $answeredTarget = isset($userAnswer[$mId]) ? (string) $userAnswer[$mId] : null;
                        if ($answeredTarget !== $mId) {
                            $isAllCorrect = false;
                            break;
                        }
                    }
                    $isAllCorrect ? $correctCount++ : $wrongCount++;
                } elseif (in_array($q->type, ['essay', 'isian', 'short_answer'])) {
                    $isCorrect = false;
                    $rawUserText = is_array($userAnswer) ? implode(' ', $userAnswer) : (string) $userAnswer;
                    $cleanUserAnswer = strtolower(trim(html_entity_decode(strip_tags($rawUserText))));

                    $correctOptions = $q->options->where('is_correct', 1);
                    foreach ($correctOptions as $opt) {
                        $cleanOptionText = strtolower(trim(html_entity_decode(strip_tags($opt->option_text))));
                        if ($cleanUserAnswer === $cleanOptionText) {
                            $isCorrect = true;
                            break;
                        }
                    }
                    $isCorrect ? $correctCount++ : $wrongCount++;
                } else {
                    $wrongCount++;
                }
            }

            // D. Hitung Skor berdasarkan jumlah soal yang objektif/bisa dinilai saja
            $score = $objectiveCount > 0 ? round(($correctCount / $objectiveCount) * 100) : 0;
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

            return response()->json([
                'success' => true,
                'data' => [
                    'score' => $score,
                    'correct' => $correctCount,
                    'wrong' => $wrongCount,
                    'unanswered' => $unansweredCount,
                ],
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

    // Perhatikan parameternya: Kita langsung memakai model (\App\Models\Exam $exam)
    public function getRanking(Exam $exam)
    {
        // Karena ada fungsi resolveRouteBinding di model Exam Anda,
        // Laravel OTOMATIS menerjemahkan hashid dari Flutter menjadi ID angka di sini!
        // Jadi $exam->id sudah pasti berisi ID Angka aslinya.

        $rankings = PublicExamResult::where('exam_id', $exam->id)
            ->orderBy('score', 'desc')
            ->orderBy('duration_seconds', 'asc')
            ->take(100) // Ambil Top 100 Nasional
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $rankings,
        ]);
    }
}
