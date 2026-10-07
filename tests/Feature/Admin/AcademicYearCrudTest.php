<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AcademicYearCrudTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::create([
            'name' => 'Sekolah Test',
            'domain' => 'test.local',
        ]);

        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $this->admin = User::factory()->create(['school_id' => $this->school->id]);
        $this->admin->assignRole($role);
    }

    public function test_admin_can_view_and_create_academic_years(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.academic-years.index'))
            ->assertOk()
            ->assertSee('Tahun Pelajaran');

        $this->actingAs($this->admin)
            ->post(route('admin.academic-years.store'), [
                'name' => '2026/2027',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.academic-years.index'));

        $this->assertDatabaseHas('academic_years', [
            'school_id' => $this->school->id,
            'name' => '2026/2027',
            'is_active' => true,
        ]);
    }

    public function test_activating_an_academic_year_deactivates_the_previous_one(): void
    {
        $previous = AcademicYear::create([
            'school_id' => $this->school->id,
            'name' => '2025/2026',
            'is_active' => true,
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.academic-years.store'), [
                'name' => '2026/2027',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.academic-years.index'));

        $this->assertDatabaseHas('academic_years', ['id' => $previous->id, 'is_active' => false]);
        $this->assertDatabaseHas('academic_years', [
            'school_id' => $this->school->id,
            'name' => '2026/2027',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_update_and_delete_an_unused_academic_year(): void
    {
        $year = AcademicYear::create([
            'school_id' => $this->school->id,
            'name' => '2025/2026',
            'is_active' => false,
        ]);

        $this->actingAs($this->admin)
            ->put(route('admin.academic-years.update', $year), [
                'name' => '2026/2027',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.academic-years.index'));

        $this->assertDatabaseHas('academic_years', [
            'id' => $year->id,
            'name' => '2026/2027',
            'is_active' => true,
        ]);

        $this->delete(route('admin.academic-years.destroy', $year))
            ->assertRedirect(route('admin.academic-years.index'));

        $this->assertDatabaseMissing('academic_years', ['id' => $year->id]);
    }

    public function test_academic_years_are_school_scoped_and_used_years_cannot_be_deleted(): void
    {
        $otherSchool = School::create(['name' => 'Sekolah Lain', 'domain' => 'other.local']);
        $otherYear = AcademicYear::create([
            'school_id' => $otherSchool->id,
            'name' => '2026/2027',
            'is_active' => true,
        ]);

        $this->actingAs($this->admin)
            ->put(route('admin.academic-years.update', $otherYear), [
                'name' => 'Diubah',
                'is_active' => '1',
            ])
            ->assertNotFound();

        $year = AcademicYear::create([
            'school_id' => $this->school->id,
            'name' => '2026/2027',
            'is_active' => false,
        ]);
        $classroom = Classroom::create([
            'school_id' => $this->school->id,
            'academic_year_id' => $year->id,
            'name' => 'Kelas 1',
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.academic-years.destroy', $year))
            ->assertRedirect(route('admin.academic-years.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('academic_years', ['id' => $year->id]);
        $this->assertDatabaseHas('classrooms', ['id' => $classroom->id]);
    }
}
