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

        // --- FILTER BERDASARKAN EXAM_ID ---
        if ($request->filled('exam_id')) {
            $query->where('exam_id', $request->exam_id);
        }

        // 1. Filter Dropdown: HANYA berlaku untuk Super Admin
        if (auth()->user()->hasRole('admin') && $request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        // 2. Fitur Pencarian Teks (Berdasarkan nama sesi)
        if ($request->filled('search')) {
            $query->where('session_name', 'like', '%'.$request->search.'%');
        }

        // Menjalankan query dengan paginasi
        $sessions = $query->latest()->paginate(12)->withQueryString();

        // 3. Kirim data daftar sekolah ke layar (Hanya dikirim jika admin)
        $schools = auth()->user()->hasRole('admin') ? School::orderBy('name')->get() : [];

        // 4. Ambil data ujian untuk form modal (Create/Edit)
        $exams = Exam::latest()->get();

        // Ambil detail ujian yang sedang dipilih (untuk judul di Blade)
        $selectedExam = $request->filled('exam_id') ? Exam::find($request->exam_id) : null;

        return view('admin.sessions.index', compact('sessions', 'schools', 'exams', 'selectedExam'));
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
            'school_id' => auth()->user()->school_id, // Pastikan sesi ujian terkait dengan sekolah pengguna yang membuatnya
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
        // 1. Ambil siswa TERDAFTAR beserta relasi sekolah dan kelasnya
        $enrolledStudents = $examSession->students()
            ->with(['school', 'classrooms']) // Asumsi nama relasi di Model User adalah 'classrooms'
            ->get();

        $enrolledIds = $enrolledStudents->pluck('id')->toArray();

        // 2. Query siswa BELUM TERDAFTAR
        $query = User::role('siswa')
            ->whereNotIn('id', $enrolledIds)
            ->with(['school', 'classrooms']); // Tambahkan 'classrooms' di sini

        // Logika Filter Sekolah
        if (auth()->user()->hasRole('admin')) {
            if ($request->filled('school_id')) {
                $query->where('school_id', $request->school_id);
            }
        } else {
            $query->where('school_id', auth()->user()->school_id);
        }

        // 3. Cukup urutkan berdasarkan NAMA saja untuk menghindari error pivot
        $availableStudents = $query->orderBy('name', 'asc')->get();

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

    public function explanation($hashedId) // Ubah nama variabel agar tidak bingung
    {
        // 1. Decode ID
        // Hashids::decode mengembalikan bentuk Array, ambil elemen ke-0
        $decodedArray = Hashids::decode($hashedId);

        // Jika array kosong (artinya orang mencoba menebak URL dengan teks ngawur)
        if (empty($decodedArray)) {
            abort(404, 'Halaman tidak ditemukan atau link tidak valid.');
        }

        $realId = $decodedArray[0];

        // 2. Ambil data dengan ID asli
        $session = ExamSession::with(['exam.questions.options'])->findOrFail($realId);

        // 3. Lapis Keamanan
        if ($session->user_id !== auth()->id()) {
            abort(403, 'Akses ditolak. Ini bukan ujian Anda.');
        }

        if ($session->user_status !== 'completed') {
            abort(403, 'Anda belum menyelesaikan ujian ini.');
        }

        if (! $session->exam->show_explanation) {
            abort(403, 'Fitur pembahasan tidak diaktifkan untuk ujian ini.');
        }

        return view('student.exams.explanation', compact('session'));
    }
}
