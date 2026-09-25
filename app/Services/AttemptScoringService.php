<?php

namespace App\Services;

use App\Models\DetailNilai;
use App\Models\ExamAttempt;
use App\Models\Question;
use App\Models\ScoringProfile;
use Illuminate\Support\Collection;

/**
 * The single source of truth for CBT scoring.
 *
 * Profiles with {"type":"weighted"} use score_weight (for CPNS/TKP).
 * All other profiles use correct/wrong/empty point rules, with +1/0/0 as
 * the safe default (suitable for TKA and SNBT simulations).
 */
class AttemptScoringService
{
    public function scoreAttempt(ExamAttempt $attempt): array
    {
        $attempt->loadMissing([
            'session.exam.sections.section',
            'session.exam.sections.scoringProfile',
            'answers.question.options',
            'answers.question.matches',
            'answers.question.section.scoringProfile',
        ]);

        $answers = $attempt->answers->keyBy('question_id');
        $questions = $attempt->session->exam->questions()
            ->with(['options', 'matches', 'section.scoringProfile'])
            ->get();

        foreach ($questions as $question) {
            $profile = $question->section?->scoringProfile;
            $answer = $answers->get($question->id);
            $result = $this->scoreQuestion($question, $answer?->answer, $profile);

            if ($answer) {
                $answer->update(['score' => $result['score']]);
            }
        }

        // Use the same section aggregation shown on the result page. This
        // keeps the final score consistent when an exam has multiple sections.
        $sectionResults = $this->sectionResults($attempt);
        $this->persistSectionDetails($attempt, $sectionResults);

        $rawScore = (float) $sectionResults->sum('earned');
        $maximumScore = (float) $sectionResults->sum('maximum');
        $aggregate = $this->aggregateSectionScores($attempt->session->exam, $sectionResults);
        $resultMode = $aggregate['result_mode'];
        $finalScore = $aggregate['final_score'];

        $attempt->update([
            'raw_score' => $rawScore,
            'final_score' => $finalScore,
            'status' => 'completed',
            'finished_at' => now(),
        ]);

        return compact('rawScore', 'maximumScore', 'finalScore', 'resultMode');
    }

    public function sectionResults(ExamAttempt $attempt): Collection
    {
        $attempt->loadMissing([
            'session.exam.sections.section',
            'session.exam.sections.scoringProfile',
            'answers',
        ]);

        $exam = $attempt->session->exam;
        $answers = $attempt->answers->keyBy('question_id');
        $questions = $exam->questions()
            ->with(['section.scoringProfile', 'options', 'matches'])
            ->get();
        $questionsBySection = $questions->groupBy('exam_section_id');

        return $exam->sections()
            ->with('scoringProfile')
            ->orderBy('order')
            ->orderBy('id')
            ->get()
            ->map(function ($section) use ($answers, $exam, $questionsBySection) {
                $sectionQuestions = $questionsBySection->get($section->id, collect());
                $earned = 0.0;
                $maximum = 0.0;
                $profile = $section->scoringProfile;

                foreach ($sectionQuestions as $question) {
                    $result = $this->scoreQuestion(
                        $question,
                        $answers->get($question->id)?->answer,
                        $profile
                    );
                    $earned += $result['score'];
                    $maximum += $result['maximum'];
                }

                return [
                    'id' => $section->id,
                    'name' => $section->section?->name ?? 'Sesi Utama',
                    'question_count' => $sectionQuestions->count(),
                    'earned' => round($earned, 2),
                    'maximum' => round($maximum, 2),
                    'score' => $maximum > 0 ? round(($earned / $maximum) * 100, 2) : 0,
                    'display_score' => $this->resultMode($profile) === 'total'
                        ? round($earned, 2)
                        : ($maximum > 0 ? round(($earned / $maximum) * 100, 2) : 0),
                    'result_mode' => $this->resultMode($profile),
                ];
            });
    }

