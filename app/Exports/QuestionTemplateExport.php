<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class QuestionTemplateExport implements FromArray, ShouldAutoSize, WithStyles
{
    public function array(): array
    {
        return [[
            'jenis_soal',
            'narasi_soal',
            'pembahasan',
            'opsi_a',
            'opsi_b',
            'opsi_c',
            'opsi_d',
            'opsi_e',
            'kunci_jawaban',
            'jawaban_a',
            'jawaban_b',
            'jawaban_c',
            'jawaban_d',
            'jawaban_e',
            'bobot_a',
            'bobot_b',
            'bobot_c',
            'bobot_d',
            'bobot_e',
            'pasangan_kiri_a',
            'pasangan_kanan_a',
            'pasangan_kiri_b',
            'pasangan_kanan_b',
            'pasangan_kiri_c',
            'pasangan_kanan_c',
            'pasangan_kiri_d',
            'pasangan_kanan_d',
            'pasangan_kiri_e',
            'pasangan_kanan_e',
        ]];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastColumn = $sheet->getHighestColumn();
        $sheet->getStyle('A1:'.$lastColumn.'1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
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
        $sheet->getColumnDimension('B')->setWidth(60);
        $sheet->getColumnDimension('C')->setWidth(45);

        return [];
    }
}
