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

        // Proteksi Lapis Ketiga (Jika user bypass URL verifikasi)
        if ($exam->is_premium) {
            if (! auth()->check() || empty(auth()->user()->premium_until) || Carbon::parse(auth()->user()->premium_until)->isPast()) {
                session()->forget('public_user_'.$exam->id);

                return redirect()->route('public.exams.index')
                    ->with('error', 'Akses ditolak. Ujian ini memerlukan status Premium aktif.');
            }
        }

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

        // Validasi Blokir
        if ($state['is_locked']) {
            // 1. Hapus memori ujian yang terkunci agar bisa diulang
            session()->forget('public_exam_state_'.$exam->id);

            // 2. (Opsional) Hapus data diri agar mereka harus ketik nama lagi
            // session()->forget('public_user_'.$exam->id);

            // 3. Arahkan kembali ke halaman daftar ujian dengan pesan peringatan
            return redirect()->route('public.exams.index')
                ->with('error', 'Ujian Anda dikunci karena pelanggaran keamanan. Silakan pilih dan mulai ulang ujian dari awal.');
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

        if (! $state || $state['status'] === 'completed') {
            return redirect()->route('public.exams.result', $exam);
        }

        // ========================================================
        // 1. SINKRONISASI JAWABAN AKHIR DARI REQUEST
        // ========================================================
        if ($request->has('final_answers') && ! empty($request->final_answers)) {
            $decodedAnswers = is_string($request->final_answers)
                ? json_decode($request->final_answers, true)
                : $request->final_answers;

            if (is_array($decodedAnswers)) {
                $state['answers'] = $decodedAnswers;
            }
        }

        // ========================================================
        // 2. KALKULASI / KOREKSI (HANYA DILAKUKAN DI SINI)
        // ========================================================
        $questions = $exam->questions()->with(['options', 'matches'])->get();
        $answers = $state['answers'] ?? [];

        $correctCount = 0;
        $wrongCount = 0;
        $unansweredCount = 0;
        $totalQuestions = $questions->count();

        foreach ($questions as $q) {
            $userAnswer = isset($answers[$q->id]) ? $answers[$q->id] : null;

            if (is_string($userAnswer) && (strpos(trim($userAnswer), '{') === 0 || strpos(trim($userAnswer), '[') === 0)) {
                $decoded = json_decode($userAnswer, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $userAnswer = $decoded;
                }
            }
            if (is_object($userAnswer)) {
                $userAnswer = (array) $userAnswer;
            }

            if ($userAnswer === null || $userAnswer === '' || (is_array($userAnswer) && empty($userAnswer))) {
                $unansweredCount++;

                continue;
            }

            if ($q->type === 'single_choice') {
                $correctOptionId = null;
                foreach ($q->options as $opt) {
                    if (filter_var($opt->is_correct, FILTER_VALIDATE_BOOLEAN)) {
                        $correctOptionId = (string) $opt->id;
                        break;
                    }
                }
                ($correctOptionId !== null && (string) $userAnswer === $correctOptionId) ? $correctCount++ : $wrongCount++;
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
                ($correctOptionIds === $userStr) ? $correctCount++ : $wrongCount++;
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
            } elseif ($q->type === 'essay' && is_string($userAnswer)) {
                $cleanUser = preg_replace('/\s+/', ' ', trim(strip_tags($userAnswer)));
                $isCorrect = $q->options->where('is_correct', 1)->contains(function ($opt) use ($cleanUser) {
                    $cleanCorrect = preg_replace('/\s+/', ' ', trim(strip_tags(html_entity_decode($opt->option_text ?? ''))));

                    return strcasecmp($cleanCorrect, $cleanUser) === 0 || (is_numeric($cleanCorrect) && is_numeric($cleanUser) && (float) $cleanCorrect === (float) $cleanUser);
                });
                $isCorrect ? $correctCount++ : $wrongCount++;
            } else {
                $wrongCount++;
            }
        }

        $score = $totalQuestions > 0 ? round(($correctCount / $totalQuestions) * 100) : 0;

        // SIMPAN HASIL KALKULASI KE SESSION
        $state['status'] = 'completed';
        $state['finished_at'] = Carbon::now('Asia/Jakarta')->toDateTimeString();
        $state['grading'] = [
            'score' => $score,
            'correctCount' => $correctCount,
            'wrongCount' => $wrongCount,
            'unansweredCount' => $unansweredCount,
            'totalQuestions' => $totalQuestions,
        ];

        session()->put($sessionKey, $state);

        // ========================================================
        // 3. SIMPAN KE DATABASE & UPDATE POIN
        // ========================================================
        if ($userData) {
            $durationSeconds = Carbon::parse($state['finished_at'])->diffInSeconds(Carbon::parse($state['started_at']));
            $userId = auth()->check() ? auth()->id() : null;

            // LOGIKA UPDATE POIN BERDASARKAN SELISIH NILAI TERTINGGI
            if ($userId) {
                // Cari nilai tertinggi sebelumnya untuk ujian ini
                $highestPreviousScore = PublicExamResult::where('exam_id', $exam->id)
                    ->where('user_id', $userId)
                    ->max('score'); // Akan mengembalikan null jika belum pernah mengerjakan

                if (is_null($highestPreviousScore)) {
                    // Jika belum pernah mengerjakan, tambahkan skor penuh
                    auth()->user()->increment('total_poin', $score);
                } elseif ($score > $highestPreviousScore) {
                    // Jika pernah mengerjakan dan skor baru lebih besar, tambahkan selisihnya
                    $selisih = $score - $highestPreviousScore;
                    auth()->user()->increment('total_poin', $selisih);
                }
                // Jika skor baru lebih kecil atau sama dengan skor sebelumnya, poin tidak bertambah
            }

            // Simpan riwayat ujian ini ke database
            PublicExamResult::create([
                'exam_id' => $exam->id,
                'nama_peserta' => $userData['nama_peserta'],
                'user_id' => $userId,
                'asal_sekolah' => $userData['asal_sekolah'],
                'score' => $score,
                'correct_count' => $correctCount,
                'wrong_count' => $wrongCount,
                'unanswered_count' => $unansweredCount,
                'duration_seconds' => $durationSeconds,
            ]);
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

        // Ambil hasil perhitungan dari session (dibuat oleh metode finish)
        $grading = $state['grading'] ?? null;

        // Fallback keamanan jika nilai belum ada di session
        if (! $grading) {
            return redirect()->route('public.exams.index')
                ->with('error', 'Sesi ujian telah berakhir. Silakan ulangi ujian Anda.');
        }

        return view('public.exams.result', [
            'exam' => $exam,
            'score' => $grading['score'],
            'correctCount' => $grading['correctCount'],
            'wrongCount' => $grading['wrongCount'],
            'unansweredCount' => $grading['unansweredCount'],
            'totalQuestions' => $grading['totalQuestions'],
        ]);
    }

    /**
     * Menampilkan Halaman Verifikasi
     */
    public function verify(Exam $exam)
    {
        abort_if(! $exam->is_public, 403);

        // ========================================================
        // LOGIKA CEGATAN PREMIUM
        // ========================================================
        if ($exam->is_premium) {
            // 1. Jika belum login
            if (! auth()->check()) {
                return redirect()->route('login')
                    ->with('error', 'Akses ditolak. Ujian ini eksklusif untuk member Premium. Silakan login.');
            }

            // 2. Jika sudah login, tapi masa aktif premium habis/null
            $user = auth()->user();
            if (empty($user->premium_until) || Carbon::parse($user->premium_until)->isPast()) {
                return redirect()->route('public.exams.index')
                    ->with('error', 'Akses ditolak. Masa aktif Premium Anda telah habis atau Anda belum berlangganan.');
            }
        }
        // ========================================================

        if (session()->has('public_user_'.$exam->id)) {
            return redirect()->route('public.exams.show', $exam);
        }

        $token = strtoupper(Str::random(6));
        session()->put('exam_token_'.$exam->id, $token);

        return view('public.exams.verify', compact('exam', 'token'));
    }

    public function processVerify(Request $request, Exam $exam)
    {
        abort_if(! $exam->is_public, 403);

        // Proteksi Ganda di proses POST
        if ($exam->is_premium) {
            if (! auth()->check() || empty(auth()->user()->premium_until) || Carbon::parse(auth()->user()->premium_until)->isPast()) {
                return redirect()->route('public.exams.index')
                    ->with('error', 'Akses ditolak. Masa aktif Premium Anda tidak valid.');
            }
        }

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

    public function getRanking($identifier)
    {
        // 1. Cari data ujiannya terlebih dahulu.
        // Kita cek apakah $identifier itu cocok dengan 'hashid', 'slug', atau 'id'
        $exam = Exam::where('hashid', $identifier)
            ->orWhere('id', $identifier)
            ->first();

        // Jika ujian tidak ditemukan, kembalikan pesan error
        if (! $exam) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data ujian tidak ditemukan.',
                'data' => [],
            ], 404);
        }

        // 2. Gunakan ID asli (Integer) dari tabel exams untuk mencari ranking
        $rankings = PublicExamResult::where('exam_id', $exam->id)
            ->orderBy('score', 'desc')
            ->orderBy('duration_seconds', 'asc') // Ranking berdasarkan skor, lalu durasi tercepat
            ->take(100) // Ambil Top 100 Nasional
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $rankings,
        ]);
    }
}