    public function resultMode(?ScoringProfile $profile): string
    {
        if ($this->isWeightedProfile($profile)) {
            return 'total';
        }

        return ($profile?->rules['result_mode'] ?? 'average') === 'total'
            ? 'total'
            : 'average';
    }

    public function aggregateSectionScores($exam, Collection $sectionResults): array
    {
        $scoring = $exam->scoring === 'total' ? 'total' : 'average';
        $values = $sectionResults->map(fn (array $section): float => (float) (
            $scoring === 'total'
                ? ($section['display_score'] ?? $section['earned'] ?? 0)
                : ($section['score'] ?? 0)
        ));

        return [
            'result_mode' => $scoring,
            'final_score' => round(
                $scoring === 'total'
                    ? $values->sum()
                    : ($values->count() > 0 ? $values->avg() : 0),
                2
            ),
        ];
    }

    public function scoreQuestion(Question $question, mixed $answer, ?ScoringProfile $profile = null): array
    {
        $rules = $profile?->rules ?? [];
        $weighted = $this->isWeightedProfile($profile) || $question->type === 'tkp';
        $answer = $this->normaliseAnswer($answer);
        $empty = $answer === null || $answer === '' || $answer === [];

        if ($weighted && in_array($question->type, ['single_choice', 'tkp'], true)) {
            $maximum = (float) $question->options->max(fn ($option) => (float) ($option->score_weight ?? 0));
            $selected = $question->options->first(fn ($option) => (int) $option->id === (int) $answer);

            if ($maximum > 0) {
                return [
                    'score' => $selected && ! $empty ? (float) ($selected->score_weight ?? 0) : 0.0,
                    'maximum' => $maximum,
                ];
            }
        }

        $correct = (float) ($rules['correct'] ?? 1);
        $wrong = (float) ($rules['wrong'] ?? 0);
        $emptyScore = (float) ($rules['empty'] ?? 0);
        $isCorrect = $this->isCorrect($question, $answer);

        return [
            'score' => $empty ? $emptyScore : ($isCorrect ? $correct : $wrong),
            'maximum' => max($correct, $emptyScore, 0),
        ];
    }

    private function persistSectionDetails(ExamAttempt $attempt, Collection $sectionResults): void
    {
        $attempt->loadMissing(['session.exam', 'answers.question']);
        $exam = $attempt->session->exam;
        $answers = $attempt->answers->keyBy('question_id');

        $sectionQuestions = $exam->sections()
            ->with(['section', 'questions' => fn ($query) => $query->with(['options', 'matches'])])
            ->orderBy('order')
            ->orderBy('id')
            ->get()
            ->mapWithKeys(fn ($section) => [$section->id => $section->questions]);

        foreach ($sectionResults as $sectionResult) {
            $sectionId = $sectionResult['id'];
            $questions = $sectionQuestions->get($sectionId, collect());
            $profile = $exam->sections()->whereKey($sectionId)->first()?->scoringProfile;
            $benar = 0;
            $salah = 0;
            $tidakDijawab = 0;

            foreach ($questions as $question) {
                $answer = $answers->get($question->id)?->answer;
                $normalised = $this->normaliseAnswer($answer);

                if ($answer === null || $answer === '' || $answer === []) {
                    $tidakDijawab++;
                    continue;
                }

                $questionScore = $this->scoreQuestion($question, $normalised, $profile);
                $isWeighted = $this->isWeightedProfile($profile) || $question->type === 'tkp';

                if ($isWeighted) {
                    if ((float) ($questionScore['score'] ?? 0) > 0) {
                        $benar++;
                    } else {
                        $salah++;
                    }

                    continue;
                }

                $isCorrect = $this->isCorrect($question, $normalised);

                if ($isCorrect) {
                    $benar++;
                } else {
                    $salah++;
                }
            }

            $resultMode = $sectionResult['result_mode'] ?? ($profile ? $this->resultMode($profile) : 'average');
            $isPointBased = $this->isPointBased($resultMode, $profile);
            $sectionScore = (float) ($sectionResult['display_score'] ?? $sectionResult['score'] ?? 0);

            DetailNilai::updateOrCreate(
                [
                    'user_id' => $attempt->user_id,
                    'exam_attempt_id' => $attempt->id,
                    'section_id' => $sectionId,
                ],
                [
                    'exam_session_id' => $attempt->exam_session_id,
                    'exam_id' => $exam->id,
                    'section_id' => $sectionId,
                    'scoring_profile_id' => $profile?->id,
                    'result_mode' => $resultMode,
                    'nilai' => round($sectionScore, 2),
                    'benar' => $benar,
                    'salah' => $salah,
                    'tidak_dijawab' => $tidakDijawab,
                    'earned' => round((float) ($sectionResult['earned'] ?? 0), 2),
                    'maximum' => round((float) ($sectionResult['maximum'] ?? 0), 2),
                    'display_score' => round((float) ($sectionResult['display_score'] ?? $sectionResult['score'] ?? 0), 2),
                    'is_point_based' => $isPointBased,
                    'score_label' => $isPointBased ? 'Point' : 'Nilai 100',
                    'metadata' => [
                        'section_name' => $sectionResult['name'] ?? 'Sesi Utama',
                        'question_count' => (int) ($sectionResult['question_count'] ?? 0),
                        'score_display_mode' => $isPointBased ? 'points' : 'percentage',
                    ],
                ]
            );
        }
    }

