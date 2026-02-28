<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExamSessionController extends Controller
{
    /**
     * Menampilkan daftar sesi ujian.
     */
    public function index()
    {
        // Ambil data sesi, urutkan dari yang terbaru, paginate 10 per halaman
        // Eager load 'exam' agar tidak N+1 query
        $sessions = ExamSession::with('exam')->latest()->paginate(10);

        // Ambil data ujian untuk dropdown di modal (hanya yang statusnya published/aktif)
        // Sesuaikan 'status' dengan kolom di tabel exams Anda, jika tidak ada hapus where-nya
        $exams = Exam::latest()->get();

        // Return ke view admin/session/index.blade.php
        return view('admin.sessions.index', compact('sessions', 'exams'));
    }

    /**
     * Menyimpan sesi ujian baru (Dipanggil via Axios).
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'session_name' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time', // Waktu selesai harus setelah waktu mulai
        ], [
            'end_time.after' => 'Waktu selesai harus lebih akhir dari waktu mulai.',
        ]);

        // 2. Simpan ke Database
        $session = ExamSession::create([
            'exam_id' => $validated['exam_id'],
            'session_name' => $validated['session_name'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'token' => strtoupper(Str::random(6)), // Generate Token 6 Karakter Unik
        ]);

        // 3. Return JSON response karena view menggunakan Axios
        return response()->json([
            'message' => 'Sesi ujian berhasil dibuat.',
            'data' => $session,
        ], 201);
    }

    /**
     * Memperbarui sesi ujian (Dipanggil via Axios).
     */
    public function update(Request $request, ExamSession $examSession)
    {
        // 1. Validasi Input
        $validated = $request->validate([
            'session_name' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ], [
            'end_time.after' => 'Waktu selesai harus lebih akhir dari waktu mulai.',
        ]);

        // 2. Update Database
        // Note: exam_id biasanya tidak diubah saat edit untuk menjaga integritas data peserta
        $examSession->update([
            'session_name' => $validated['session_name'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
        ]);

        // 3. Return JSON response
        return response()->json([
            'message' => 'Jadwal sesi berhasil diperbarui.',
            'data' => $examSession,
        ]);
    }

    /**
     * Menghapus sesi ujian.
     */
    public function destroy(ExamSession $examSession)
    {
        // Hapus data (Cascade delete akan menghapus data di exam_session_user juga jika migration sudah benar)
        $examSession->delete();

        // Redirect back dengan flash message (karena view menggunakan form submit biasa untuk delete)
        return redirect()->route('admin.exam-sessions.index')
            ->with('success', 'Sesi ujian berhasil dihapus.');
    }

    /**
     * Mengacak ulang token akses (Regenerate Token).
     */
    public function regenerateToken(ExamSession $examSession)
    {
        // Generate token baru
        $newToken = strtoupper(Str::random(6));

        // Update di database
        $examSession->update([
            'token' => $newToken,
        ]);

        // Return token baru ke JSON agar UI bisa update tanpa reload
        return response()->json([
            'message' => 'Token berhasil diperbarui.',
            'token' => $newToken,
        ]);
    }

    public function studentIndex(ExamSession $examSession)
    {
        // Ambil siswa yang SUDAH terdaftar di sesi ini
        $enrolledStudents = $examSession->students()->get();

        // Ambil ID siswa yang sudah terdaftar untuk exclude
        $enrolledIds = $enrolledStudents->pluck('id')->toArray();

        // Ambil siswa yang BELUM terdaftar (Role siswa/user)
        // Sesuaikan query 'role' dengan sistem role Anda (misal Spatie atau kolom biasa)
        $availableStudents = User::role('siswa') // Gunakan scope 'role' milik Spatie
            ->whereNotIn('id', $enrolledIds)
            ->orderBy('name')
            ->get();

        return view('admin.sessions.students', compact('examSession', 'enrolledStudents', 'availableStudents'));
    }

    /**
     * Simpan Siswa ke Sesi (Enrollment)
     */
    public function studentStore(Request $request, ExamSession $examSession)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:users,id',
        ]);

        // Gunakan syncWithoutDetaching agar data siswa lain tidak terhapus
        $examSession->students()->syncWithoutDetaching($request->student_ids);

        return back()->with('success', 'Siswa berhasil ditambahkan ke sesi ujian.');
    }

    /**
     * Hapus Siswa dari Sesi
     */
    public function studentDestroy(ExamSession $examSession, User $user)
    {
        $examSession->students()->detach($user->id);

        return back()->with('success', 'Siswa dihapus dari sesi.');
    }
}
