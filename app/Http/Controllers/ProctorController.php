<?php

namespace App\Http\Controllers;

use App\Exports\ButirSoalExport;
use App\Models\DetailNilai;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\ExamSessionUser;
use App\Models\ExamAttempt;
use App\Models\School;
use App\Models\StudentAnswer;
use App\Models\User;
use App\Services\AttemptScoringService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ProctorController extends Controller
{
    /**
     * Menampilkan daftar jadwal ujian yang bisa diawasi hari ini.
     */
    public function index()
    {
        $user = auth()->user();

        // Mulai Query dengan eager loading relasi 'exam'
        $query = ExamSession::with('exam')->orderBy('start_time', 'asc');

        // =========================================================
        // FILTER HAK AKSES PENGAWAS (PROCTOR)
        // =========================================================
        // Jika bukan admin, batasi sesi ujian yang tampil
        if (! $user->hasRole('admin')) {
            $query->whereHas('exam', function ($q) use ($user) {
                // Tampilkan jika user adalah pembuat ujian
                $q->where('teacher_id', $user->id)
                  // ATAU jika user terdaftar sebagai undangan di ujian tersebut
                    ->orWhereHas('invitedTeachers', function ($subQuery) use ($user) {
                        $subQuery->where('user_id', $user->id);
                    });
            });
        }

        $sessions = $query->get();

        return view('proctor.index', compact('sessions'));
    }

    /**
     * Halaman Monitoring (Dashboard Pengawas) untuk sesi tertentu.
     */
    public function show(Request $request, ExamSession $examSession)
    {
        // 1. --- [AUTO-SWEEP / PENYAPU OTOMATIS] ---
        // Mencari siswa offline yang waktunya sudah habis dan menutup paksa ujiannya.
        $now = Carbon::now('Asia/Jakarta');
        $durationMinutes = (int) $examSession->exam->duration_minutes;
        $sessionEnd = Carbon::parse($examSession->end_time)->timezone('Asia/Jakarta');

        $ongoingStudents = $examSession->students()->wherePivot('status', 'ongoing')->get();

        foreach ($ongoingStudents as $student) {
            if ($student->pivot->started_at) {
                $startedAt = Carbon::parse($student->pivot->started_at)->timezone('Asia/Jakarta');
                $personalDeadline = $startedAt->copy()->addMinutes($durationMinutes);

                // Real deadline adalah waktu tersingkat antara durasi siswa vs jadwal akhir sesi
                $realDeadline = $personalDeadline->min($sessionEnd);

                // Jika waktu sekarang melebihi deadline (diberi toleransi telat 1 menit)
                if ($now->greaterThan($realDeadline->addMinutes(1))) {
                    // Panggil fungsi skoring dan penutupan
                    $this->forceFinishLogic($examSession, $student);
                }
            }
        }
        // ------------------------------------------

        // 2. Ambil data siswa beserta status ujian (setelah disapu bersih) DAN asal sekolahnya
        $students = $examSession->students()
            ->with('school')
            ->orderBy('name', 'asc')
            ->get();

        // 3. LOGIKA LIVE UPDATE (AJAX)
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'students' => $students,
            ]);
        }

        // 4. Ambil data sekolah khusus untuk filter Super Admin
        $schools = auth()->user()->hasRole('admin') ? School::orderBy('name')->get() : [];

        // 5. Tampilkan view pertama kali dimuat
        return view('proctor.monitoring', compact('examSession', 'students', 'schools'));
    }

    /**
     * Fungsi Helper Private untuk menghitung nilai dan menutup sesi
     * Digunakan oleh fitur Auto-Sweep dan tombol manual Selesaikan Paksa.
     */
    private function forceFinishLogic(ExamSession $examSession, User $student)
    {
        $attempt = ExamAttempt::where('exam_session_id', $examSession->id)
            ->where('user_id', $student->id)
            ->first();

        if ($attempt && $attempt->status !== 'completed') {
            app(AttemptScoringService::class)->scoreAttempt($attempt);
        }

        return;

        $examUser = ExamSessionUser::where('exam_session_id', $examSession->id)
            ->where('user_id', $student->id)
            ->first();
        if ($examUser && $examUser->status !== 'completed') {

            $answers = StudentAnswer::where('exam_session_id', $examSession->id)
                ->where('user_id', $student->id)
                ->with(['question.options', 'question.matches'])
                ->get();

            $totalScore = 0;
            $totalQuestions = Exam::find($examSession->exam_id)->questions()->count();

            foreach ($answers as $ans) {
                $q = $ans->question;
                $poin = 0;
                $studentAns = $ans->answer;

                // Decode JSON untuk jawaban array
                if (is_string($studentAns) && in_array($q->type, ['complex_choice', 'matching', 'true_false', 'true_false_multi'])) {
                    $decoded = json_decode($studentAns, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $studentAns = $decoded;
                    }
                }

                // LOGIKA SKORING
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

            // Perbarui status menjadi completed
            $examUser->update([
                'status' => 'completed',
                'finished_at' => Carbon::now('Asia/Jakarta'),
                'score' => round($finalScore, 2),
            ]);
        }
    }

    /**
     * Membuka kunci (Unlock) siswa yang terkena pelanggaran (is_locked = true).
     */
    public function unlock(Request $request, ExamSession $examSession, User $student)
    {
        $examUser = ExamSessionUser::where('exam_session_id', $examSession->id)
            ->where('user_id', $student->id)
            ->firstOrFail();

        // Buka kunci dan reset jumlah pelanggaran
        $examUser->update([
            'is_locked' => false,
            'violation_count' => 0,
        ]);

        return back()->with('success', "Kunci ujian siswa {$student->name} berhasil dibuka.");
    }

    /**
     * Memaksa selesai ujian siswa (Force Finish).
     */
    public function forceFinish(Request $request, ExamSession $examSession, User $student)
    {
        // Cari data ujian siswa di sesi ini
        $examUser = ExamSessionUser::where('exam_session_id', $examSession->id)
            ->where('user_id', $student->id)
            ->firstOrFail();

        if ($examUser->status !== 'completed') {
            $result = app(AttemptScoringService::class)->scoreAttempt($examUser);
        } else {
            $result = ['finalScore' => (float) $examUser->final_score];
        }

        return response()->json([
            'success' => true,
            'message' => "Ujian siswa {$student->name} diselesaikan. Nilai akhir: {$result['finalScore']}",
        ]);

        // Jika status belum completed, kita hitung nilainya dan selesaikan
        if ($examUser->status !== 'completed') {

            // 1. Ambil semua jawaban yang sudah dijawab siswa sejauh ini
            $answers = StudentAnswer::where('exam_session_id', $examSession->id)
                ->where('user_id', $student->id)
                ->with(['question.options', 'question.matches'])
                ->get();

            $totalScore = 0;
            // 2. Hitung total soal pada ujian ini
            $totalQuestions = Exam::find($examSession->exam_id)->questions()->count();

            // ATAU jika model ExamSession kamu sudah punya relasi public function exam(), bisa lebih singkat:
            // $totalQuestions = $examSession->exam->questions()->count();

            // 3. Looping untuk mengoreksi jawaban satu per satu
            foreach ($answers as $ans) {
                $q = $ans->question;
                $poin = 0;

                // Decode JSON jika tipe soal membutuhkan array/json
                $studentAns = $ans->answer;
                if (is_string($studentAns) && in_array($q->type, ['complex_choice', 'matching', 'true_false', 'true_false_multi'])) {
                    $decoded = json_decode($studentAns, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $studentAns = $decoded;
                    }
                }

                // --- LOGIKA SKORING ---
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

                // Simpan skor parsial ke database (per jawaban)
                $ans->update(['score' => $poin]);
                $totalScore += $poin;
            }

            // 4. Kalkulasi persentase nilai akhir (skala 100)
            $finalScore = ($totalQuestions > 0) ? ($totalScore / $totalQuestions) * 100 : 0;
            $finalScore = round($finalScore, 2);

            // 5. Perbarui status ujian siswa dan simpan nilainya
            $examUser->update([
                'status' => 'completed',
                'finished_at' => now(),
                'score' => $finalScore,
            ]);
        }

        // Kembalikan response JSON untuk halaman Monitor (Ajax)
        return response()->json([
            'success' => true,
            'message' => "Ujian siswa {$student->name} diselesaikan. Nilai akhir: {$examUser->score}",
        ]);
    }

    /**
     * Mereset login/ujian siswa (Mulai dari awal).
     */
    public function reset(Request $request, ExamSession $examSession, User $student)
    {
        $examUser = ExamSessionUser::where('exam_session_id', $examSession->id)
            ->where('user_id', $student->id)
            ->firstOrFail();

        $attemptIds = ExamAttempt::where('exam_session_id', $examSession->id)
            ->where('user_id', $student->id)
            ->pluck('id');

        // Hapus semua jawaban siswa untuk sesi ini
        StudentAnswer::where('exam_attempt_id', $examUser->id)
            ->delete();

        if ($attemptIds->isNotEmpty()) {
            DetailNilai::whereIn('exam_attempt_id', $attemptIds)
                ->where('user_id', $student->id)
                ->delete();
        }

        // Kembalikan status ke belum mulai
        $examUser->update([
            'status' => 'not_started',
            'started_at' => null,
            'finished_at' => null,
            'raw_score' => 0,
            'final_score' => 0,
            'is_locked' => false,
            'violation_count' => 0,
        ]);

        return back()->with('success', "Ujian siswa {$student->name} berhasil direset.");
    }

    public function exportAnalysis(ExamSession $examSession)
    {
        // Pastikan user memiliki akses
        $user = auth()->user();
        if (! $user->hasRole('admin')) {
            $hasAccess = $examSession->exam->teacher_id === $user->id ||
                         $examSession->exam->invitedTeachers()->where('user_id', $user->id)->exists();

            if (! $hasAccess) {
                abort(403, 'Akses ditolak.');
            }
        }

        $fileName = 'Analisis_Butir_Soal_'.Str::slug($examSession->exam->title).'_'.date('Ymd_His').'.xlsx';

        return Excel::download(new ButirSoalExport($examSession->id), $fileName);
    }

    public function exportJson(ExamSession $examSession)
    {
        $user = auth()->user();
        if (! $user->hasRole('admin') && $examSession->exam->teacher_id !== $user->id
            && ! $examSession->exam->invitedTeachers()->where('user_id', $user->id)->exists()) {
            abort(403, 'Akses ditolak.');
        }

        $attempts = ExamAttempt::with(['user:id,name,username', 'answers.question:id,content,type'])
            ->where('exam_session_id', $examSession->id)
            ->orderBy('user_id')
            ->get()
            ->map(fn ($attempt) => [
                'attempt_id' => $attempt->id,
                'student' => [
                    'id' => $attempt->user_id,
                    'name' => $attempt->user?->name,
                    'username' => $attempt->user?->username,
                ],
                'status' => $attempt->status,
                'started_at' => $attempt->started_at?->toISOString(),
                'finished_at' => $attempt->finished_at?->toISOString(),
                'raw_score' => (float) $attempt->raw_score,
                'final_score' => (float) $attempt->final_score,
                'violation_count' => $attempt->violation_count,
                'answers' => $attempt->answers->map(fn ($answer) => [
                    'question_id' => $answer->question_id,
                    'question_type' => $answer->question?->type,
                    'answer' => $answer->answer,
                    'score' => (float) $answer->score,
                    'is_doubtful' => (bool) $answer->is_doubtful,
                ])->values()->all(),
            ])->values()->all();

        $payload = [
            'exam' => [
                'id' => $examSession->exam->id,
                'title' => $examSession->exam->title,
            ],
            'session' => [
                'id' => $examSession->id,
                'name' => $examSession->session_name,
                'start_time' => $examSession->start_time?->toISOString(),
                'end_time' => $examSession->end_time?->toISOString(),
            ],
            'exported_at' => now()->toISOString(),
            'participants' => $attempts,
        ];

        return response()->streamDownload(function () use ($payload) {
            echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }, 'nilai_ujian_'.Str::slug($examSession->exam->title).'_'.now()->format('Ymd_His').'.json', [
            'Content-Type' => 'application/json; charset=UTF-8',
        ]);
    }

    /**
     * Menampilkan halaman analisis jawaban siswa secara detail.
     */
    public function showStudentAnalysis(ExamSession $examSession, User $student)
    {
        $examUser = ExamSessionUser::where('exam_session_id', $examSession->id)
            ->where('user_id', $student->id)
            ->firstOrFail();

        // Cegah akses jika ujian belum selesai
        if ($examUser->status !== 'completed') {
            return redirect()->back()->with('error', 'Siswa belum menyelesaikan ujian.');
        }

        // Ambil data jawaban beserta relasinya
        $answers = StudentAnswer::where('exam_attempt_id', $examUser->id)
            ->with(['question.options', 'question.matches'])
            ->get()
            ->map(function ($answer) {
                // Proses JSON jika jawaban berupa array (pilihan ganda kompleks, menjodohkan)
                $decoded = is_string($answer->answer) ? json_decode($answer->answer, true) : $answer->answer;
                $answer->formatted_answer = (json_last_error() === JSON_ERROR_NONE) ? $decoded : $answer->answer;

                return $answer;
            });

        return view('proctor.student-analysis', compact('examSession', 'student', 'examUser', 'answers'));
    }
}
