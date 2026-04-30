<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ClassroomController extends Controller
{
    /**
     * Tampilkan daftar kelas
     */
    public function index()
    {
        $schoolId = Auth::user()->school_id;

        $classrooms = Classroom::with(['homeroomTeacher', 'academicYear']) // <-- Sesuaikan
            ->withCount('students')
            ->where('school_id', $schoolId)
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.classrooms.index', compact('classrooms'));
    }

    /**
     * Tampilkan form tambah kelas
     */
    public function create()
    {
        $schoolId = Auth::user()->school_id;

        // Asumsi kamu punya model AcademicYear
        $academicYears = AcademicYear::where('school_id', $schoolId)->get();

        // Ambil data guru untuk dijadikan Wali Kelas (menggunakan spatie/laravel-permission)
        $teachers = User::role('guru')->where('school_id', $schoolId)->get();

        return view('admin.classrooms.create', compact('academicYears', 'teachers'));
    }

    /**
     * Simpan kelas baru
     */
    public function store(Request $request)
    {
        $schoolId = Auth::user()->school_id;

        $request->validate([
            'name' => 'required|string|max:255',
            'academic_year_id' => ['required', Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'user_id' => ['nullable', Rule::exists('users', 'id')->where('school_id', $schoolId)],
        ]);

        Classroom::create([
            'school_id' => $schoolId,
            'academic_year_id' => $request->academic_year_id,
            'user_id' => $request->user_id,
            'name' => $request->name,
        ]);

        return redirect()->route('admin.classrooms.index')->with('success', 'Kelas berhasil dibuat!');
    }

    /**
     * Form edit kelas
     */
    public function edit(Classroom $classroom)
    {
        $schoolId = Auth::user()->school_id;

        // Keamanan: Pastikan kelas ini milik sekolah admin
        if ($classroom->school_id !== $schoolId) {
            abort(403);
        }

        $academicYears = AcademicYear::where('school_id', $schoolId)->get();
        $teachers = User::role('guru')->where('school_id', $schoolId)->get();

        return view('admin.classrooms.edit', compact('classroom', 'academicYears', 'teachers'));
    }

    /**
     * Update kelas
     */
    public function update(Request $request, Classroom $classroom)
    {
        $schoolId = Auth::user()->school_id;
        if ($classroom->school_id !== $schoolId) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'academic_year_id' => ['required', Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'user_id' => ['nullable', Rule::exists('users', 'id')->where('school_id', $schoolId)],
        ]);

        $classroom->update([
            'academic_year_id' => $request->academic_year_id,
            'user_id' => $request->user_id,
            'name' => $request->name,
        ]);

        return redirect()->route('admin.classrooms.index')->with('success', 'Kelas berhasil diperbarui!');
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

        return redirect()->route('admin.classrooms.index')->with('success', 'Kelas berhasil dihapus!');
    }

    // =========================================================================
    // FITUR PENGELOLAAN SISWA DI DALAM KELAS
    // =========================================================================

    /**
     * Tampilkan form untuk memasukkan/mengeluarkan siswa dari kelas
     */
    public function manageStudents(Classroom $classroom)
    {
        $schoolId = Auth::user()->school_id;
        if ($classroom->school_id !== $schoolId) {
            abort(403);
        }

        // 1. Ambil siswa yang SUDAH ADA di kelas ini
        $classroom->load(['students' => function ($q) {
            $q->orderBy('name', 'asc');
        }]);

        // 2. Ambil ID semua siswa di sekolah ini yang SUDAH PUNYA KELAS
        $assignedStudentIds = \Illuminate\Support\Facades\DB::table('classroom_student')
            ->join('users', 'classroom_student.student_id', '=', 'users.id')
            ->where('users.school_id', $schoolId)
            ->pluck('student_id');

        // 3. Ambil siswa yang BELUM MENDAPAT KELAS
        $unassignedStudents = User::where('school_id', $schoolId)
            ->role('siswa')
            ->whereNotIn('id', $assignedStudentIds)
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.classrooms.students', compact('classroom', 'unassignedStudents'));
    }

    /**
     * Proses simpan daftar siswa ke kelas (Sync)
     */
    public function syncStudents(Request $request, Classroom $classroom)
    {
        $schoolId = Auth::user()->school_id;
        if ($classroom->school_id !== $schoolId) {
            abort(403);
        }

        // Validasi input agar yang dikirim berupa array ID dan siswa tersebut adalah milik sekolah ini
        $request->validate([
            'student_ids' => 'nullable|array',
            'student_ids.*' => [Rule::exists('users', 'id')->where('school_id', $schoolId)],
        ]);

        // Fungsi sync() akan otomatis menambah data baru dan menghapus data lama di tabel pivot (classroom_student)
        $classroom->students()->sync($request->student_ids ?? []);

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

        // syncWithoutDetaching agar siswa yang sudah ada di kelas tidak terhapus
        $classroom->students()->syncWithoutDetaching($request->student_ids);

        return back()->with('success', count($request->student_ids).' siswa berhasil ditambahkan ke kelas!');
    }

    /**
     * Proses mengeluarkan siswa dari kelas
     */
    public function detachStudent(Classroom $classroom, User $student)
    {
        $schoolId = Auth::user()->school_id;
        if ($classroom->school_id !== $schoolId) {
            abort(403);
        }

        $classroom->students()->detach($student->id);

        return back()->with('success', 'Siswa '.$student->name.' berhasil dikeluarkan dari kelas.');
    }
}
