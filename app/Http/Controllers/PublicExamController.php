<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\PublicExamResult;
use App\Models\Subject;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublicExamController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil Mata Pelajaran yang HANYA memiliki ujian publik dengan tipe 'TKA'
        $subjects = Subject::whereHas('exams', function ($query) {
            $query->where('is_public', true)
                ->whereHas('examType', function ($q) {
                    $q->where('name', 'TKA');
                });
        })->orderBy('name', 'asc')->get();

        // 2. Query utama untuk mengambil data ujian
        $publicExams = Exam::query()
            ->where('is_public', true)
            // Memfilter berdasarkan relasi ExamType yang namanya 'TKA'
            ->whereHas('examType', function ($query) {
                $query->where('name', 'TKA');
            })
            // Tambahkan 'examType' agar tidak terjadi N+1 Query Problem
            ->with(['subject', 'level', 'examType'])
            // Filter berdasarkan subject yang dipilih dari URL
            ->when($request->filled('subject'), function ($query) use ($request) {
                $query->where('subject_id', $request->subject);
            })
            ->latest()
            ->paginate(9);

        // Hapus 'levels' dari compact karena sudah tidak digunakan
        return view('public.exams.index', compact('publicExams', 'subjects'));
    }

    public function restart(Exam $exam)
    {
        abort_if(! $exam->is_public, 403);

        // Hapus memori ujian ini dari session browser
        session()->forget('public_exam_state_'.$exam->id);

        // Arahkan kembali ke halaman mulai ujian
        return redirect()->route('public.exams.show', $exam);
    }

    /**
     * Menjalankan antarmuka ujian publik
     */
    public function show(Exam $exam)
    {
        abort_if(! $exam->is_public, 403, 'AKSES DITOLAK: Ujian ini hanya untuk siswa internal.');

        if (! session()->has('public_user_'.$exam->id)) {
            return redirect()->route('public.exams.verify', $exam)
                ->with('error', 'Silakan isi data diri Anda terlebih dahulu.');
        }

        $now = Carbon::now('Asia/Jakarta');
        $sessionKey = 'public_exam_state_'.$exam->id;

        // Inisialisasi State Session
        if (! session()->has($sessionKey)) {
            session()->put($sessionKey, [
                'started_at' => $now->toDateTimeString(),
                'status' => 'ongoing',
                'is_locked' => false,
                'violation_count' => 0,
                'answers' => [],
                'flags' => [],
                'finished_at' => null,
            ]);
        }

        $state = session()->get($sessionKey);

        // Polling Status Ujian (AJAX)
        if (request()->ajax()) {
            return response()->json([
                'status' => $state['status'],
                'is_locked' => (bool) $state['is_locked'],
            ]);
        }

        // Validasi Blokir & Selesai
        if ($state['is_locked']) {
            session()->forget('public_verified_exam_'.$exam->id);

            return redirect()->route('welcome')->with('error', 'Ujian Anda telah dikunci karena pelanggaran keamanan.');
        }
        if ($state['status'] === 'completed' || $state['finished_at'] !== null) {
            return redirect()->route('public.exams.result', $exam);
        }

        // Perhitungan Waktu
        $startTime = Carbon::parse($state['started_at'])->timezone('Asia/Jakarta');
        $duration = (int) $exam->duration_minutes;
        $deadlinePersonal = $startTime->copy()->addMinutes($duration);

        $realDeadline = $exam->end_time
            ? $deadlinePersonal->min(Carbon::parse($exam->end_time)->timezone('Asia/Jakarta'))
            : $deadlinePersonal;

        $timeLeftSeconds = $now->diffInSeconds($realDeadline, false);

        if ($timeLeftSeconds <= 0 && $timeLeftSeconds > -60) {
            $timeLeftSeconds = 60;
        } elseif ($timeLeftSeconds <= -60) {
            return $this->finish($exam);
        }

        // Ambil Data Soal Lengkap
        $questions = $exam->questions()
            ->with(['options', 'matches'])
            ->get()
            ->keyBy('id');

        $questionIds = $questions->keys()->toArray();

        $pivotMock = (object) [
            'status' => $state['status'],
            'is_locked' => $state['is_locked'],
            'violation_count' => $state['violation_count'],
        ];

        $config = [
            'random_question' => $exam->random_question ?? false,
            'random_answer' => $exam->random_answer ?? false,
            'enable_violation' => $exam->enable_violation ?? true,
            'max_tolerances' => $exam->max_tolerances ?? 3,
        ];
        $userData = session()->get('public_user_'.$exam->id);

        return view('public.exams.run', [
            'exam' => $exam,
            'questions' => $questions,
            'questionIds' => $questionIds,
            'config' => $config,
            'timeLeftSeconds' => (int) $timeLeftSeconds,
            'existingAnswers' => $state['answers'] ?? [],
            'flags' => $state['flags'] ?? [],
            'pivot' => $pivotMock,
            'userData' => $userData,
        ]);
    }

    /**
     * Menyimpan jawaban (AJAX)
     */
    public function storeAnswer(Request $request, Exam $exam)
    {
        abort_if(! $exam->is_public, 403);
        $sessionKey = 'public_exam_state_'.$exam->id;
        $state = session()->get($sessionKey);

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

            session()->put($sessionKey, $state);

            return response()->json(['success' => true]);
        }

        return response()->json(['error' => 'Sesi tidak valid'], 403);
    }

    /**
     * Mencatat pelanggaran layar (AJAX)
     */
    public function recordViolation(Request $request, Exam $exam)
    {
        abort_if(! $exam->is_public, 403);
        $sessionKey = 'public_exam_state_'.$exam->id;
        $state = session()->get($sessionKey);

        if ($state && ! $state['is_locked']) {
            $state['violation_count'] += 1;
            $max = $exam->max_tolerances ?? 3;

            if ($state['violation_count'] >= $max) {
                $state['is_locked'] = true;
            }

            session()->put($sessionKey, $state);

            return response()->json([
                'violation_count' => $state['violation_count'],
                'max_tolerances' => $max,
                'is_locked' => $state['is_locked'],
            ]);
        }

        return response()->json(['error' => 'Gagal mencatat pelanggaran'], 400);
    }

    /**
     * Memproses penyelesaian ujian
     */
    public function finish(Request $request, Exam $exam)
    {
        abort_if(! $exam->is_public, 403);
        $sessionKey = 'public_exam_state_'.$exam->id;
        $state = session()->get($sessionKey);
        $userData = session()->get('public_user_'.$exam->id);

        if ($state && $state['status'] !== 'completed') {

            // ========================================================
            // 1. SINKRONISASI JAWABAN WEB
            // ========================================================
            if ($request->has('final_answers') && ! empty($request->final_answers)) {
                $decodedAnswers = is_string($request->final_answers) ? json_decode($request->final_answers, true) : $request->final_answers;
                if (is_array($decodedAnswers)) {
                    $state['answers'] = $decodedAnswers;
                }
            }

            $state['status'] = 'completed';
            $state['finished_at'] = Carbon::now('Asia/Jakarta')->toDateTimeString();
            session()->put($sessionKey, $state);

            // ==========================================
            // KALKULASI & SIMPAN KE DATABASE RANKING
            // ==========================================
            if ($userData) {
                // PASTIKAN LOAD RELATION 'matches'
                $questions = $exam->questions()->with(['options', 'matches'])->get();
                $correctCount = 0;
                $wrongCount = 0;
                $unansweredCount = 0;
                $answers = $state['answers'] ?? [];

                foreach ($questions as $q) {
                    $userAnswer = isset($answers[$q->id]) ? $answers[$q->id] : null;

                    if (is_string($userAnswer) && (strpos($userAnswer, '{') === 0 || strpos($userAnswer, '[') === 0)) {
                        $userAnswer = json_decode($userAnswer, true) ?? $userAnswer;
                    }
                    if (is_object($userAnswer)) {
                        $userAnswer = (array) $userAnswer;
                    }

                    if ($userAnswer === null || $userAnswer === '' || (is_array($userAnswer) && empty($userAnswer))) {
                        $unansweredCount++;

                        continue;
                    }

                    if ($q->type === 'single_choice') {
                        $correctOption = $q->options->where('is_correct', 1)->first();
                        if ($correctOption && $userAnswer == $correctOption->id) {
                            $correctCount++;
                        } else {
                            $wrongCount++;
                        }
                    } elseif ($q->type === 'true_false' && is_array($userAnswer)) {
                        $isAllCorrect = true;
                        foreach ($q->options as $opt) {
                            $isOptCorrect = filter_var($opt->is_correct, FILTER_VALIDATE_BOOLEAN);
                            $expectedAnswer = $isOptCorrect ? 'benar' : 'salah';
                            $givenAnswer = isset($userAnswer[$opt->id]) ? strtolower(trim((string) $userAnswer[$opt->id])) : null;

                            if ($givenAnswer !== $expectedAnswer) {
                                $isAllCorrect = false;
                                break;
                            }
                        }
                        $isAllCorrect ? $correctCount++ : $wrongCount++;
                    } elseif ($q->type === 'complex_choice' && is_array($userAnswer)) {
                        $correctOptionIds = $q->options->where('is_correct', 1)->pluck('id')->toArray();
                        $correctStr = array_map('strval', $correctOptionIds);
                        $userStr = array_map('strval', $userAnswer);

                        if (count(array_diff($correctStr, $userStr)) === 0 && count(array_diff($userStr, $correctStr)) === 0) {
                            $correctCount++;
                        } else {
                            $wrongCount++;
                        }
                    } elseif ($q->type === 'matching' && is_array($userAnswer)) {
                        $isAllCorrect = true;
                        foreach ($q->matches as $match) {
                            $answeredTarget = isset($userAnswer[$match->id]) ? $userAnswer[$match->id] : null;
                            if ($answeredTarget != $match->id) {
                                $isAllCorrect = false;
                                break;
                            }
                        }
                        $isAllCorrect ? $correctCount++ : $wrongCount++;
                    } elseif ($q->type === 'essay') {
    $correctRaw = $q->options->first()->option_text ?? '';
    $cleanCorrect = trim(strip_tags(html_entity_decode($correctRaw)));
    $cleanUser = trim(strip_tags($userAnswer));

    $isCorrect = false;

    if (strcasecmp($cleanCorrect, $cleanUser) === 0) {
        $isCorrect = true;
    } elseif (is_numeric($cleanCorrect) && is_numeric($cleanUser)) {
        if ((float) $cleanCorrect === (float) $cleanUser) {
            $isCorrect = true;
        }
    } else {
                        $wrongCount++;
                    }
                }

                $totalQuestions = $questions->count();
                $score = $totalQuestions > 0 ? round(($correctCount / $totalQuestions) * 100) : 0;

                $startedAt = Carbon::parse($state['started_at']);
                $finishedAt = Carbon::parse($state['finished_at']);
                $durationSeconds = $finishedAt->diffInSeconds($startedAt);

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
        }

        return redirect()->route('public.exams.result', $exam);
    }

    /**
     * Menampilkan hasil/nilai ujian
     */
    public function result(Exam $exam)
    {
        abort_if(! $exam->is_public, 403);
        $sessionKey = 'public_exam_state_'.$exam->id;
        $state = session()->get($sessionKey);

        if (! $state || $state['status'] !== 'completed') {
            return redirect()->route('public.exams.index');
        }

        // PASTIKAN LOAD RELATION 'matches'
        $questions = $exam->questions()->with(['options', 'matches'])->get();
        $totalQuestions = $questions->count();
        $correctCount = 0;
        $wrongCount = 0;
        $unansweredCount = 0;
        $answers = $state['answers'] ?? [];

        // ========================================================
        // KOREKSI UNIVERSAL (ANTI-ERROR TIPE DATA)
        // ========================================================
        foreach ($questions as $q) {
            $userAnswer = isset($answers[$q->id]) ? $answers[$q->id] : null;

            // A. Pastikan format JSON string di-decode ke Array dengan aman
            if (is_string($userAnswer) && (strpos(trim($userAnswer), '{') === 0 || strpos(trim($userAnswer), '[') === 0)) {
                $decoded = json_decode($userAnswer, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $userAnswer = $decoded;
                }
            }
            if (is_object($userAnswer)) {
                $userAnswer = (array) $userAnswer;
            }

            // B. Hitung sebagai "Tidak Dijawab" jika kosong
            if ($userAnswer === null || $userAnswer === '' || (is_array($userAnswer) && empty($userAnswer))) {
                $unansweredCount++;

                continue;
            }

            // C. Logika Koreksi Berdasarkan Tipe
            if ($q->type === 'single_choice') {
                // Cari opsi yang is_correct bernilai true / 1 / "1" secara fleksibel
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

                // Ekstrak hanya value-nya dan jadikan string
                $userStr = array_map('strval', array_values($userAnswer));

                // Urutkan array agar perbandingan posisi tidak mempengaruhi hasil
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
            } else {
                // Tipe soal essay otomatis masuk sini karena harus dinilai guru manual
                $wrongCount++;
            }
        }

        $score = $totalQuestions > 0 ? round(($correctCount / $totalQuestions) * 100) : 0;

        return view('public.exams.result', compact('exam', 'score', 'correctCount', 'wrongCount', 'unansweredCount', 'totalQuestions'));
    }

    /**
     * Menampilkan Halaman Verifikasi
     */
    public function verify(Exam $exam)
    {
        abort_if(! $exam->is_public, 403);

        if (session()->has('public_user_'.$exam->id)) {
            return redirect()->route('public.exams.show', $exam);
        }

        // Generate token acak & simpan di session
        $token = strtoupper(Str::random(6));
        session()->put('exam_token_'.$exam->id, $token);

        return view('public.exams.verify', compact('exam', 'token'));
    }

    public function processVerify(Request $request, Exam $exam)
    {
        abort_if(! $exam->is_public, 403);

        $request->validate([
            'nama_peserta' => 'required|string|max:100',
            'asal_sekolah' => 'required|string|max:100',
            'token' => 'required|string',
        ]);

        $correctToken = session('exam_token_'.$exam->id);

        // Jika session expired / tidak ada
        if (! $correctToken) {
            return redirect()->route('public.exams.verify', $exam)
                ->with('error', 'Sesi token habis, silakan muat ulang halaman.');
        }

        if (strtoupper($request->token) !== $correctToken) {
            return back()->withInput()->with('error', 'Token yang Anda masukkan salah!');
        }

        // Hapus token dari session setelah berhasil
        session()->forget('exam_token_'.$exam->id);

        session()->put('public_user_'.$exam->id, [
            'nama_peserta' => $request->nama_peserta,
            'asal_sekolah' => $request->asal_sekolah,
        ]);

        return redirect()->route('public.exams.show', $exam);
    }

    public function ranking(Exam $exam)
    {
        abort_if(! $exam->is_public, 403);

        $results = PublicExamResult::where('exam_id', $exam->id)
            ->orderBy('score', 'desc')
            ->orderBy('duration_seconds', 'asc') // tie-breaker: waktu lebih cepat = lebih tinggi
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($item, $index) {
                $item->rank = $index + 1;

                return $item;
            });

        $userResult = null;
        $userData = session('public_user_'.$exam->id);
        if ($userData) {
            $userResult = $results->firstWhere('nama_peserta', $userData['nama_peserta']);
        }

        return view('public.exams.ranking', compact('exam', 'results', 'userResult'));
    }

    public function detail($slug)
    {
        // Cari ujian yang public berdasarkan slug
        $exam = Exam::where('slug', $slug)
            ->where('is_public', true)
            ->withCount('questions')
            ->firstOrFail();

        return view('public.exams.detail', compact('exam'));
    }
}
