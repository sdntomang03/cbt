<?php

namespace Database\Seeders;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamSession;
use App\Models\StudentAnswer;
use App\Models\User;
use App\Services\AttemptScoringService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExamParticipantSeeder extends Seeder
{
    public function run(): void
    {
        $exam = Exam::query()
            ->where('title', 'Ujian Simulasi Fitur Lengkap')
            ->first();

        if (! $exam) {
            $this->command->warn('ExamParticipantSeeder dilewati: ujian seed tidak ditemukan.');
            return;
        }

        $session = ExamSession::query()
            ->where('exam_id', $exam->id)
            ->orderBy('id')
            ->first();

        if (! $session) {
            $this->command->warn('ExamParticipantSeeder dilewati: sesi ujian tidak ditemukan.');
            return;
        }

        $students = User::role('siswa')
            ->where('school_id', $session->school_id)
            ->orderBy('id')
            ->limit(10)
            ->get();

        if ($students->isEmpty()) {
            $this->command->warn('ExamParticipantSeeder dilewati: siswa pada sekolah yang sama tidak ditemukan.');
            return;
        }

        $questions = $exam->questions()
            ->with(['options', 'matches'])
            ->orderBy('id')
            ->get();

        if ($questions->isEmpty()) {
            $this->command->warn('ExamParticipantSeeder dilewati: soal ujian tidak ditemukan.');
            return;
        }

        $scoring = app(AttemptScoringService::class);

        DB::transaction(function () use ($students, $session, $questions, $scoring): void {
            foreach ($students as $studentIndex => $student) {
                $attempt = ExamAttempt::updateOrCreate(
                    [
                        'exam_session_id' => $session->id,
                        'user_id' => $student->id,
                    ],
                    [
                        'status' => 'ongoing',
                        'started_at' => now()->subMinutes(45 + ($studentIndex * 2)),
                        'finished_at' => null,
                        'raw_score' => 0,
                        'final_score' => 0,
                        'is_locked' => false,
                        'violation_count' => $studentIndex % 3 === 0 ? 1 : 0,
                        'school_id' => $student->school_id,
                    ]
                );

                $attempt->answers()->delete();

                foreach ($questions as $questionIndex => $question) {
                    StudentAnswer::create([
                        'exam_attempt_id' => $attempt->id,
                        'question_id' => $question->id,
                        'school_id' => $student->school_id,
                        'answer' => $this->answerFor($question, $studentIndex, $questionIndex),
                        'score' => 0,
                        'is_doubtful' => ($studentIndex + $questionIndex) % 5 === 0,
                    ]);
                }

                $scoring->scoreAttempt($attempt->fresh());
            }
        });

        $this->command->info('Seed peserta dan jawaban analisis berhasil dibuat untuk '.$students->count().' siswa.');
    }

    private function answerFor($question, int $studentIndex, int $questionIndex): mixed
    {
        $options = $question->options;

        // Determine a base probability of answering correctly from question level
        $levelName = optional($question->level)->name ? strtolower(optional($question->level)->name) : null;
        $baseProb = 0.6; // default
        if ($levelName) {
            if (str_contains($levelName, 'mudah') || str_contains($levelName, 'easy')) {
                $baseProb = 0.85;
            } elseif (str_contains($levelName, 'sedang') || str_contains($levelName, 'medium')) {
                $baseProb = 0.6;
            } elseif (str_contains($levelName, 'sulit') || str_contains($levelName, 'sukar') || str_contains($levelName, 'hard')) {
                $baseProb = 0.35;
            }
        } else {
            // fallback based on question index: earlier questions slightly easier
            $baseProb = $questionIndex < 3 ? 0.75 : ($questionIndex < 7 ? 0.6 : 0.4);
        }

        // Per-student skill variation (-0.15 .. +0.15)
        $skillOffset = ((($studentIndex % 7) - 3) / 20); // -0.15..+0.15
        $pCorrect = max(0.05, min(0.95, $baseProb + $skillOffset));

        // Random draw
        $rnd = mt_rand() / mt_getrandmax();

        if ($question->type === 'matching') {
            // Either full correct mapping or random mapping
            if ($rnd <= $pCorrect) {
                return $question->matches->mapWithKeys(fn ($m) => [$m->id => $m->id])->all();
            }

            // scramble mapping: map each premise to random target id
            $targets = $question->matches->pluck('id')->shuffle()->values()->all();
            $map = [];
            foreach ($question->matches as $i => $m) {
                $map[$m->id] = $targets[$i] ?? $m->id;
            }

            return $map;
        }

        if ($question->type === 'complex_choice') {
            $correct = $options->where('is_correct', true)->pluck('id')->values()->all();
            if (empty($correct)) return [];

            if ($rnd <= $pCorrect) {
                return $correct;
            }

            // partially correct: drop or add some wrong choices
            $selected = [];
            foreach ($options as $opt) {
                $pickProb = $opt->is_correct ? 0.6 : 0.15; // less likely to pick wrongs
                if (mt_rand() / mt_getrandmax() <= ($pickProb + $skillOffset)) {
                    $selected[] = $opt->id;
                }
            }

            // ensure not empty
            return !empty($selected) ? array_values(array_unique($selected)) : [$options->first()->id];
        }

        if (in_array($question->type, ['true_false', 'true_false_multi'], true)) {
            $out = [];
            foreach ($options as $option) {
                $isCorrect = (bool) $option->is_correct;
                $pick = (mt_rand() / mt_getrandmax()) <= $pCorrect ? ($isCorrect ? 'benar' : 'salah') : ($isCorrect ? 'salah' : 'benar');
                $out[$option->id] = $pick;
            }

            return $out;
        }

        if ($question->type === 'essay') {
            // return correct answer sometimes, otherwise blank or short wrong text
            if ($rnd <= $pCorrect) {
                return $options->first()?->option_text ?? '';
            }

            return 'Jawaban tidak lengkap';
        }

        if ($question->type === 'tkp') {
            // choose option weighted by score_weight; higher weight more likely for high-skill students
            $weights = $options->map(fn($o) => max(0.01, (float) ($o->score_weight ?? 1)))->values()->all();
            $total = array_sum($weights);
            // bias towards higher weights when pCorrect high
            $biasFactor = 1 + ($pCorrect - 0.5);
            $biased = array_map(function($w) use ($biasFactor) { return pow($w, $biasFactor); }, $weights);
            $sumBiased = array_sum($biased);
            $r = mt_rand() / mt_getrandmax();
            $acc = 0.0;
            foreach ($options->values() as $idx => $opt) {
                $acc += ($biased[$idx] / $sumBiased);
                if ($r <= $acc) {
                    return $opt->id;
                }
            }

            return $options->first()?->id;
        }

        // Single choice / true single
        $correct = $options->firstWhere('is_correct', true);
        $incorrect = $options->where('is_correct', false)->pluck('id')->values()->all();

        if ($rnd <= $pCorrect && $correct) {
            return $correct->id;
        }

        // choose random incorrect
        return $incorrect[array_rand($incorrect)] ?? ($correct?->id ?? null);
    }
}
