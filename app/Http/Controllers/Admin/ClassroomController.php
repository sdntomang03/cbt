<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Level;
use App\Models\School;
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
    public function index(Request $request)
    {
        $user = Auth::user();
        $schools = $user->hasRole('admin')
            ? School::orderBy('name')->get()
            : collect();
        $selectedSchoolId = $user->hasRole('admin')
            ? $request->validate([
                'school_id' => ['nullable', 'integer', Rule::exists('schools', 'id')],
            ])['school_id'] ?? null
            : $user->school_id;

        $classrooms = collect();
        $academicYears = collect();
        $levels = collect();
        $teachers = collect();

        if ($selectedSchoolId) {
            $query = Classroom::with([
                'homeroomTeacher' => fn ($query) => $query->where('school_id', $selectedSchoolId),
                'academicYear' => fn ($query) => $query->where('school_id', $selectedSchoolId),
            ])
                ->withCount([
                    'students' => fn ($query) => $query->where('users.school_id', $selectedSchoolId),
                ])
                ->where('school_id', $selectedSchoolId);

            if (! $user->hasAnyRole(['admin', 'operator'])) {
                $query->where('user_id', $user->id);
            }

            $classrooms = $query->orderBy('name', 'asc')->get();
            $academicYears = AcademicYear::where('school_id', $selectedSchoolId)->get();
            $levels = Level::where('school_id', $selectedSchoolId)->get();
            $teachers = User::role('guru')->where('school_id', $selectedSchoolId)->get();
        }

        return view('admin.classrooms.index', compact(
            'classrooms',
            'academicYears',
            'teachers',
            'levels',
            'schools',
            'selectedSchoolId'
        ));
    }

    /**
     * Simpan kelas baru via Modal (POST)
     */
    public function store(Request $request)
    {
        $schoolId = $this->selectedSchoolId($request);

        $request->validate([
            'name' => 'required|string|max:255',
            'academic_year_id' => ['nullable', Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'homeroom_teacher_id' => ['nullable', Rule::exists('users', 'id')->where('school_id', $schoolId)],
            'level_id' => ['nullable', Rule::exists('levels', 'id')->where('school_id', $schoolId)],
        ]);

        Classroom::create([
            'school_id' => $schoolId,
            'academic_year_id' => $request->academic_year_id,
            'user_id' => $request->homeroom_teacher_id, // Perhatikan pemetaan field DB dengan input form
            'level_id' => $request->level_id,
            'name' => $request->name,
        ]);

        return redirect()->route('admin.classrooms.index', ['school_id' => $schoolId])
            ->with('success', 'Kelas berhasil dibuat!');
    }

    /**
     * Update kelas via Modal (PUT)
     */
    public function update(Request $request, Classroom $classroom)
    {
        $this->authorizeClassroom($classroom);
        $schoolId = (int) $classroom->school_id;

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

        return redirect()->route('admin.classrooms.index', ['school_id' => $schoolId])
            ->with('success', 'Kelas berhasil diperbarui!');
    }

    /**
     * Hapus kelas
     */
    public function destroy(Classroom $classroom)
    {
        $this->authorizeClassroom($classroom);

        $classroom->delete();

        return redirect()->route('admin.classrooms.index', ['school_id' => $classroom->school_id])
            ->with('success', 'Kelas berhasil dihapus!');
    }

    // =========================================================================
    // FITUR PENGELOLAAN SISWA DI DALAM KELAS (TETAP SAMA KARENA DI HALAMAN TERPISAH)
    // =========================================================================

    public function manageStudents(Classroom $classroom)
    {
        $this->authorizeClassroom($classroom);
        $schoolId = (int) $classroom->school_id;

        // 1. Tampilkan daftar siswa yang SUDAH ADA DI KELAS INI
        $classroom->load(['students' => function ($q) use ($classroom, $schoolId) {
            $q->where('users.school_id', $schoolId)
                ->where('classroom_student.academic_year_id', $classroom->academic_year_id)
                ->orderBy('name', 'asc');
        }]);

        // 2. Cari semua ID siswa yang SUDAH PUNYA KELAS MANAPUN pada tahun ajaran yang sama
        $assignedStudentIds = DB::table('classroom_student')
            ->join('classrooms', 'classroom_student.classroom_id', '=', 'classrooms.id')
            ->where('classrooms.school_id', $schoolId)
            ->where('classroom_student.academic_year_id', $classroom->academic_year_id)
            ->pluck('classroom_student.student_id')
            ->toArray();

        // 3. Tarik SEMUA siswa di sekolah ini yang BELUM DAPAT KELAS
        $unassignedStudents = User::where('school_id', $schoolId)
            ->role('siswa')
            ->whereNotIn('id', $assignedStudentIds)
            ->orderBy('name', 'asc')
            ->get();

        $studentKeterangan = User::where('school_id', $schoolId)
            ->role('siswa')
            ->whereNotNull('keterangan')
            ->where('keterangan', '<>', '')
            ->distinct()
            ->orderBy('keterangan')
            ->pluck('keterangan');

        return view('admin.classrooms.students', compact('classroom', 'unassignedStudents', 'studentKeterangan'));
    }

    public function syncStudents(Request $request, Classroom $classroom)
    {
        $this->authorizeClassroom($classroom);
        $schoolId = (int) $classroom->school_id;

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

        return redirect()->route('admin.classrooms.index', ['school_id' => $schoolId])
            ->with('success', 'Daftar siswa di kelas '.$classroom->name.' berhasil diperbarui!');
    }

    public function attachStudents(Request $request, Classroom $classroom)
    {
        $this->authorizeClassroom($classroom);
        $schoolId = (int) $classroom->school_id;

        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => [Rule::exists('users', 'id')->where('school_id', $schoolId)],
        ]);

        // Tambahkan academic_year_id saat insert siswa ke tabel pivot
        $pivotData = [];
        foreach ($request->student_ids as $studentId) {
            $pivotData[$studentId] = ['academic_year_id' => $classroom->academic_year_id];
        }

        $classroom->students()->syncWithoutDetaching($pivotData);

        return redirect()->route('admin.classrooms.students', [
            'classroom' => $classroom->id,
            'school_id' => $schoolId,
        ])->with('success', count($request->student_ids).' siswa berhasil ditambahkan ke kelas!');
    }

    public function detachStudent(Classroom $classroom, User $student)
    {
        $this->authorizeClassroom($classroom);
        $schoolId = (int) $classroom->school_id;
        abort_unless((int) $student->school_id === $schoolId, 404);

        // Hapus spesifik berdasarkan student, classroom, dan tahun ajaran
        // (Agar riwayat kelas siswa di tahun ajaran sebelumnya tidak ikut terhapus)
        DB::table('classroom_student')
            ->where('classroom_id', $classroom->id)
            ->where('student_id', $student->id)
            ->where('academic_year_id', $classroom->academic_year_id)
            ->delete();

        return redirect()->route('admin.classrooms.students', [
            'classroom' => $classroom->id,
            'school_id' => $schoolId,
        ])->with('success', 'Siswa '.$student->name.' berhasil dikeluarkan dari kelas.');
    }

    private function selectedSchoolId(Request $request): int
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            return (int) $request->validate([
                'school_id' => ['required', 'integer', Rule::exists('schools', 'id')],
            ])['school_id'];
        }

        abort_unless($user->school_id, 403);

        return (int) $user->school_id;
    }

    private function authorizeClassroom(Classroom $classroom): void
    {
        $user = Auth::user();

        abort_unless(
            $user->hasRole('admin') || (int) $classroom->school_id === (int) $user->school_id,
            403
        );
    }
}
