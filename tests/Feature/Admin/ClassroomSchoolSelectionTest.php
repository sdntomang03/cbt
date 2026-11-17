<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ClassroomSchoolSelectionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'guru', 'guard_name' => 'web']);
        $this->admin = User::factory()->create(['school_id' => null]);
        $this->admin->assignRole($role);
    }

    public function test_admin_must_select_a_school_and_classroom_data_is_scoped_to_it(): void
    {
        $school = School::create(['name' => 'Sekolah Pilihan']);
        $otherSchool = School::create(['name' => 'Sekolah Lain']);
        $year = AcademicYear::create([
            'school_id' => $school->id,
            'name' => 'Tahun Pilihan',
        ]);
        AcademicYear::create([
            'school_id' => $otherSchool->id,
            'name' => 'Tahun Lain',
        ]);
        Classroom::create([
            'school_id' => $school->id,
            'academic_year_id' => $year->id,
            'name' => 'Kelas Pilihan',
        ]);
        Classroom::create([
            'school_id' => $otherSchool->id,
            'academic_year_id' => AcademicYear::where('school_id', $otherSchool->id)->first()->id,
            'name' => 'Kelas Lain',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.classrooms.index'))
            ->assertOk()
            ->assertSee('Pilih sekolah terlebih dahulu.')
            ->assertDontSee('Kelas Pilihan')
            ->assertDontSee('Kelas Lain');

        $this->actingAs($this->admin)
            ->get(route('admin.classrooms.index', ['school_id' => $school->id]))
            ->assertOk()
            ->assertSee('Kelas Pilihan')
            ->assertSee('Tahun Pilihan')
            ->assertDontSee('Kelas Lain')
            ->assertDontSee('Tahun Lain');
    }

    public function test_admin_creates_a_class_in_the_selected_school_and_cannot_use_another_schools_year(): void
    {
        $school = School::create(['name' => 'Sekolah Pilihan']);
        $otherSchool = School::create(['name' => 'Sekolah Lain']);
        $year = AcademicYear::create([
            'school_id' => $school->id,
            'name' => 'Tahun Pilihan',
        ]);
        $otherYear = AcademicYear::create([
            'school_id' => $otherSchool->id,
            'name' => 'Tahun Lain',
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.classrooms.store'), [
                'school_id' => $school->id,
                'academic_year_id' => $otherYear->id,
                'name' => 'Kelas Tidak Valid',
            ])
            ->assertSessionHasErrors('academic_year_id');

        $this->assertDatabaseMissing('classrooms', ['name' => 'Kelas Tidak Valid']);

        $this->actingAs($this->admin)
            ->post(route('admin.classrooms.store'), [
                'school_id' => $school->id,
                'academic_year_id' => $year->id,
                'name' => 'Kelas Baru',
            ])
            ->assertRedirect(route('admin.classrooms.index', ['school_id' => $school->id]));

        $this->assertDatabaseHas('classrooms', [
            'school_id' => $school->id,
            'academic_year_id' => $year->id,
            'name' => 'Kelas Baru',
        ]);
    }
}
