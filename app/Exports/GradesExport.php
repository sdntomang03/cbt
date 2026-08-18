<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Exam;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomStartCell; // Tambahan untuk mengatur posisi mulai tabel
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GradesExport implements FromQuery, ShouldAutoSize, WithEvents, WithHeadings, WithMapping, WithStyles, WithCustomStartCell
{
    protected $examId;
    protected $schoolId;
    protected $rowNumber = 0;
    protected $examTitle;

    public function __construct($examId, $schoolId = null)
    {
        $this->examId = $examId;
        $this->schoolId = $schoolId;

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

    /**
     * Memulai tabel data dari cell A4
     * (Baris 1-3 dikosongkan untuk tempat Judul)
     */
    public function startCell(): string
    {
        return 'A4';
    }

    public function headings(): array
    {
        return [
            'No',             // Kolom A
            'Nama Siswa',     // Kolom B
            'Username/NISN',  // Kolom C
            'Sekolah',        // Kolom D
            'Sesi Ujian',     // Kolom E
            'Nilai Akhir',    // Kolom F
            'Status',         // Kolom G
        ];
    }

    public function map($user): array
    {
        $session = $user->examSessions->first();
        $score = $session ? $session->pivot->score : 0;

        $this->rowNumber++;

        return [
            $this->rowNumber,
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

        // 1. Header Style (Mulai di Baris ke-4, sampai Kolom G)
        $sheet->getStyle('A4:G4')->applyFromArray([
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
        $sheet->getStyle('A4:G'.$highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF888888'],
                ],
            ],
        ]);

        // 3. Rata Tengah untuk isi tabel tertentu (Mulai baris 5 karena header di 4)
        if($highestRow > 4) {
            $sheet->getStyle('A5:A'.$highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // No
            $sheet->getStyle('C5:C'.$highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // NISN
            $sheet->getStyle('E5:G'.$highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Sesi, Nilai, Status
        }

        $sheet->getRowDimension(4)->setRowHeight(25);

        return [];
    }

    /**
     * Menambahkan Judul di Atas dan Rekapitulasi di Bawah
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // ============================================
                // A. INJEKSI JUDUL UJIAN DI BARIS 1 DAN 2
                // ============================================
                $sheet->setCellValue('A1', 'REKAPITULASI HASIL UJIAN');
                $sheet->setCellValue('A2', 'Nama Ujian : ' . $this->examTitle);

                // Gabungkan cell A sampai G agar judul berada di tengah layar
                $sheet->mergeCells('A1:G1');
                $sheet->mergeCells('A2:G2');

                // Style Judul
                $sheet->getStyle('A1:A2')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getStyle('A2')->getFont()->setSize(12);

                // ============================================
                // B. REKAPITULASI (MIN, MAX, AVG) DI BAWAH
                // ============================================
                $lastDataRow = $sheet->getHighestRow();

                $avgRow = $lastDataRow + 1;
                $maxRow = $lastDataRow + 2;
                $minRow = $lastDataRow + 3;

                // Masukkan Label di Kolom E (Bawah kolom Sesi Ujian)
                $sheet->setCellValue('E'.$avgRow, 'Rata-rata Nilai:');
                $sheet->setCellValue('E'.$maxRow, 'Nilai Tertinggi:');
                $sheet->setCellValue('E'.$minRow, 'Nilai Terendah:');

                // Masukkan Rumus Excel di Kolom F (Bawah kolom Nilai Akhir)
                if ($lastDataRow > 4) { // Pastikan ada data siswa (baris > 4)
                    $sheet->setCellValue('F'.$avgRow, "=ROUND(AVERAGE(F5:F{$lastDataRow}), 2)");
                    $sheet->setCellValue('F'.$maxRow, "=MAX(F5:F{$lastDataRow})");
                    $sheet->setCellValue('F'.$minRow, "=MIN(F5:F{$lastDataRow})");
                } else {
                    $sheet->setCellValue('F'.$avgRow, '0');
                    $sheet->setCellValue('F'.$maxRow, '0');
                    $sheet->setCellValue('F'.$minRow, '0');
                }

                // Styling area rekapitulasi (E sampai F)
                $summaryRange = 'E'.$avgRow.':F'.$minRow;

                $sheet->getStyle($summaryRange)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['argb' => 'FF1F2937'],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF888888'],
                        ],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFF3F4F6'],
                    ],
                ]);

                // Rata Kanan untuk Label, Rata Tengah untuk Nilai Rumus
                $sheet->getStyle('E'.$avgRow.':E'.$minRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('F'.$avgRow.':F'.$minRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}
