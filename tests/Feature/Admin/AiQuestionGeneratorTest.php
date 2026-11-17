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

class AiQuestionGeneratorTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Exam $exam;

    private array $examSections;

    protected function setUp(): void
    {
        parent::setUp();

        $school = School::create(['name' => 'Sekolah Ujian']);
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
            'title' => 'Simulasi CPNS',
            'slug' => 'simulasi-cpns',
        ]);

        $sections = [
            Section::create(['abbreviation' => 'TIU', 'name' => 'Tes Intelegensia Umum']),
            Section::create(['abbreviation' => 'TKP', 'name' => 'Tes Karakteristik Pribadi']),
        ];
        $this->examSections = collect($sections)->map(fn (Section $section, int $index) => $this->exam->sections()->create([
            'section_id' => $section->id,
            'order' => $index + 1,
        ]))->all();
    }

    public function test_generator_uses_the_exam_route_key_and_offers_tkp_and_exam_sections(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.exams.soal.ai_generator', $this->exam))
            ->assertOk()
            ->assertSee(route('admin.exams.soal.index', $this->exam), false)
            ->assertSee('TKP Berbobot (CPNS)')
            ->assertSee('TKP - Tes Karakteristik Pribadi')
            ->assertSee('TIU - Tes Intelegensia Umum');
    }

    public function test_tkp_question_and_score_weights_are_saved_to_the_selected_exam_section(): void
    {
        $section = $this->examSections[1];
        $questionData = [[
            'type' => 'tkp',
            'content' => '<p>Bagaimana Anda merespons keluhan warga?</p>',
            'explanation' => '<p>Dahulukan pelayanan yang profesional.</p>',
            'options' => [
                ['option_text' => 'Mengabaikan keluhan.', 'is_correct' => false, 'score_weight' => 1],
                ['option_text' => 'Menunda tanpa alasan.', 'is_correct' => false, 'score_weight' => 2],
                ['option_text' => 'Mengarahkan ke petugas lain.', 'is_correct' => false, 'score_weight' => 3],
                ['option_text' => 'Mendengarkan dan menindaklanjuti.', 'is_correct' => false, 'score_weight' => 4],
                ['option_text' => 'Mendengar, memeriksa, lalu memberi solusi.', 'is_correct' => false, 'score_weight' => 5],
            ],
        ]];

        $this->actingAs($this->admin)
            ->post(route('admin.exams.soal.ai_preview', $this->exam), [
                'exam_section_id' => $section->id,
                'json_data' => json_encode($questionData),
            ])
            ->assertOk()
            ->assertSee('TKP - Tes Karakteristik Pribadi')
            ->assertSee('Bobot 5');

        $this->actingAs($this->admin)
            ->post(route('admin.soal.import_json_store', $this->exam), [
                'exam_section_id' => $section->id,
                'json_data' => base64_encode(json_encode($questionData)),
                'selected_indexes' => [0],
            ])
            ->assertRedirect(route('admin.exams.soal.index', [
                'exam' => $this->exam,
                'section_id' => $section->id,
            ]));

        $question = Question::where('content', $questionData[0]['content'])->firstOrFail();
        $this->assertSame($section->id, $question->exam_section_id);
        $this->assertSame([1.0, 2.0, 3.0, 4.0, 5.0], $question->options()->orderBy('id')->pluck('score_weight')->all());
    }

    public function test_tkp_preview_rejects_missing_or_invalid_score_weights(): void
    {
        $section = $this->examSections[1];
        $questionData = [[
            'type' => 'tkp',
            'content' => '<p>Bagaimana Anda merespons keluhan warga?</p>',
            'options' => [
                ['option_text' => 'Respons pertama.', 'score_weight' => 1],
                ['option_text' => 'Respons kedua.', 'score_weight' => 2],
                ['option_text' => 'Respons ketiga.', 'score_weight' => 3],
                ['option_text' => 'Respons keempat.', 'score_weight' => 3],
                ['option_text' => 'Respons kelima.', 'score_weight' => 5],
            ],
        ]];
        $generatorUrl = route('admin.exams.soal.ai_generator', $this->exam);

        $this->actingAs($this->admin)
            ->from($generatorUrl)
            ->post(route('admin.exams.soal.ai_preview', $this->exam), [
                'exam_section_id' => $section->id,
                'json_data' => json_encode($questionData),
            ])
            ->assertRedirect($generatorUrl)
            ->assertSessionHasErrors('error');
    }
}
