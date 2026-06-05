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
            ->whereHas('examType', function ($query) {
                $query->where('name', 'TKA');
            })
            ->with(['subject', 'level', 'examType'])
            ->latest()
            ->paginate(9);

        return response()->json([
            'success' => true,
            'data' => [
                'exams' => $publicExams,
            ],
        ]);
    }

    // 2. Memberikan Kode Captcha/Verifikasi
    public function getVerificationCode(Exam $exam)
    {
        if (! $exam->is_public) {
            return response()->json(['success' => false, 'message' => 'Ujian tidak valid'], 403);
        }

        $token = strtoupper(Str::random(6));

        // Simpan token sementara di Cache menggunakan IP sebagai penanda
        Cache::put('verify_code_'.$exam->id.'_'.request()->ip(), $token, now()->addMinutes(15));

        return response()->json([
            'success' => true,
            'data' => [
                'verification_code' => $token,
            ],
        ]);
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

        // Buat Session Token Unik (UUID) untuk Flutter
        $sessionToken = (string) Str::uuid();

        // Simpan Data Peserta ke Cache
        Cache::put('api_user_'.$exam->id.'_'.$sessionToken, [
            'nama_peserta' => $request->nama_peserta,
            'asal_sekolah' => $request->asal_sekolah,
        ], now()->addHours(6));

        // Hapus cache captcha
        Cache::forget('verify_code_'.$exam->id.'_'.$request->ip());

        return response()->json([
            'success' => true,
            'data' => [
                'session_token' => $sessionToken,
            ],
        ]);
    }

    // 4. Memulai Ujian (Adaptasi dari show di Controller Web)
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

        // Jika ujian baru pertama kali dibuka, buat sesi State-nya
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

        // Cek status blokir/selesai
        if ($state['is_locked']) {
            return response()->json(['success' => false, 'message' => 'Ujian dikunci karena pelanggaran']);
        }
        if ($state['status'] === 'completed') {
            return response()->json(['success' => false, 'message' => 'Ujian sudah diselesaikan']);
        }

        // Kalkulasi Waktu (Sama seperti Web)
        $startTime = Carbon::parse($state['started_at'])->timezone('Asia/Jakarta');
        $duration = (int) $exam->duration_minutes;
        $deadlinePersonal = $startTime->copy()->addMinutes($duration);

        $realDeadline = $exam->end_time
            ? $deadlinePersonal->min(Carbon::parse($exam->end_time)->timezone('Asia/Jakarta'))
            : $deadlinePersonal;

        $timeLeftSeconds = $now->diffInSeconds($realDeadline, false);

        // Jika waktu habis, auto-finish
        if ($timeLeftSeconds <= -10) {
            return $this->finish($request, $exam);
        }

        // Ambil Data Soal (Sama seperti Web)
        $questions = $exam->questions()->with(['options', 'matches'])->get();

        return response()->json([
            'success' => true,
            'data' => [
                'questions' => $questions,
                'state' => [
                    'time_left_seconds' => $timeLeftSeconds > 0 ? $timeLeftSeconds : 10,
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

        if ($state && ! $state['is_locked'] && $state['status'] === 'ongoing') {
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

        return response()->json(['success' => false], 403);
    }

    // 6. Menyelesaikan Ujian dan Perhitungan Nilai (Diadopsi dari web finish())
    public function finish(Request $request, Exam $exam)
    {
        $token = $request->input('session_token');
        $cacheKey = 'api_exam_state_'.$exam->id.'_'.$token;
        $state = Cache::get($cacheKey);

        if ($state && $state['status'] !== 'completed') {
            $state['status'] = 'completed';
            $state['finished_at'] = Carbon::now('Asia/Jakarta')->toDateTimeString();
            Cache::put($cacheKey, $state, now()->addHours(6));

            $userData = $state['user_data'] ?? null;
            if ($userData) {
                $questions = $exam->questions()->with('options')->get();
                $correctCount = 0;
                $wrongCount = 0;
                $unansweredCount = 0;
                $answers = $state['answers'] ?? [];

                // Hitung Skor persis seperti Web
                // Hitung Skor persis seperti Web
                foreach ($questions as $q) {
                    if (! isset($answers[$q->id]) || empty($answers[$q->id])) {
                        $unansweredCount++;

                        continue;
                    }

                    $userAnswer = $answers[$q->id];

                    // 1. Pilihan Ganda Biasa
                    if ($q->type === 'single_choice') {
                        $correctOption = $q->options->where('is_correct', 1)->first();
                        ($correctOption && $userAnswer == $correctOption->id) ? $correctCount++ : $wrongCount++;
                    }
                    // 2. Benar Salah (Array Pernyataan {"50":"benar", "51":"salah"})
                    elseif ($q->type === 'true_false' && is_array($userAnswer)) {
                        $isAllCorrect = true;
                        foreach ($q->options as $opt) {
                            $expectedAnswer = $opt->is_correct ? 'benar' : 'salah';
                            $givenAnswer = isset($userAnswer[$opt->id]) ? strtolower($userAnswer[$opt->id]) : null;

                            if ($givenAnswer !== $expectedAnswer) {
                                $isAllCorrect = false;
                                break;
                            }
                        }
                        $isAllCorrect ? $correctCount++ : $wrongCount++;
                    }
                    // 3. Pilihan Ganda Kompleks (Checkbox)
                    elseif ($q->type === 'complex_choice' && is_array($userAnswer)) {
                        $correctOptionIds = $q->options->where('is_correct', 1)->pluck('id')->toArray();
                        (count(array_diff($correctOptionIds, $userAnswer)) === 0 && count(array_diff($userAnswer, $correctOptionIds)) === 0) ? $correctCount++ : $wrongCount++;
                    }
                    // 4. Menjodohkan (Array Pasangan {"1":1, "2":2})
                    elseif ($q->type === 'matching' && is_array($userAnswer)) {
                        $isAllCorrect = true;
                        foreach ($q->matches as $match) {
                            // Cek apakah target id yang dijawab sama dengan id target yang seharusnya
                            $answeredTarget = isset($userAnswer[$match->id]) ? $userAnswer[$match->id] : null;
                            if ($answeredTarget != $match->id) {
                                $isAllCorrect = false;
                                break;
                            }
                        }
                        $isAllCorrect ? $correctCount++ : $wrongCount++;
                    } else {
                        $wrongCount++;
                    }
                }

                $totalQuestions = $questions->count();
                $score = $totalQuestions > 0 ? round(($correctCount / $totalQuestions) * 100) : 0;

                $startedAt = Carbon::parse($state['started_at']);
                $finishedAt = Carbon::parse($state['finished_at']);
                $durationSeconds = $finishedAt->diffInSeconds($startedAt);

                // Masukkan ke Database!
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

        return response()->json(['success' => false]);
    }

    // 7. Mencatat pelanggaran (Diadopsi dari Web recordViolation)
    public function recordViolation(Request $request, Exam $exam)
    {
        $token = $request->input('session_token');
        $cacheKey = 'api_exam_state_'.$exam->id.'_'.$token;
        $state = Cache::get($cacheKey);

        if ($state && ! $state['is_locked']) {
            $state['violation_count'] += 1;
            $max = $exam->max_tolerances ?? 3;

            if ($state['violation_count'] >= $max) {
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

        return response()->json(['success' => false, 'error' => 'Gagal mencatat pelanggaran'], 400);
    }
}
