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
    public function index()
    {
        $schoolId = $this->schoolId();
        $school = School::findOrFail($schoolId);
        $academicYears = AcademicYear::where('school_id', $schoolId)
            ->withCount('classrooms')
            ->orderByDesc('is_active')
            ->orderByDesc('name')
            ->get();

        return view('admin.academic-years.index', compact('academicYears', 'school'));
    }

    public function store(Request $request)
    {
        $schoolId = $this->schoolId();
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

        return redirect()->route('admin.academic-years.index')
            ->with('success', 'Tahun pelajaran berhasil ditambahkan.');
    }

    public function update(Request $request, AcademicYear $academicYear)
    {
        $schoolId = $this->schoolId();
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

        return redirect()->route('admin.academic-years.index')
            ->with('success', 'Tahun pelajaran berhasil diperbarui.');
    }

    public function destroy(AcademicYear $academicYear)
    {
        $schoolId = $this->schoolId();
        $this->ensureBelongsToSchool($academicYear, $schoolId);

        if ($academicYear->classrooms()->exists()) {
            return redirect()->route('admin.academic-years.index')
                ->with('error', 'Tahun pelajaran tidak dapat dihapus karena masih digunakan oleh kelas.');
        }

        $academicYear->delete();

        return redirect()->route('admin.academic-years.index')
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

    private function schoolId(): int
    {
        $schoolId = auth()->user()->school_id;
        abort_if(! $schoolId, 403, 'Akun belum terhubung ke sekolah.');

        return (int) $schoolId;
    }

    private function ensureBelongsToSchool(AcademicYear $academicYear, int $schoolId): void
    {
        abort_unless($academicYear->school_id === $schoolId, 404);
    }
}
