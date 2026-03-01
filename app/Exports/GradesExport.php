<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles; // Tambahan untuk Event
use Maatwebsite\Excel\Events\AfterSheet; // Tambahan untuk Event
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GradesExport implements FromQuery, ShouldAutoSize, WithEvents, WithHeadings, WithMapping, WithStyles
{
    protected $examId;

    protected $schoolId;

    public function __construct($examId, $schoolId = null)
    {
        $this->examId = $examId;
        $this->schoolId = $schoolId;
    }

    public function query()
    {
        $query = User::query()
            ->role('siswa')
            ->whereHas('examSessions', function ($q) {
                $q->where('exam_id', $this->examId);
            })
            ->with(['examSessions' => function ($q) {
                $q->where('exam_id', $this->examId);
            }, 'school']);

        if ($this->schoolId) {
            $query->where('school_id', $this->schoolId);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Nama Siswa',
            'Username/NISN',
            'Sekolah',
            'Sesi Ujian',
            'Nilai Akhir',
            'Status',
        ];
    }

    public function map($user): array
    {
        $session = $user->examSessions->first();
        $score = $session ? $session->pivot->score : 0;

        return [
            $user->name,
            $user->username,
            $user->school->name ?? '-',
            $session->title ?? 'Sesi Default',
            $score,
            ($score >= 75) ? 'Lulus' : 'Remedial',
        ];
    }

    /**
     * Styling Tabel Utama
     */
    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();

        // 1. Header Style
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF4F46E5'], // Indigo
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // 2. Border untuk Tabel Utama
        $sheet->getStyle('A1:F'.$highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF888888'], // Abu-abu
                ],
            ],
        ]);

        // 3. Rata Tengah untuk kolom NISN, Sesi, Nilai, Status
        $sheet->getStyle('B2:B'.$highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D2:F'.$highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getRowDimension(1)->setRowHeight(25);

        return [];
    }

    /**
     * Menambahkan Baris Rekapitulasi (Min, Max, Avg) di bawah tabel
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Cari tahu di baris ke berapa data terakhir berada
                $lastDataRow = $sheet->getHighestRow();

                // Tentukan baris untuk rekapitulasi
                $avgRow = $lastDataRow + 1;
                $maxRow = $lastDataRow + 2;
                $minRow = $lastDataRow + 3;

                // Masukkan Label di Kolom D (Sesi Ujian)
                $sheet->setCellValue('D'.$avgRow, 'Rata-rata Nilai:');
                $sheet->setCellValue('D'.$maxRow, 'Nilai Tertinggi:');
                $sheet->setCellValue('D'.$minRow, 'Nilai Terendah:');

                // Masukkan Rumus Excel di Kolom E (Nilai Akhir)
                if ($lastDataRow > 1) { // Pastikan ada data siswa
                    $sheet->setCellValue('E'.$avgRow, "=ROUND(AVERAGE(E2:E{$lastDataRow}), 2)");
                    $sheet->setCellValue('E'.$maxRow, "=MAX(E2:E{$lastDataRow})");
                    $sheet->setCellValue('E'.$minRow, "=MIN(E2:E{$lastDataRow})");
                } else {
                    $sheet->setCellValue('E'.$avgRow, '0');
                    $sheet->setCellValue('E'.$maxRow, '0');
                    $sheet->setCellValue('E'.$minRow, '0');
                }

                // Styling khusus untuk area rekapitulasi
                $summaryRange = 'D'.$avgRow.':E'.$minRow;

                $sheet->getStyle($summaryRange)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['argb' => 'FF1F2937'], // Warna teks lebih gelap
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF888888'],
                        ],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFF3F4F6'], // Warna latar abu-abu sangat muda
                    ],
                ]);

                // Rata Kanan untuk Label, Rata Tengah untuk Nilai Rumus
                $sheet->getStyle('D'.$avgRow.':D'.$minRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('E'.$avgRow.':E'.$minRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}
