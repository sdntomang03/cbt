<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class QuestionPreviewImport implements ToCollection, WithHeadingRow
{
    public Collection $rows;

    public function collection(Collection $rows)
    {
        $this->rows = $rows->filter(function ($row) {
            return ! empty(trim((string) ($row['narasi_soal'] ?? '')));
        })->values();
    }
}
