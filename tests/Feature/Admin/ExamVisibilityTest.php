<?php

namespace Tests\Feature\Admin;

use App\Models\Exam;
use App\Models\ExamType;
use App\Models\Level;
use App\Models\School;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ExamVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_without_a_school_can_view_exams_from_any_school(): void
    {
        $school = School::create([
            'name' => 'Sekolah Ujian',
            'domain' => 'ujian.local',
        ]);

        $admin = User::factory()->create(['school_id' => null]);
        $admin->assignRole(Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']));

        $teacher = User::factory()->create(['school_id' => $school->id]);
        $teacher->assignRole(Role::firstOrCreate(['name' => 'guru', 'guard_name' => 'web']));
        $examType = ExamType::create(['school_id' => $school->id, 'name' => 'Penilaian Harian']);
        $level = Level::create(['school_id' => $school->id, 'name' => 'Kelas 1']);
        $subject = Subject::create(['school_id' => $school->id, 'name' => 'Matematika']);

        Exam::create([
            'school_id' => $school->id,
            'teacher_id' => $teacher->id,
            'exam_type_id' => $examType->id,
            'level_id' => $level->id,
            'subject_id' => $subject->id,
            'title' => 'Ujian dari sekolah',
            'slug' => 'ujian-dari-sekolah',
            'status' => 'published',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.exams.index'))
            ->assertOk()
            ->assertSee('Ujian dari sekolah');
    }
}
