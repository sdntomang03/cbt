<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Exam; // Tambahkan import model Exam
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GradesExport implements FromQuery, ShouldAutoSize, WithEvents, WithHeadings, WithMapping, WithStyles
{
    protected $examId;
    protected $schoolId;
    protected $rowNumber = 0; // State untuk Nomor Urut
    protected $examTitle;     // State untuk Nama Ujian

    public function __construct($examId, $schoolId = null)
    {
        $this->examId = $examId;
        $this->schoolId = $schoolId;

        // Ambil nama ujian berdasarkan ID
        $this->examTitle = Exam::find($examId)?->title ?? 'Ujian';
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
            'No',             // Kolom A
            'Nama Ujian',     // Kolom B
            'Nama Siswa',     // Kolom C
            'Username/NISN',  // Kolom D
            'Sekolah',        // Kolom E
            'Sesi Ujian',     // Kolom F
            'Nilai Akhir',    // Kolom G
            'Status',         // Kolom H
        ];
    }

    public function map($user): array
    {
        $session = $user->examSessions->first();
        $score = $session ? $session->pivot->score : 0;

        $this->rowNumber++; // Increment nomor urut setiap baris

        return [
            $this->rowNumber,
            $this->examTitle,
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

        // 1. Header Style (Sekarang sampai kolom H)
        $sheet->getStyle('A1:H1')->applyFromArray([
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

        // 2. Border untuk Tabel Utama (Sekarang sampai kolom H)
        $sheet->getStyle('A1:H'.$highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF888888'], // Abu-abu
                ],
            ],
        ]);

        // 3. Rata Tengah untuk kolom No (A), NISN (D), Sesi, Nilai, Status (F, G, H)
        $sheet->getStyle('A2:A'.$highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D2:D'.$highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F2:H'.$highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

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

                // Masukkan Label di Kolom F (Sesi Ujian)
                $sheet->setCellValue('F'.$avgRow, 'Rata-rata Nilai:');
                $sheet->setCellValue('F'.$maxRow, 'Nilai Tertinggi:');
                $sheet->setCellValue('F'.$minRow, 'Nilai Terendah:');

                // Masukkan Rumus Excel di Kolom G (Nilai Akhir)
                if ($lastDataRow > 1) { // Pastikan ada data siswa
                    $sheet->setCellValue('G'.$avgRow, "=ROUND(AVERAGE(G2:G{$lastDataRow}), 2)");
                    $sheet->setCellValue('G'.$maxRow, "=MAX(G2:G{$lastDataRow})");
                    $sheet->setCellValue('G'.$minRow, "=MIN(G2:G{$lastDataRow})");
                } else {
                    $sheet->setCellValue('G'.$avgRow, '0');
                    $sheet->setCellValue('G'.$maxRow, '0');
                    $sheet->setCellValue('G'.$minRow, '0');
                }

                // Styling khusus untuk area rekapitulasi (Bergeser ke F dan G)
                $summaryRange = 'F'.$avgRow.':G'.$minRow;

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

                // Rata Kanan untuk Label (F), Rata Tengah untuk Nilai Rumus (G)
                $sheet->getStyle('F'.$avgRow.':F'.$minRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('G'.$avgRow.':G'.$minRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}
