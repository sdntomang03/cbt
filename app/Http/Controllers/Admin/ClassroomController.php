<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Level;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ClassroomController extends Controller
{
    /**
     * Tampilkan daftar kelas berserta data untuk Modal
     */
    public function index()
    {
        $schoolId = Auth::user()->school_id;

        // Ambil Data Kelas Utama
        $classrooms = Classroom::with(['homeroomTeacher', 'academicYear'])
            ->withCount('students')
            ->where('school_id', $schoolId)
            ->orderBy('name', 'asc')
            ->get();

        // Ambil Data Pendukung untuk Dropdown Modal
        $academicYears = AcademicYear::where('school_id', $schoolId)->get();
        // Pastikan relasi spatie sudah diatur dengan benar untuk memanggil scope 'role'
        $levels = Level::where('school_id', $schoolId)->get();
        $teachers = User::role('guru')->where('school_id', $schoolId)->get();

        return view('admin.classrooms.index', compact('classrooms', 'academicYears', 'teachers', 'levels'));
    }

    /**
     * Simpan kelas baru via Modal (POST)
     */
    public function store(Request $request)
    {
        $schoolId = Auth::user()->school_id;

        $request->validate([
            'name' => 'required|string|max:255',
            // Validasi nullable agar jika dikosongkan tidak error
            'academic_year_id' => ['nullable', Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'homeroom_teacher_id' => ['nullable', Rule::exists('users', 'id')->where('school_id', $schoolId)],
        ]);

        Classroom::create([
            'school_id' => $schoolId,
            'academic_year_id' => $request->academic_year_id,
            'user_id' => $request->homeroom_teacher_id, // Perhatikan pemetaan field DB dengan input form
            'level_id' => $request->level_id,
            'name' => $request->name,
        ]);

        // Karena menggunakan modal, redirect ke halaman index (back)
        return redirect()->back()->with('success', 'Kelas berhasil dibuat!');
    }

    /**
     * Update kelas via Modal (PUT)
     */
    public function update(Request $request, Classroom $classroom)
    {
        $schoolId = Auth::user()->school_id;

        // Keamanan
        if ($classroom->school_id !== $schoolId) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'academic_year_id' => ['nullable', Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'homeroom_teacher_id' => ['nullable', Rule::exists('users', 'id')->where('school_id', $schoolId)],
            'level_id' => ['nullable', Rule::exists('levels', 'id')->where('school_id', $schoolId)],
        ]);

        $classroom->update([
            'academic_year_id' => $request->academic_year_id,
            'user_id' => $request->homeroom_teacher_id,
            'level_id' => $request->level_id,
            'name' => $request->name,
        ]);

        return redirect()->back()->with('success', 'Kelas berhasil diperbarui!');
    }

    /**
     * Hapus kelas
     */
    public function destroy(Classroom $classroom)
    {
        if ($classroom->school_id !== Auth::user()->school_id) {
            abort(403);
        }

        $classroom->delete();

        return redirect()->back()->with('success', 'Kelas berhasil dihapus!');
    }

    // =========================================================================
    // FITUR PENGELOLAAN SISWA DI DALAM KELAS (TETAP SAMA KARENA DI HALAMAN TERPISAH)
    // =========================================================================

    public function manageStudents(Classroom $classroom)
    {
        $schoolId = Auth::user()->school_id;
        if ($classroom->school_id !== $schoolId) {
            abort(403);
        }

        // Filter siswa yang tampil di kelas berdasarkan tahun ajaran kelas tersebut
        $classroom->load(['students' => function ($q) use ($classroom) {
            $q->where('classroom_student.academic_year_id', $classroom->academic_year_id)
                ->orderBy('name', 'asc');
        }]);

        // Cari ID siswa yang SUDAH memiliki kelas PADA TAHUN AJARAN INI
        $assignedStudentIds = DB::table('classroom_student')
            ->join('users', 'classroom_student.student_id', '=', 'users.id')
            ->where('users.school_id', $schoolId)
            ->where('classroom_student.academic_year_id', $classroom->academic_year_id) // Tambahan filter tahun ajaran
            ->pluck('student_id');

        // Tampilkan siswa yang BELUM memiliki kelas pada TAHUN AJARAN INI
        $unassignedStudents = User::where('school_id', $schoolId)
            ->role('siswa')
            ->whereNotIn('id', $assignedStudentIds)
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.classrooms.students', compact('classroom', 'unassignedStudents'));
    }

    public function syncStudents(Request $request, Classroom $classroom)
    {
        $schoolId = Auth::user()->school_id;
        if ($classroom->school_id !== $schoolId) {
            abort(403);
        }

        $request->validate([
            'student_ids' => 'nullable|array',
            'student_ids.*' => [Rule::exists('users', 'id')->where('school_id', $schoolId)],
        ]);

        // Siapkan data pivot yang menyertakan academic_year_id
        $pivotData = [];
        if ($request->filled('student_ids')) {
            foreach ($request->student_ids as $studentId) {
                $pivotData[$studentId] = ['academic_year_id' => $classroom->academic_year_id];
            }
        }

        $classroom->students()->sync($pivotData);

        return redirect()->route('admin.classrooms.index')
            ->with('success', 'Daftar siswa di kelas '.$classroom->name.' berhasil diperbarui!');
    }

    public function attachStudents(Request $request, Classroom $classroom)
    {
        $schoolId = Auth::user()->school_id;
        if ($classroom->school_id !== $schoolId) {
            abort(403);
        }

        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:users,id',
        ]);

        // Tambahkan academic_year_id saat insert siswa ke tabel pivot
        $pivotData = [];
        foreach ($request->student_ids as $studentId) {
            $pivotData[$studentId] = ['academic_year_id' => $classroom->academic_year_id];
        }

        $classroom->students()->syncWithoutDetaching($pivotData);

        return back()->with('success', count($request->student_ids).' siswa berhasil ditambahkan ke kelas!');
    }

    public function detachStudent(Classroom $classroom, User $student)
    {
        $schoolId = Auth::user()->school_id;
        if ($classroom->school_id !== $schoolId) {
            abort(403);
        }

        // Hapus spesifik berdasarkan student, classroom, dan tahun ajaran
        // (Agar riwayat kelas siswa di tahun ajaran sebelumnya tidak ikut terhapus)
        DB::table('classroom_student')
            ->where('classroom_id', $classroom->id)
            ->where('student_id', $student->id)
            ->where('academic_year_id', $classroom->academic_year_id)
            ->delete();

        return back()->with('success', 'Siswa '.$student->name.' berhasil dikeluarkan dari kelas.');
    }
}
