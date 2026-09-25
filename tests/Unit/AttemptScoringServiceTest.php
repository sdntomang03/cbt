<?php

namespace Tests\Unit;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\ScoringProfile;
use App\Services\AttemptScoringService;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class AttemptScoringServiceTest extends TestCase
{
    public function test_tka_uses_standard_correct_answer_scoring(): void
    {
        $question = $this->singleChoiceQuestion();

        $result = app(AttemptScoringService::class)->scoreQuestion($question, 2);

        $this->assertSame(1.0, $result['score']);
        $this->assertSame(1.0, $result['maximum']);
    }

    public function test_snbt_profile_can_apply_a_negative_score_for_wrong_answers(): void
    {
        $profile = new ScoringProfile(['rules' => ['correct' => 4, 'wrong' => -1, 'empty' => 0]]);

        $result = app(AttemptScoringService::class)->scoreQuestion($this->singleChoiceQuestion(), 1, $profile);

        $this->assertSame(-1.0, $result['score']);
        $this->assertSame(4.0, $result['maximum']);
    }

    public function test_cpns_tkp_uses_the_selected_option_weight(): void
    {
        $question = new Question(['type' => 'single_choice']);
        $question->setRelation('options', new Collection([
            (new QuestionOption(['score_weight' => 1]))->setAttribute('id', 10),
            (new QuestionOption(['score_weight' => 5]))->setAttribute('id', 11),
        ]));
        $profile = new ScoringProfile(['rules' => ['type' => 'weighted']]);

        $result = app(AttemptScoringService::class)->scoreQuestion($question, 11, $profile);

        $this->assertSame(5.0, $result['score']);
        $this->assertSame(5.0, $result['maximum']);
    }

    public function test_true_false_scoring_normalises_boolean_answers(): void
    {
        $question = new Question(['type' => 'true_false']);
        $question->setRelation('options', new Collection([
            (new QuestionOption(['is_correct' => true]))->setAttribute('id', 10),
            (new QuestionOption(['is_correct' => false]))->setAttribute('id', 11),
        ]));

        $service = app(AttemptScoringService::class);

        $this->assertSame(1.0, $service->scoreQuestion($question, [
            10 => 'benar',
            11 => 'salah',
        ])['score']);
        $this->assertSame(0.0, $service->scoreQuestion($question, [
            10 => 'salah',
            11 => 'benar',
        ])['score']);
    }

    public function test_result_mode_defaults_to_average_and_supports_total_points(): void
    {
        $service = app(AttemptScoringService::class);

        $this->assertSame('average', $service->resultMode(null));
        $this->assertSame('total', $service->resultMode(
            new ScoringProfile(['rules' => ['result_mode' => 'total']])
        ));
        $this->assertSame('total', $service->resultMode(
            new ScoringProfile(['rules' => ['type' => 'weighted']])
        ));
    }

    private function singleChoiceQuestion(): Question
    {
        $question = new Question(['type' => 'single_choice']);
        $question->setRelation('options', new Collection([
            (new QuestionOption(['is_correct' => false]))->setAttribute('id', 1),
            (new QuestionOption(['is_correct' => true]))->setAttribute('id', 2),
        ]));

        return $question;
    }
}
