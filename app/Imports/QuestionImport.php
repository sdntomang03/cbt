<?php

namespace App\Imports;

use App\Models\Question;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class QuestionImport implements ToCollection, WithHeadingRow
{
    public function __construct(
        protected int $examId,
        protected int $sectionId,
        protected int $userId,
        protected ?int $schoolId,
        protected ?array $selectedIndexes = null
    ) {}

    public function collection(Collection $rows): void
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $index => $row) {
                if ($this->selectedIndexes !== null && ! in_array($index, $this->selectedIndexes, true)) {
                    continue;
                }

                if (is_array($row)) {
                    $row = collect($row);
                }

                if (! $row instanceof Collection) {
                    throw new InvalidArgumentException('Format baris soal Excel tidak valid pada baris '.($index + 2).'.');
                }

                $content = $this->value($row, 'narasi_soal');
                if ($content === null) {
                    continue;
                }

                $type = $this->questionType($row);
                $question = Question::create([
                    'exam_section_id' => $this->sectionId,
                    'user_id' => $this->userId,
                    'school_id' => $this->schoolId,
                    'type' => $type,
                    'content' => $content,
                    'explanation' => $this->value($row, 'pembahasan') ?? $this->value($row, 'explanation'),
                    'subject_id' => null,
                    'level_id' => null,
                ]);

                if ($type === 'matching') {
                    $this->saveMatches($question, $row, $index);

                    continue;
                }

                $this->saveOptions($question, $row, $type, $index);
            }
        });
    }

    private function questionType(Collection $row): string
    {
        $rawType = $this->value($row, 'jenis_soal') ?? $this->value($row, 'type');
        if ($rawType === null) {
            return $this->value($row, 'opsi_a') === null ? 'essay' : 'single_choice';
        }

        $type = str_replace([' ', '-'], '_', strtolower(trim($rawType)));
        $types = [
            'single_choice' => 'single_choice',
            'pilihan_ganda' => 'single_choice',
            'pilgan' => 'single_choice',
            'complex_choice' => 'complex_choice',
            'pilihan_ganda_kompleks' => 'complex_choice',
            'pg_kompleks' => 'complex_choice',
            'essay' => 'essay',
            'isian_singkat' => 'essay',
            'matching' => 'matching',
            'menjodohkan' => 'matching',
            'true_false' => 'true_false',
            'benar_salah' => 'true_false',
            'tkp' => 'tkp',
            'tkp_berbobot' => 'tkp',
        ];

        if (! isset($types[$type])) {
            throw new InvalidArgumentException('Jenis soal tidak dikenal: '.$rawType);
        }

        return $types[$type];
    }

    private function saveOptions(Question $question, Collection $row, string $type, int $rowIndex): void
    {
        $rawAnswer = $this->value($row, 'kunci_jawaban');
        $answerKeys = $this->answerKeys($rawAnswer);
        if (in_array($type, ['single_choice', 'complex_choice'], true)) {
            $tokens = preg_split('/[;,\\s]+/', strtoupper((string) $rawAnswer), -1, PREG_SPLIT_NO_EMPTY);
            if ($rawAnswer === null || $tokens !== $answerKeys) {
                throw new InvalidArgumentException('Kunci jawaban tidak valid pada baris '.($rowIndex + 2).'. Gunakan huruf opsi A-Z.');
            }
        }

        $count = 0;
        $correctCount = 0;

        foreach (range('A', 'Z') as $letter) {
            $optionText = $this->value($row, 'opsi_'.strtolower($letter));
            if ($optionText === null) {
                continue;
            }

            $isCorrect = match ($type) {
                'true_false' => $this->trueFalseValue($this->value($row, 'jawaban_'.strtolower($letter))),
                'essay' => $answerKeys === [] || in_array($letter, $answerKeys, true),
                default => in_array($letter, $answerKeys, true),
            };
            $weight = $this->value($row, 'bobot_'.strtolower($letter));
            if ($type === 'tkp' && $weight !== null && (! is_numeric($weight) || (float) $weight < 0 || (float) $weight > 999999.99)) {
                throw new InvalidArgumentException('Bobot TKP tidak valid pada baris '.($rowIndex + 2).'.');
            }

            $question->options()->create([
                'school_id' => $this->schoolId,
                'option_text' => $optionText,
                'is_correct' => $isCorrect,
                'score_weight' => $type === 'tkp'
                    ? (float) ($weight ?? 0)
                    : 0,
            ]);
            $count++;
            $correctCount += $isCorrect ? 1 : 0;
        }

        if ($count === 0 && $type === 'essay' && ($answer = $this->value($row, 'kunci_jawaban')) !== null) {
            $question->options()->create([
                'school_id' => $this->schoolId,
                'option_text' => $answer,
                'is_correct' => true,
                'score_weight' => 0,
            ]);
            $count++;
        }

        if ($count === 0) {
            throw new InvalidArgumentException('Soal pada baris '.($rowIndex + 2).' tidak memiliki opsi atau pasangan jawaban.');
        }

        if (in_array($type, ['single_choice', 'complex_choice'], true)) {
            $availableKeys = array_map(
                fn (int $index) => chr(65 + $index),
                array_keys(array_filter(range('A', 'Z'), fn ($letter) => $this->value($row, 'opsi_'.strtolower($letter)) !== null))
            );
            if (array_diff($answerKeys, $availableKeys) !== [] || $correctCount === 0) {
                throw new InvalidArgumentException('Kunci jawaban tidak sesuai dengan opsi pada baris '.($rowIndex + 2).'.');
            }
        }
    }

    private function saveMatches(Question $question, Collection $row, int $rowIndex): void
    {
        $count = 0;
        foreach (range('A', 'Z') as $letter) {
            $suffix = strtolower($letter);
            $premise = $this->value($row, 'pasangan_kiri_'.$suffix);
            $target = $this->value($row, 'pasangan_kanan_'.$suffix);

            if ($premise === null && $target === null) {
                continue;
            }

            if ($premise === null || $target === null) {
                throw new InvalidArgumentException('Pasangan menjodohkan tidak lengkap pada baris '.($rowIndex + 2).'.');
            }

            $question->matches()->create([
                'school_id' => $this->schoolId,
                'premise_text' => $premise,
                'target_text' => $target,
            ]);
            $count++;
        }

        if ($count === 0) {
            throw new InvalidArgumentException('Soal menjodohkan pada baris '.($rowIndex + 2).' tidak memiliki pasangan.');
        }
    }

    private function answerKeys(?string $value): array
    {
        if ($value === null) {
            return [];
        }

        return collect(preg_split('/[;,\\s]+/', strtoupper($value), -1, PREG_SPLIT_NO_EMPTY))
            ->filter(fn (string $key) => in_array($key, range('A', 'Z'), true))
            ->unique()
            ->values()
            ->all();
    }

    private function trueFalseValue(?string $value): bool
    {
        $answer = strtolower(trim((string) $value));

        if (in_array($answer, ['benar', 'true', '1', 'ya'], true)) {
            return true;
        }

        if (in_array($answer, ['salah', 'false', '0', 'tidak'], true)) {
            return false;
        }

        throw new InvalidArgumentException('Jawaban benar/salah harus diisi BENAR atau SALAH.');
    }

    private function value(Collection $row, string $key): ?string
    {
        $value = $row->get($key);
        if ($value === null) {
            return null;
        }

        $value = (string) $value;

        return trim($value) === '' ? null : $this->normalizeLatexMarkup($value);
    }

    private function normalizeLatexMarkup(string $html): string
    {
        if (! str_contains($html, 'ql-formula')) {
            return $html;
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previousErrorMode = libxml_use_internal_errors(true);

        try {
            $loaded = $document->loadHTML(
                '<?xml encoding="UTF-8"><div id="question-import-root">'.$html.'</div>',
                LIBXML_HTML_NODEFDTD | LIBXML_NONET
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrorMode);
        }

        if (! $loaded) {
            throw new InvalidArgumentException('Konten soal dengan rumus LaTeX tidak dapat dibaca.');
        }

        $xpath = new DOMXPath($document);
        $formulaNodes = $xpath->query(
            '//*[@id="question-import-root"]//span[contains(concat(" ", normalize-space(@class), " "), " ql-formula ") and @data-value]'
        );

        if ($formulaNodes === false) {
            throw new InvalidArgumentException('Elemen rumus LaTeX pada konten soal tidak dapat dibaca.');
        }

        foreach ($formulaNodes as $formulaNode) {
            while ($formulaNode->firstChild !== null) {
                $formulaNode->removeChild($formulaNode->firstChild);
            }
        }

        $root = $document->getElementById('question-import-root');
        if ($root === null) {
            throw new InvalidArgumentException('Konten soal LaTeX tidak dapat diproses.');
        }

        $normalized = '';
        foreach ($root->childNodes as $child) {
            $normalized .= $document->saveHTML($child);
        }

        return $normalized;
    }
}
