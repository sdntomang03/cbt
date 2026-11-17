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

class JsonQuestionImportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Exam $exam;

    private int $examSectionId;

    protected function setUp(): void
    {
        parent::setUp();

        $school = School::create(['name' => 'Sekolah JSON']);
        $this->admin = User::factory()->create(['school_id' => $school->id]);
        $this->admin->assignRole(Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']));

        $examType = ExamType::create(['school_id' => $school->id, 'name' => 'CPNS']);
        $level = Level::create(['name' => 'Umum']);
        $subject = Subject::create(['name' => 'CPNS']);
        $this->exam = Exam::create([
            'school_id' => $school->id,
            'teacher_id' => $this->admin->id,
            'exam_type_id' => $examType->id,
            'level_id' => $level->id,
            'subject_id' => $subject->id,
            'title' => 'Import JSON CPNS',
            'slug' => 'import-json-cpns',
        ]);

        $section = Section::create(['abbreviation' => 'TKP', 'name' => 'Tes Karakteristik Pribadi']);
        $this->examSectionId = $this->exam->sections()->create([
            'section_id' => $section->id,
            'order' => 1,
        ])->id;
    }

    public function test_json_import_preview_and_storage_keep_tkp_score_weights(): void
    {
        $questions = [[
            'type' => 'TKP Berbobot',
            'content' => '<p>Seorang warga menyampaikan keluhan layanan.</p>',
            'explanation' => '<p>Tindak lanjuti secara profesional.</p>',
            'options' => [
                ['option_text' => 'Mengabaikan keluhan.', 'score_weight' => 1],
                ['option_text' => 'Meminta warga kembali.', 'score_weight' => 2],
                ['option_text' => 'Meneruskan tanpa memeriksa.', 'score_weight' => 3],
                ['option_text' => 'Memeriksa penyebab keluhan.', 'score_weight' => 4],
                ['option_text' => 'Memeriksa dan memberi solusi.', 'score_weight' => 5],
            ],
        ]];

        $previewResponse = $this->actingAs($this->admin)
            ->post(route('admin.soal.import_json_preview', $this->exam), [
                'text_json' => json_encode($questions),
            ])
            ->assertOk()
            ->assertSee('tkp')
            ->assertSee('Bobot 5');

        preg_match('/name="json_data" value="([^"]+)"/', $previewResponse->getContent(), $matches);

        $this->assertNotEmpty($matches[1] ?? null, 'Preview should submit the normalized question JSON.');

        $this->actingAs($this->admin)
            ->post(route('admin.soal.import_json_store', $this->exam), [
                'json_data' => $matches[1],
                'selected_indexes' => [0],
            ])
            ->assertRedirect(route('admin.exams.soal.index', [
                'exam' => $this->exam,
                'section_id' => $this->examSectionId,
            ]));

        $question = Question::where('content', $questions[0]['content'])->firstOrFail();
        $this->assertSame('tkp', $question->type);
        $this->assertSame(
            [1.0, 2.0, 3.0, 4.0, 5.0],
            $question->options()->orderBy('id')->pluck('score_weight')->all()
        );
    }
}
