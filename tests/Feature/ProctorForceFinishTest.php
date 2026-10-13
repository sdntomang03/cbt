<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamSection;
use App\Models\ExamSession;
use App\Models\Level;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\School;
use App\Models\Section;
use App\Models\StudentAnswer;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProctorForceFinishTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private ExamSession $examSession;

    private QuestionOption $correctOption;

    protected function setUp(): void
    {
        parent::setUp();

        $school = School::create(['name' => 'Sekolah Test']);
        $this->admin = User::factory()->create(['school_id' => $school->id]);
        $this->admin->assignRole(Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']));

        $exam = Exam::create([
            'school_id' => $school->id,
            'teacher_id' => $this->admin->id,
            'level_id' => Level::create(['name' => 'Kelas Test'])->id,
            'subject_id' => Subject::create(['name' => 'Mapel Test'])->id,
            'title' => 'Ujian Test',
            'slug' => 'ujian-test',
            'status' => 'published',
        ]);

        $this->examSession = ExamSession::create([
            'school_id' => $school->id,
            'exam_id' => $exam->id,
            'session_name' => 'Sesi Test',
            'start_time' => now()->subHour(),
            'end_time' => now()->addHour(),
        ]);

        $section = Section::create(['name' => 'Sesi Utama', 'abbreviation' => 'SU']);
        $examSection = ExamSection::create([
            'exam_id' => $exam->id,
            'section_id' => $section->id,
            'order' => 1,
        ]);
        $question = Question::create([
            'exam_section_id' => $examSection->id,
            'user_id' => $this->admin->id,
            'type' => 'single_choice',
            'content' => 'Pertanyaan tes',
        ]);
        $this->correctOption = QuestionOption::create([
            'question_id' => $question->id,
            'option_text' => 'Jawaban benar',
            'is_correct' => true,
        ]);
    }

    public function test_admin_can_force_finish_one_participant_and_calculate_their_score(): void
    {
        $student = User::factory()->create(['school_id' => $this->admin->school_id]);
        $student->assignRole(Role::firstOrCreate(['name' => 'siswa', 'guard_name' => 'web']));
        $attempt = $this->createAttempt($student, 'ongoing');
        StudentAnswer::create([
            'exam_attempt_id' => $attempt->id,
            'question_id' => $this->correctOption->question_id,
            'answer' => $this->correctOption->id,
        ]);

        $response = $this->actingAs($this->admin)->postJson(
            route('proctor.force-finish', [$this->examSession, $student])
        );

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', "Ujian siswa {$student->name} diselesaikan. Nilai akhir: 100");

        $this->assertDatabaseHas('exam_attempts', [
            'id' => $attempt->id,
            'status' => 'completed',
            'final_score' => 100,
        ]);
        $this->assertNotNull($attempt->fresh()->finished_at);

        $this->actingAs($student)
            ->get(route('student.exam.result', $this->examSession->exam))
            ->assertOk()
            ->assertSee('100.00');
    }

    public function test_admin_can_force_finish_all_unfinished_participants_without_changing_completed_scores(): void
    {
        $ongoingStudent = User::factory()->create();
        $unstartedStudent = User::factory()->create();
        $completedStudent = User::factory()->create();

        $ongoingAttempt = $this->createAttempt($ongoingStudent, 'ongoing');
        $unstartedAttempt = $this->createAttempt($unstartedStudent, 'not_started');
        $completedAttempt = $this->createAttempt($completedStudent, 'completed', 87);

        StudentAnswer::create([
            'exam_attempt_id' => $ongoingAttempt->id,
            'question_id' => $this->correctOption->question_id,
            'answer' => $this->correctOption->id,
        ]);

        $response = $this->actingAs($this->admin)->postJson(
            route('proctor.force-finish-all', $this->examSession)
        );

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('completed', 2);

        $this->assertDatabaseHas('exam_attempts', [
            'id' => $ongoingAttempt->id,
            'status' => 'completed',
            'final_score' => 100,
        ]);
        $this->assertDatabaseHas('exam_attempts', [
            'id' => $unstartedAttempt->id,
            'status' => 'completed',
            'final_score' => 0,
        ]);
        $this->assertDatabaseHas('exam_attempts', [
            'id' => $completedAttempt->id,
            'status' => 'completed',
            'final_score' => 87,
        ]);
    }

    private function createAttempt(User $student, string $status, float $finalScore = 0): ExamAttempt
    {
        return ExamAttempt::create([
            'exam_session_id' => $this->examSession->id,
            'user_id' => $student->id,
            'status' => $status,
            'final_score' => $finalScore,
        ]);
    }
}
