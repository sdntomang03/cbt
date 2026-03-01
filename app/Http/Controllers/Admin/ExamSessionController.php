<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExamSessionController extends Controller
{
    /**
     * Menampilkan daftar sesi ujian.
     */
    public function index(Request $request)
    {
        // Pastikan model ExamSession memiliki relasi ke 'school' dan 'exam'
        $query = ExamSession::with(['exam', 'school']);

        // 1. Filter Dropdown: HANYA berlaku untuk Super Admin
        if (auth()->user()->hasRole('admin') && $request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        // 2. Fitur Pencarian Teks (Berdasarkan nama sesi)
        if ($request->filled('search')) {
            $query->where('session_name', 'like', '%'.$request->search.'%');
        }

        $sessions = $query->latest()->paginate(12)->withQueryString();

        // 3. Kirim data daftar sekolah ke layar (Hanya dikirim jika admin)
        $schools = auth()->user()->hasRole('admin') ? School::orderBy('name')->get() : [];

        // 4. Ambil data ujian untuk form modal (Create/Edit)
        $exams = Exam::latest()->get();

        return view('admin.sessions.index', compact('sessions', 'schools', 'exams'));
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

    public function studentIndex(Request $request, ExamSession $examSession)
    {
        // Ambil siswa yang SUDAH terdaftar di sesi ini
        $enrolledStudents = $examSession->students()->with('school')->get();
        $enrolledIds = $enrolledStudents->pluck('id')->toArray();

        // Query dasar untuk siswa yang BELUM terdaftar
        $query = User::role('siswa')->whereNotIn('id', $enrolledIds)->with('school');

        // LOGIKA FILTER SEKOLAH
        if (auth()->user()->hasRole('admin')) {
            // Jika Super Admin memilih sekolah di dropdown
            if ($request->filled('school_id')) {
                $query->where('school_id', $request->school_id);
            }
            // Jika tidak memilih (kosong), akan menampilkan semua siswa lintas sekolah
        } else {
            // Jika Admin Biasa / Guru, KUNCI hanya boleh melihat siswanya sendiri
            $query->where('school_id', $examSession->school_id);
        }

        $availableStudents = $query->orderBy('name')->get();

        // Kirim daftar sekolah untuk dropdown HANYA jika Super Admin
        $schools = auth()->user()->hasRole('admin') ? School::orderBy('name')->get() : [];

        return view('admin.sessions.students', compact('examSession', 'enrolledStudents', 'availableStudents', 'schools'));
    }

    /**
     * Simpan Siswa ke Sesi (Enrollment)
     */
    public function studentStore(Request $request, ExamSession $examSession)
    {
        // 1. Validasi input
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:users,id',
        ]);

        // 2. Ambil ID Sekolah dari sesi ujian ini
        $schoolId = $examSession->school_id;

        // 3. Siapkan data pivot yang menyertakan school_id
        $syncData = [];
        foreach ($request->student_ids as $studentId) {
            $syncData[$studentId] = [
                'school_id' => $schoolId, // <--- INI KUNCI JAWABANNYA
            ];
        }

        // 4. Masukkan ke database (Gunakan syncWithoutDetaching agar siswa yang sudah ada tidak terhapus)
        $examSession->students()->syncWithoutDetaching($syncData);

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

    public function destroyMass(Request $request, ExamSession $examSession)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:users,id',
        ]);

        // Hapus relasi siswa yang dipilih dari sesi ujian ini
        $examSession->students()->detach($request->student_ids);

        return redirect()->back()->with('success', count($request->student_ids).' siswa berhasil dikeluarkan dari sesi ujian.');
    }
}
