<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Level;
use App\Models\PublicExamResult;
use App\Models\Question;
use App\Models\Subject;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublicExamController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil Level/Kelas yang HANYA memiliki ujian publik
        $levels = Level::whereHas('exams', function ($query) {
            $query->where('is_public', true);
        })->orderBy('name', 'asc')->get();

        // 2. Siapkan variabel subjects kosong secara default
        $subjects = collect();

        // 3. Jika user SUDAH memilih Level, baru cari Mata Pelajaran terkait
        if ($request->filled('level')) {
            $subjects = Subject::whereHas('exams', function ($query) use ($request) {
                // Pastikan mapel ini ada ujian publiknya dan sesuai dengan level yang dipilih
                $query->where('is_public', true)
                    ->where('level_id', $request->level);
            })->orderBy('name', 'asc')->get();
        }

        // 4. Query utama untuk mengambil data ujian
        $publicExams = Exam::query()
            ->where('is_public', true)
                    // Memfilter berdasarkan relasi ExamType yang namanya 'TKA'
            ->whereHas('examType', function ($query) {
                $query->where('name', 'TKA'); // Sesuaikan huruf besar/kecil dengan yang ada di database Anda
            })
                    // Tambahkan 'examType' agar tidak terjadi N+1 Query Problem
            ->with(['subject', 'level', 'examType'])
            ->when($request->filled('level'), function ($query) use ($request) {
                $query->where('level_id', $request->level);
            })
            ->when($request->filled('subject'), function ($query) use ($request) {
                $query->where('subject_id', $request->subject);
            })
            ->latest()
            ->paginate(9);

        return view('public.exams.index', compact('publicExams', 'levels', 'subjects'));
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
        $questions = Question::with(['options', 'matches'])
            ->where('exam_id', $exam->id)
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

        return view('public.exams.run', [
            'exam' => $exam,
            'questions' => $questions,
            'questionIds' => $questionIds,
            'config' => $config,
            'timeLeftSeconds' => (int) $timeLeftSeconds,
            'existingAnswers' => $state['answers'] ?? [],
            'flags' => $state['flags'] ?? [],
            'pivot' => $pivotMock,
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
    public function finish(Exam $exam)
    {
        abort_if(! $exam->is_public, 403);
        $sessionKey = 'public_exam_state_'.$exam->id;
        $state = session()->get($sessionKey);
        $userData = session()->get('public_user_'.$exam->id);

        if ($state && $state['status'] !== 'completed') {
            $state['status'] = 'completed';
            $state['finished_at'] = Carbon::now('Asia/Jakarta')->toDateTimeString();
            session()->put($sessionKey, $state);

            // ==========================================
            // KALKULASI & SIMPAN KE DATABASE RANKING
            // ==========================================
            if ($userData) {
                $questions = Question::where('exam_id', $exam->id)->with('options')->get();
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

                // Waktu Pengerjaan
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

        $questions = Question::where('exam_id', $exam->id)->with('options')->get();
        $totalQuestions = $questions->count();
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
