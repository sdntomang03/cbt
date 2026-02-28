<?php

namespace App\Http\Controllers;

use App\Models\ExamSession;
use App\Models\ExamSessionUser;
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
    public function show(ExamSession $examSession)
    {
        $students = $examSession->students()->orderBy('name', 'asc')->get();

        // Jika dipanggil via AJAX (Auto Update)
        if (request()->ajax()) {
            return response()->json([
                'students' => $students,
            ]);
        }

        return view('proctor.monitoring', compact('examSession', 'students'));
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
        $examUser = ExamSessionUser::where('exam_session_id', $examSession->id)
            ->where('user_id', $student->id)
            ->firstOrFail();

        if ($examUser->status !== 'completed') {
            $examUser->update([
                'status' => 'completed',
                'finished_at' => now(),
            ]);

            // Catatan: Jika Anda ingin sistem otomatis menghitung nilai saat di-force finish,
            // Anda bisa memanggil logika skoring dari StudentExamController di sini,
            // atau membiarkan Cron Job/Admin merekap nilainya nanti.
        }

        return back()->with('success', "Ujian siswa {$student->name} berhasil diselesaikan paksa.");
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
