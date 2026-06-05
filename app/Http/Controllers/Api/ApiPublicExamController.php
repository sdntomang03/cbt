<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\PublicExamResult;
use App\Models\Subject;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ApiPublicExamController extends Controller
{
    /**
     * Mengambil prefix cache untuk sesi ujian
     */
    private function getCacheKey($examId, $token)
    {
        return "api_public_exam_{$examId}_{$token}";
    }

    /**
     * 1. Daftar Ujian Publik
     */
    public function index(Request $request)
    {
        $subjects = Subject::whereHas('exams', function ($query) {
            $query->where('is_public', true)
                ->whereHas('examType', function ($q) {
                    $q->where('name', 'TKA');
                });
        })->orderBy('name', 'asc')->get();

        $publicExams = Exam::query()
            ->where('is_public', true)
            ->whereHas('examType', function ($query) {
                $query->where('name', 'TKA');
            })
            ->with(['subject', 'level', 'examType'])
            ->when($request->filled('subject'), function ($query) use ($request) {
                $query->where('subject_id', $request->subject);
            })
            ->latest()
            ->paginate(9);

        return response()->json([
            'success' => true,
            'message' => 'Daftar ujian publik berhasil diambil',
            'data' => [
                'subjects' => $subjects,
                'exams' => $publicExams,
            ],
        ]);
    }

    /**
     * 2. Detail Ujian berdasarkan Slug
     */
    public function detail($slug)
    {
        $exam = Exam::where('slug', $slug)
            ->where('is_public', true)
            ->withCount('questions')
            ->first();

        if (! $exam) {
            return response()->json(['success' => false, 'message' => 'Ujian tidak ditemukan'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => ['exam' => $exam],
        ]);
    }

    /**
     * 3. Permintaan Verifikasi (Mendapatkan kode captcha/token pendaftaran)
     */
    public function verify(Exam $exam)
    {
        if (! $exam->is_public) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $verificationCode = strtoupper(Str::random(6));

        // Simpan kode verifikasi sementara selama 10 menit
        Cache::put("verify_code_{$exam->id}", $verificationCode, now()->addMinutes(10));

        return response()->json([
            'success' => true,
            'message' => 'Gunakan kode verifikasi ini untuk mendaftar',
            'data' => [
                'exam_id' => $exam->id,
                'verification_code' => $verificationCode, // Di production, jadikan gambar/captcha
            ],
        ]);
    }

    /**
     * 4. Proses Verifikasi & Mendapatkan Session Token Ujian
     */
    public function processVerify(Request $request, Exam $exam)
    {
        if (! $exam->is_public) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'nama_peserta' => 'required|string|max:100',
            'asal_sekolah' => 'required|string|max:100',
            'verification_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $correctCode = Cache::get("verify_code_{$exam->id}");

        if (! $correctCode || strtoupper($request->verification_code) !== $correctCode) {
            return response()->json(['success' => false, 'message' => 'Kode verifikasi salah atau kadaluarsa.'], 400);
        }

        Cache::forget("verify_code_{$exam->id}");

        // Generate Session Token untuk client
        $sessionToken = (string) Str::uuid();
        $cacheKey = $this->getCacheKey($exam->id, $sessionToken);

        Cache::put($cacheKey.'_user', [
            'nama_peserta' => $request->nama_peserta,
            'asal_sekolah' => $request->asal_sekolah,
        ], now()->addHours(4));

        return response()->json([
            'success' => true,
            'message' => 'Verifikasi berhasil. Gunakan token ini untuk memulai ujian.',
            'data' => [
                'session_token' => $sessionToken,
            ],
        ]);
    }

    /**
     * 5. Menjalankan / Mengambil Data Soal Ujian
     */
    public function start(Request $request, Exam $exam)
    {
        if (! $exam->is_public) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $token = $request->input('session_token');
        $cacheKey = $this->getCacheKey($exam->id, $token);
        $userData = Cache::get($cacheKey.'_user');

        if (! $userData) {
            return response()->json(['success' => false, 'message' => 'Token tidak valid atau kadaluarsa.'], 401);
        }

        $now = Carbon::now('Asia/Jakarta');
        $state = Cache::get($cacheKey.'_state');

        // Inisialisasi State Jika Belum Ada
        if (! $state) {
            $state = [
                'started_at' => $now->toDateTimeString(),
                'status' => 'ongoing',
                'is_locked' => false,
                'violation_count' => 0,
                'answers' => [],
                'flags' => [],
                'finished_at' => null,
            ];
            Cache::put($cacheKey.'_state', $state, now()->addHours(4));
        }

        // Validasi Blokir & Selesai
        if ($state['is_locked']) {
            return response()->json(['success' => false, 'message' => 'Ujian terkunci karena pelanggaran keamanan.', 'status' => 'locked'], 403);
        }
        if ($state['status'] === 'completed') {
            return response()->json(['success' => false, 'message' => 'Ujian telah diselesaikan.', 'status' => 'completed'], 400);
        }

        // Perhitungan Waktu
        $startTime = Carbon::parse($state['started_at'])->timezone('Asia/Jakarta');
        $deadlinePersonal = $startTime->copy()->addMinutes((int) $exam->duration_minutes);
        $realDeadline = $exam->end_time ? $deadlinePersonal->min(Carbon::parse($exam->end_time)->timezone('Asia/Jakarta')) : $deadlinePersonal;

        $timeLeftSeconds = $now->diffInSeconds($realDeadline, false);

        if ($timeLeftSeconds <= -60) {
            return $this->finish($request, $exam); // Otomatis diselesaikan
        }

        // Ambil Data Soal (PENTING: Hapus field 'is_correct' agar tidak bocor ke frontend)
        $questions = $exam->questions()->with(['options', 'matches'])->get()->map(function ($q) {
            $q->options->makeHidden(['is_correct', 'score']); // Keamanan API

            return $q;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'exam' => $exam,
                'user' => $userData,
                'state' => [
                    'time_left_seconds' => max(0, $timeLeftSeconds),
                    'violation_count' => $state['violation_count'],
                    'answers' => $state['answers'],
                    'flags' => $state['flags'],
                ],
                'questions' => $questions,
                'config' => [
                    'random_question' => $exam->random_question ?? false,
                    'random_answer' => $exam->random_answer ?? false,
                    'enable_violation' => $exam->enable_violation ?? true,
                    'max_tolerances' => $exam->max_tolerances ?? 3,
                ],
            ],
        ]);
    }

    /**
     * 6. Menyimpan Jawaban Sementara
     */
    public function storeAnswer(Request $request, Exam $exam)
    {
        $token = $request->input('session_token');
        $cacheKey = $this->getCacheKey($exam->id, $token);
        $state = Cache::get($cacheKey.'_state');

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

            Cache::put($cacheKey.'_state', $state, now()->addHours(4));

            return response()->json(['success' => true, 'message' => 'Jawaban tersimpan']);
        }

        return response()->json(['success' => false, 'message' => 'Sesi tidak valid atau terkunci'], 403);
    }

    /**
     * 7. Mencatat Pelanggaran (Meninggalkan layar)
     */
    public function recordViolation(Request $request, Exam $exam)
    {
        $token = $request->input('session_token');
        $cacheKey = $this->getCacheKey($exam->id, $token);
        $state = Cache::get($cacheKey.'_state');

        if ($state && ! $state['is_locked']) {
            $state['violation_count'] += 1;
            $max = $exam->max_tolerances ?? 3;

            if ($state['violation_count'] >= $max) {
                $state['is_locked'] = true;
            }

            Cache::put($cacheKey.'_state', $state, now()->addHours(4));

            return response()->json([
                'success' => true,
                'data' => [
                    'violation_count' => $state['violation_count'],
                    'max_tolerances' => $max,
                    'is_locked' => $state['is_locked'],
                ],
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Gagal mencatat pelanggaran'], 400);
    }

    /**
     * 8. Menyelesaikan Ujian
     */
    public function finish(Request $request, Exam $exam)
    {
        $token = $request->input('session_token');
        $cacheKey = $this->getCacheKey($exam->id, $token);
        $state = Cache::get($cacheKey.'_state');
        $userData = Cache::get($cacheKey.'_user');

        if (! $state || ! $userData) {
            return response()->json(['success' => false, 'message' => 'Sesi tidak valid'], 401);
        }

        if ($state['status'] !== 'completed') {
            $state['status'] = 'completed';
            $state['finished_at'] = Carbon::now('Asia/Jakarta')->toDateTimeString();
            Cache::put($cacheKey.'_state', $state, now()->addHours(4));

            $questions = $exam->questions()->with('options')->get();
            $correctCount = 0;
            $wrongCount = 0;
            $unansweredCount = 0;
            $answers = $state['answers'] ?? [];

            foreach ($questions as $q) {
                if (! isset($answers[$q->id]) || $answers[$q->id] === '') {
                    $unansweredCount++;

                    continue;
                }

                $userAnswer = $answers[$q->id];
                if (in_array($q->type, ['single_choice', 'true_false'])) {
                    $correctOption = $q->options->where('is_correct', 1)->first();
                    ($correctOption && $userAnswer == $correctOption->id) ? $correctCount++ : $wrongCount++;
                } elseif ($q->type === 'complex_choice' && is_array($userAnswer)) {
                    $correctOptionIds = $q->options->where('is_correct', 1)->pluck('id')->toArray();
                    (count(array_diff($correctOptionIds, $userAnswer)) === 0 && count(array_diff($userAnswer, $correctOptionIds)) === 0) ? $correctCount++ : $wrongCount++;
                } else {
                    $wrongCount++;
                }
            }

            $totalQuestions = $questions->count();
            $score = $totalQuestions > 0 ? round(($correctCount / $totalQuestions) * 100) : 0;
            $durationSeconds = Carbon::parse($state['finished_at'])->diffInSeconds(Carbon::parse($state['started_at']));

            // Simpan ke DB
            $result = PublicExamResult::create([
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
                'message' => 'Ujian selesai.',
                'data' => $result,
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Ujian sudah diselesaikan sebelumnya.'], 400);
    }

    /**
     * 9. Menampilkan Ranking
     */
    public function ranking(Exam $exam)
    {
        if (! $exam->is_public) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $results = PublicExamResult::where('exam_id', $exam->id)
            ->orderBy('score', 'desc')
            ->orderBy('duration_seconds', 'asc')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($item, $index) {
                $item->rank = $index + 1;

                return $item;
            });

        return response()->json([
            'success' => true,
            'data' => ['ranking' => $results],
        ]);
    }

    /**
     * 10. Restart / Mengulang Ujian
     */
    public function restart(Request $request, Exam $exam)
    {
        $token = $request->input('session_token');
        $cacheKey = $this->getCacheKey($exam->id, $token);

        // Hapus state lama agar ujian bisa dimulai ulang
        Cache::forget($cacheKey.'_state');

        return response()->json([
            'success' => true,
            'message' => 'Sesi ujian telah direset. Anda dapat memulai ulang ujian.',
        ]);
    }
}
