<?php

namespace App\Http\Controllers;

use App\Models\ExamSession;
use App\Models\ExamSessionUser;
use App\Models\Question;
use App\Models\School;
use App\Models\StudentAnswer;
use App\Models\User;
use Illuminate\Http\Request;

class ProctorController extends Controller
{
    /**
     * Menampilkan daftar jadwal ujian yang bisa diawasi hari ini.
     */
    public function index()
    {
        // Ambil sesi ujian yang sedang berlangsung hari ini atau akan datang
        $sessions = ExamSession::with('exam')
            ->whereDate('start_time', '<=', now())
            ->whereDate('end_time', '>=', now())
            ->orderBy('start_time', 'asc')
            ->get();

        return view('proctor.index', compact('sessions'));
    }

    /**
     * Halaman Monitoring (Dashboard Pengawas) untuk sesi tertentu.
     */
    public function show(Request $request, ExamSession $examSession)
    {
        // 1. Ambil data siswa beserta status ujian (pivot) DAN asal sekolahnya
        $students = $examSession->students()
            ->with('school') // Wajib agar nama sekolah muncul di tabel Alpine JS
            ->orderBy('name', 'asc')
            ->get();

        // 2. LOGIKA LIVE UPDATE (AJAX)
        // Jika Alpine JS melakukan fetch data setiap 5 detik, kirimkan JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'students' => $students,
            ]);
        }

        // 3. Ambil data sekolah khusus untuk filter Super Admin
        $schools = auth()->user()->hasRole('admin') ? School::orderBy('name')->get() : [];

        // 4. Tampilkan view pertama kali dimuat
        return view('proctor.monitoring', compact('examSession', 'students', 'schools'));
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

        // Jika status belum completed, kita hitung nilainya dan selesaikan
        if ($examUser->status !== 'completed') {

            // 1. Ambil semua jawaban yang sudah dijawab siswa sejauh ini
            $answers = StudentAnswer::where('exam_session_id', $examSession->id)
                ->where('user_id', $student->id)
                ->with(['question.options', 'question.matches'])
                ->get();

            $totalScore = 0;
            // 2. Hitung total soal pada ujian ini
            $totalQuestions = Question::where('exam_id', $examSession->exam_id)->count();

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

        // Hapus semua jawaban siswa untuk sesi ini
        StudentAnswer::where('exam_session_id', $examSession->id)
            ->where('user_id', $student->id)
            ->delete();

        // Kembalikan status ke belum mulai
        $examUser->update([
            'status' => 'not_started',
            'started_at' => null,
            'finished_at' => null,
            'score' => null,
            'is_locked' => false,
            'violation_count' => 0,
        ]);

        return back()->with('success', "Ujian siswa {$student->name} berhasil direset.");
    }
}
