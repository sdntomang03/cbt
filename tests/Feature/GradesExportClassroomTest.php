<?php

namespace Tests\Feature;

use App\Exports\GradesExport;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Level;
use App\Models\School;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GradesExportClassroomTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_shows_active_class_or_a_note_when_student_is_unassigned(): void
    {
        Role::firstOrCreate(['name' => 'siswa', 'guard_name' => 'web']);

        $school = School::create(['name' => 'Sekolah Uji']);
        $teacher = User::factory()->create(['school_id' => $school->id]);
        $level = Level::create(['name' => 'Kelas X', 'school_id' => $school->id]);
        $subject = Subject::create(['name' => 'Matematika', 'school_id' => $school->id]);
        $exam = Exam::create([
            'school_id' => $school->id,
            'teacher_id' => $teacher->id,
            'level_id' => $level->id,
            'subject_id' => $subject->id,
            'title' => 'Ujian Matematika',
            'slug' => 'ujian-matematika',
        ]);
        $session = ExamSession::create([
            'school_id' => $school->id,
            'exam_id' => $exam->id,
            'session_name' => 'Sesi 1',
            'start_time' => now(),
            'end_time' => now()->addHour(),
        ]);

        $activeYear = AcademicYear::create([
            'school_id' => $school->id,
            'name' => 'Tahun Aktif',
            'is_active' => true,
        ]);
        $previousYear = AcademicYear::create([
            'school_id' => $school->id,
            'name' => 'Tahun Sebelumnya',
            'is_active' => false,
        ]);
        $activeClass = Classroom::create([
            'school_id' => $school->id,
            'academic_year_id' => $activeYear->id,
            'name' => 'X IPA 1',
        ]);
        $previousClass = Classroom::create([
            'school_id' => $school->id,
            'academic_year_id' => $previousYear->id,
            'name' => 'IX IPA 1',
        ]);

        $assignedStudent = $this->createStudent('Siswa Masuk Kelas');
        $unassignedWithNote = $this->createStudent('Siswa Tanpa Kelas', 'Pindahan');
        $unassignedWithoutNote = $this->createStudent('Siswa Tanpa Keterangan');

        $activeClass->students()->attach($assignedStudent->id, ['academic_year_id' => $activeYear->id]);
        $previousClass->students()->attach($assignedStudent->id, ['academic_year_id' => $previousYear->id]);
        $session->students()->attach([
            $assignedStudent->id => ['final_score' => 90],
            $unassignedWithNote->id => ['final_score' => 80],
            $unassignedWithoutNote->id => ['final_score' => 70],
        ]);

        $export = new GradesExport($exam->id);
        $students = $export->query()->get()->keyBy('username');

        $this->assertSame('Kelas / Keterangan', $export->headings()[4]);
        $this->assertSame('X IPA 1', $export->map($students[$assignedStudent->username])[4]);
        $this->assertSame('Pindahan', $export->map($students[$unassignedWithNote->username])[4]);
        $this->assertSame('Belum masuk kelas', $export->map($students[$unassignedWithoutNote->username])[4]);
    }

    private function createStudent(string $name, ?string $keterangan = null): User
    {
        $student = User::factory()->create([
            'name' => $name,
            'keterangan' => $keterangan,
        ]);
        $student->assignRole('siswa');

        return $student;
    }
}
