<?php

namespace Tests\Feature\Admin;

use App\Models\Exam;
use App\Models\ExamType;
use App\Models\Level;
use App\Models\Question;
use App\Models\School;
use App\Models\Section;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QuestionBulkDeleteTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Exam $exam;

    private int $sectionId;

    private int $otherSectionId;

    protected function setUp(): void
    {
        parent::setUp();

        $school = School::create(['name' => 'Sekolah Soal']);
        $this->admin = User::factory()->create(['school_id' => $school->id]);
        $this->admin->assignRole(Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']));

        $examType = ExamType::create(['school_id' => $school->id, 'name' => 'Ujian']);
        $level = Level::create(['name' => 'Umum']);
        $subject = Subject::create(['name' => 'Umum']);
        $this->exam = Exam::create([
            'school_id' => $school->id,
            'teacher_id' => $this->admin->id,
            'exam_type_id' => $examType->id,
            'level_id' => $level->id,
            'subject_id' => $subject->id,
            'title' => 'Ujian Soal',
            'slug' => 'ujian-soal-bulk',
        ]);

        $section = Section::create(['abbreviation' => 'TWK', 'name' => 'Tes Wawasan Kebangsaan']);
        $otherSection = Section::create(['abbreviation' => 'TIU', 'name' => 'Tes Intelegensia Umum']);
        $this->sectionId = $this->exam->sections()->create([
            'section_id' => $section->id,
            'order' => 1,
        ])->id;
        $this->otherSectionId = $this->exam->sections()->create([
            'section_id' => $otherSection->id,
            'order' => 2,
        ])->id;
    }

    public function test_section_question_list_exposes_bulk_selection_and_bulk_delete_preserves_other_questions(): void
    {
        $firstQuestion = $this->createQuestion($this->sectionId, 'Soal pertama');
        $secondQuestion = $this->createQuestion($this->sectionId, 'Soal kedua');
        $remainingQuestion = $this->createQuestion($this->sectionId, 'Soal tersisa');
        $otherSectionQuestion = $this->createQuestion($this->otherSectionId, 'Soal seksi lain');

        $this->actingAs($this->admin)
            ->get(route('admin.exams.soal.index', [
                'exam' => $this->exam,
                'section_id' => $this->sectionId,
            ]))
            ->assertOk()
            ->assertSee('Pilih semua soal di halaman ini')
            ->assertSee('question_ids[]');

        $this->actingAs($this->admin)
            ->delete(route('admin.exams.soal.bulk-destroy', $this->exam), [
                'section_id' => $this->sectionId,
                'question_ids' => [$firstQuestion->id, $secondQuestion->id],
            ])
            ->assertRedirect(route('admin.exams.soal.index', [
                'exam' => $this->exam,
                'section_id' => $this->sectionId,
            ]))
            ->assertSessionHas('success', '2 soal berhasil dihapus dari seksi.');

        $this->assertDatabaseMissing('questions', ['id' => $firstQuestion->id]);
        $this->assertDatabaseMissing('questions', ['id' => $secondQuestion->id]);
        $this->assertDatabaseHas('questions', ['id' => $remainingQuestion->id]);
        $this->assertDatabaseHas('questions', ['id' => $otherSectionQuestion->id]);
    }

    public function test_bulk_delete_rejects_questions_outside_the_selected_section(): void
    {
        $question = $this->createQuestion($this->otherSectionId, 'Soal seksi lain');

        $this->actingAs($this->admin)
            ->delete(route('admin.exams.soal.bulk-destroy', $this->exam), [
                'section_id' => $this->sectionId,
                'question_ids' => [$question->id],
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('questions', ['id' => $question->id]);
    }

    private function createQuestion(int $examSectionId, string $content): Question
    {
        return Question::create([
            'exam_section_id' => $examSectionId,
            'user_id' => $this->admin->id,
            'school_id' => $this->admin->school_id,
            'type' => 'essay',
            'content' => $content,
        ]);
    }
}
