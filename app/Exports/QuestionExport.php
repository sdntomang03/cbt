<?php

namespace App\Exports;

use App\Models\Exam;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use RuntimeException;

class QuestionExport extends DefaultValueBinder implements FromArray, ShouldAutoSize, WithCustomValueBinder, WithStyles
{
    public function __construct(private readonly int $examId) {}

    public function bindValue(Cell $cell, $value): bool
    {
        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function array(): array
    {
        $questions = Exam::findOrFail($this->examId)
            ->questions()
            ->with(['options' => fn ($query) => $query->orderBy('id'), 'matches' => fn ($query) => $query->orderBy('id')])
            ->orderBy('questions.created_at')
            ->orderBy('questions.id')
            ->get();

        $slotCount = max(5, (int) $questions->max(
            fn ($question) => max($question->options->count(), $question->matches->count())
        ));
        if ($slotCount > 26) {
            throw new RuntimeException('Format Excel hanya mendukung maksimal 26 opsi atau pasangan per soal.');
        }

        $letters = array_slice(range('A', 'Z'), 0, $slotCount);
        $headings = ['jenis_soal', 'narasi_soal', 'pembahasan'];
        foreach ($letters as $letter) {
            $headings[] = 'opsi_'.strtolower($letter);
        }
        $headings[] = 'kunci_jawaban';
        foreach ($letters as $letter) {
            $headings[] = 'jawaban_'.strtolower($letter);
        }
        foreach ($letters as $letter) {
            $headings[] = 'bobot_'.strtolower($letter);
        }
        foreach ($letters as $letter) {
            $headings[] = 'pasangan_kiri_'.strtolower($letter);
            $headings[] = 'pasangan_kanan_'.strtolower($letter);
        }

        $rows = [$headings];

        foreach ($questions as $question) {
            $row = array_fill_keys($headings, '');
            $row['jenis_soal'] = $question->type;
            $row['narasi_soal'] = $question->content;
            $row['pembahasan'] = $question->explanation ?? '';

            if ($question->type === 'matching') {
                foreach ($question->matches->values() as $index => $match) {
                    $letter = strtolower($letters[$index]);
                    $row['pasangan_kiri_'.$letter] = $match->premise_text;
                    $row['pasangan_kanan_'.$letter] = $match->target_text;
                }
            } else {
                $correctAnswers = [];
                foreach ($question->options->values() as $index => $option) {
                    $letter = strtolower($letters[$index]);
                    $row['opsi_'.$letter] = $option->option_text;

                    if ($question->type === 'true_false') {
                        $row['jawaban_'.$letter] = $option->is_correct ? 'BENAR' : 'SALAH';
                    } elseif ($question->type === 'tkp') {
                        $row['bobot_'.$letter] = $option->score_weight;
                    } elseif ($option->is_correct) {
                        $correctAnswers[] = strtoupper($letter);
                    }
                }

                $row['kunci_jawaban'] = implode(',', $correctAnswers);
            }

            $rows[] = array_values($row);
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $lastColumn = $sheet->getHighestColumn();
        $sheet->getStyle('A1:'.$lastColumn.'1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF4F46E5'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        $sheet->freezePane('A2');
        $lastRow = max(1, $sheet->getHighestRow());
        $sheet->setAutoFilter('A1:'.$lastColumn.$lastRow);
        $sheet->getColumnDimension('B')->setWidth(60);
        $sheet->getColumnDimension('C')->setWidth(45);

        return [];
    }
}
