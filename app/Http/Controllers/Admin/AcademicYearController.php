<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AcademicYearController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $schools = $user->hasRole('admin')
            ? School::orderBy('name')->get()
            : collect();
        $selectedSchoolId = $user->hasRole('admin')
            ? $request->validate([
                'school_id' => ['nullable', 'integer', Rule::exists('schools', 'id')],
            ])['school_id'] ?? null
            : $user->school_id;

        $school = $selectedSchoolId ? School::findOrFail($selectedSchoolId) : null;
        $academicYears = $selectedSchoolId
            ? AcademicYear::where('school_id', $selectedSchoolId)
                ->withCount('classrooms')
                ->orderByDesc('is_active')
                ->orderByDesc('name')
                ->get()
            : collect();

        return view('admin.academic-years.index', compact(
            'academicYears',
            'school',
            'schools',
            'selectedSchoolId'
        ));
    }

    public function store(Request $request)
    {
        $schoolId = $this->schoolId($request);
        $data = $this->validated($request, $schoolId);

        DB::transaction(function () use ($data, $schoolId) {
            School::whereKey($schoolId)->lockForUpdate()->firstOrFail();

            if ($data['is_active']) {
                AcademicYear::where('school_id', $schoolId)->update(['is_active' => false]);
            }

            AcademicYear::create([
                'school_id' => $schoolId,
                'name' => $data['name'],
                'is_active' => $data['is_active'],
            ]);
        });

        return redirect()->route('admin.academic-years.index', ['school_id' => $schoolId])
            ->with('success', 'Tahun pelajaran berhasil ditambahkan.');
    }

    public function update(Request $request, AcademicYear $academicYear)
    {
        $schoolId = $this->schoolId($request);
        $this->ensureBelongsToSchool($academicYear, $schoolId);
        $data = $this->validated($request, $schoolId, $academicYear);

        DB::transaction(function () use ($academicYear, $data, $schoolId) {
            School::whereKey($schoolId)->lockForUpdate()->firstOrFail();

            if ($data['is_active']) {
                AcademicYear::where('school_id', $schoolId)
                    ->whereKeyNot($academicYear->id)
                    ->update(['is_active' => false]);
            }

            $academicYear->update($data);
        });

        return redirect()->route('admin.academic-years.index', ['school_id' => $schoolId])
            ->with('success', 'Tahun pelajaran berhasil diperbarui.');
    }

    public function destroy(Request $request, AcademicYear $academicYear)
    {
        $schoolId = $this->schoolId($request);
        $this->ensureBelongsToSchool($academicYear, $schoolId);

        if ($academicYear->classrooms()->exists()) {
            return redirect()->route('admin.academic-years.index', ['school_id' => $schoolId])
                ->with('error', 'Tahun pelajaran tidak dapat dihapus karena masih digunakan oleh kelas.');
        }

        $academicYear->delete();

        return redirect()->route('admin.academic-years.index', ['school_id' => $schoolId])
            ->with('success', 'Tahun pelajaran berhasil dihapus.');
    }

    private function validated(Request $request, int $schoolId, ?AcademicYear $academicYear = null): array
    {
        $uniqueName = Rule::unique('academic_years', 'name')
            ->where('school_id', $schoolId);

        if ($academicYear) {
            $uniqueName->ignore($academicYear->id);
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:255', $uniqueName],
            'is_active' => ['required', 'boolean'],
        ]);
    }

    private function schoolId(Request $request): int
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return (int) $request->validate([
                'school_id' => ['required', 'integer', Rule::exists('schools', 'id')],
            ])['school_id'];
        }

        $schoolId = $user->school_id;
        abort_if(! $schoolId, 403, 'Akun belum terhubung ke sekolah.');

        return (int) $schoolId;
    }

    private function ensureBelongsToSchool(AcademicYear $academicYear, int $schoolId): void
    {
        abort_unless((int) $academicYear->school_id === $schoolId, 404);
    }
}