    private function isPointBased(string $resultMode, ?ScoringProfile $profile = null): bool
    {
        if ($resultMode === 'total') {
            return true;
        }

        if ($this->isWeightedProfile($profile)) {
            return true;
        }

        return false;
    }

    private function isWeightedProfile(?ScoringProfile $profile): bool
    {
        $rules = $profile?->rules ?? [];
        $type = strtolower((string) ($rules['type'] ?? $rules['scoring_type'] ?? ''));

        return $type === 'weighted';
    }

    private function isCorrect(Question $question, mixed $answer): bool
    {
        if ($question->type === 'single_choice') {
            $correctId = optional($question->options->firstWhere('is_correct', true))->id;

            return $correctId !== null && (int) $answer === (int) $correctId;
        }

        if ($question->type === 'complex_choice') {
            $expected = $question->options->where('is_correct', true)->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();
            $actual = collect(is_array($answer) ? $answer : [])->map(fn ($id) => (int) $id)->sort()->values()->all();

            return $expected === $actual;
        }

        if (in_array($question->type, ['true_false', 'true_false_multi'], true)) {
            $answers = is_array($answer) ? $answer : [];
            return $question->options->isNotEmpty() && $question->options->every(function ($option) use ($answers) {
                $value = strtolower(trim((string) ($answers[$option->id] ?? '')));
                if (in_array($value, ['1', 'true'], true)) {
                    $value = 'benar';
                } elseif (in_array($value, ['0', 'false'], true)) {
                    $value = 'salah';
                }

                return $value === ($option->is_correct ? 'benar' : 'salah');
            });
        }

        if ($question->type === 'matching') {
            $answers = is_array($answer) ? $answer : [];
            return $question->matches->isNotEmpty()
                && $question->matches->every(fn ($match) => (int) ($answers[$match->id] ?? 0) === (int) $match->id);
        }

        if ($question->type === 'essay') {
            $actual = $this->normaliseText($answer);
            return $actual !== '' && $question->options->contains(fn ($option) => $this->normaliseText($option->option_text) === $actual);
        }

        return false;
    }

    private function normaliseAnswer(mixed $answer): mixed
    {
        if (! is_string($answer)) {
            return $answer;
        }

        $decoded = json_decode($answer, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $answer;
    }

    private function normaliseText(mixed $value): string
    {
        $value = trim(strip_tags(html_entity_decode((string) $value)));
        $numeric = str_replace(['.', ','], ['', '.'], $value);

        return is_numeric($numeric) ? (string) (float) $numeric : mb_strtolower($value);
    }
}
