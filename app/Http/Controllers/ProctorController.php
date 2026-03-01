<?php

namespace App\Http\Controllers;

use App\Models\ExamSession;
use App\Models\ExamSessionUser;
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

        // Jika status belum completed, ubah menjadi completed
        if ($examUser->status !== 'completed') {
            $examUser->update([
                'status' => 'completed',
                'finished_at' => now(),
            ]);

            // Catatan: Jika Anda ingin sistem otomatis menghitung nilai saat di-force finish,
            // Anda bisa memanggil logika skoring dari StudentExamController di sini,
            // atau membiarkan Cron Job/Admin merekap nilainya nanti.
        }

        // KUNCI PERBAIKANNYA DI SINI:
        // Kembalikan respons dalam bentuk JSON agar dikenali oleh Axios / Alpine.js
        return response()->json([
            'success' => true,
            'message' => "Ujian siswa {$student->name} berhasil diselesaikan paksa.",
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
