<?php

namespace App\Imports;

use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class QuestionImport implements ToCollection, WithHeadingRow
{
    protected $examId;

    protected $sectionId;

    protected $userId;

    protected $schoolId;

    protected $selectedIndexes;

    public function __construct($examId, $sectionId, $userId, $schoolId, ?array $selectedIndexes = null)
    {
        $this->examId = $examId;
        $this->sectionId = $sectionId;
        $this->userId = $userId;
        $this->schoolId = $schoolId;
        $this->selectedIndexes = $selectedIndexes;
    }

    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $index => $row) {
                if ($this->selectedIndexes !== null && ! in_array($index, $this->selectedIndexes, true)) {
                    continue;
                }
                // Lewati baris jika narasi soal kosong
                if (! isset($row['narasi_soal']) || empty(trim($row['narasi_soal']))) {
                    continue;
                }

                // Tentukan tipe soal (Default: Pilihan Ganda / single_choice)
                // Jika Opsi A kosong, kita asumsikan ini adalah soal Essay
                $type = empty($row['opsi_a']) ? 'essay' : 'single_choice';

                // 1. Simpan Pertanyaan Utama
                $question = Question::create([
                    'exam_section_id' => $this->sectionId,
                    'user_id' => $this->userId,
                    'school_id' => $this->schoolId,
                    'type' => $type,
                    'content' => $row['narasi_soal'],
                    'explanation' => $row['pembahasan'] ?? $row['explanation'] ?? null,
                    // Abaikan subject_id & level_id saat import agar fleksibel,
                    // atau tambahkan kolom di Excel jika memang diperlukan.
                    'subject_id' => null,
                    'level_id' => null,
                ]);

                // 2. Simpan Opsi Jawaban
                if ($type === 'single_choice') {
                    $kunci = strtoupper(trim($row['kunci_jawaban'])); // Misal: "A", "B"

                    $opsiData = [
                        'A' => $row['opsi_a'],
                        'B' => $row['opsi_b'],
                        'C' => $row['opsi_c'],
                        'D' => $row['opsi_d'],
                        'E' => $row['opsi_e'] ?? null, // Opsional
                    ];

                    foreach ($opsiData as $abjad => $teksOpsi) {
                        if (! empty($teksOpsi)) {
                            QuestionOption::create([
                                'question_id' => $question->id,
                                'school_id' => $this->schoolId,
                                'option_text' => $teksOpsi,
                                'is_correct' => ($abjad === $kunci) ? 1 : 0,
                            ]);
                        }
                    }
                } elseif ($type === 'essay') {
                    // Jika Essay, 'kunci_jawaban' di Excel dianggap sebagai jawaban yang benar
                    if (! empty($row['kunci_jawaban'])) {
                        QuestionOption::create([
                            'question_id' => $question->id,
                            'school_id' => $this->schoolId,
                            'option_text' => $row['kunci_jawaban'],
                            'is_correct' => 1,
                        ]);
                    }
                }
            }
        });
    }
}
