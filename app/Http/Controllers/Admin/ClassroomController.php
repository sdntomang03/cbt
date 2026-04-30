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
        $teachers = User::role('guru')->where('school_id', $schoolId)->get();

        return view('admin.classrooms.index', compact('classrooms', 'academicYears', 'teachers'));
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
        ]);

        $classroom->update([
            'academic_year_id' => $request->academic_year_id,
            'user_id' => $request->homeroom_teacher_id,
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

        $classroom->load(['students' => function ($q) {
            $q->orderBy('name', 'asc');
        }]);

        $assignedStudentIds = \Illuminate\Support\Facades\DB::table('classroom_student')
            ->join('users', 'classroom_student.student_id', '=', 'users.id')
            ->where('users.school_id', $schoolId)
            ->pluck('student_id');

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

        $classroom->students()->syncWithoutDetaching($request->student_ids);

        return back()->with('success', count($request->student_ids).' siswa berhasil ditambahkan ke kelas!');
    }

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
