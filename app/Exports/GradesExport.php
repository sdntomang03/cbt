<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Services\AttemptScoringService;
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
    protected $exam;
    protected $sections;
    protected $scoringService;

    public function __construct($examId, $schoolId = null)
    {
        $this->examId = $examId;
        $this->schoolId = $schoolId;

        $this->exam = Exam::with(['sections.section', 'sections.scoringProfile'])->findOrFail($examId);
        $this->examTitle = $this->exam->title;
        $this->sections = $this->exam->sections->sortBy([
            ['order', 'asc'],
            ['id', 'asc'],
        ])->values();
        $this->scoringService = app(AttemptScoringService::class);
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
        $headings = [
            'No',             // Kolom A
            'Nama Siswa',     // Kolom B
            'Username/NISN',  // Kolom C
            'Sekolah',        // Kolom D
            'Sesi Ujian',     // Kolom E
        ];

        if ($this->sections->count() > 1) {
            foreach ($this->sections as $section) {
                $mode = $section->scoringProfile
                    ? $this->scoringService->resultMode($section->scoringProfile)
                    : 'average';
                $headings[] = 'Section '.($section->section?->name ?? 'Sesi Utama')
                    .' ('.($mode === 'total' ? 'Total Poin' : 'Rata-rata').')';
            }
        }

        $mode = $this->overallResultMode();
        $headings[] = 'Nilai Akhir ('.($mode === 'total' ? 'Total Poin' : 'Rata-rata').')';
        $headings[] = 'Status';

        return $headings;
    }

    public function map($user): array
    {
        $session = $user->examSessions->first();
        $score = $session ? $session->pivot->final_score : 0;
        $attempt = $session
            ? ExamAttempt::where('exam_session_id', $session->id)
                ->where('user_id', $user->id)
                ->first()
            : null;

        $this->rowNumber++;

        $row = [
            $this->rowNumber,
            $user->name,
            $user->username,
            $user->school->name ?? '-',
            $session->session_name ?? 'Sesi Default',
        ];

        if ($this->sections->count() > 1) {
            $sectionResults = $attempt
                ? $this->scoringService->sectionResults($attempt)->keyBy('id')
                : collect();

            foreach ($this->sections as $section) {
                $row[] = $sectionResults->get($section->id)['display_score'] ?? 0;
            }
        }

        $mode = $this->overallResultMode();
        $row[] = $score;
        $row[] = $mode === 'total' ? 'Selesai' : (($score >= 75) ? 'Lulus' : 'Remedial');

        return $row;
    }

    protected function overallResultMode(): string
    {
        return $this->exam->scoring === 'total' ? 'total' : 'average';
    }

    /**
     * Styling Tabel Utama
     */
    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($this->headings()));

        // 1. Header Style (Mulai di Baris ke-4)
        $sheet->getStyle('A4:'.$lastColumn.'4')->applyFromArray([
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
        $sheet->getStyle('A4:'.$lastColumn.$highestRow)->applyFromArray([
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
            $sheet->getStyle('E5:'.$lastColumn.$highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
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
                $sheet->setCellValue('A2',  strtoupper($this->examTitle));

                // Gabungkan cell sesuai jumlah kolom export.
                $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($this->headings()));
                $sheet->mergeCells('A1:'.$lastColumn.'1');
                $sheet->mergeCells('A2:'.$lastColumn.'2');

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

                $scoreColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($this->headings()) - 1);
                $labelColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($this->headings()) - 2);
                $sheet->setCellValue($labelColumn.$avgRow, 'Rata-rata Nilai:');
                $sheet->setCellValue($labelColumn.$maxRow, 'Nilai Tertinggi:');
                $sheet->setCellValue($labelColumn.$minRow, 'Nilai Terendah:');

                // Masukkan Rumus Excel di Kolom F (Bawah kolom Nilai Akhir)
                if ($lastDataRow > 4) { // Pastikan ada data siswa (baris > 4)
                    $sheet->setCellValue($scoreColumn.$avgRow, "=ROUND(AVERAGE({$scoreColumn}5:{$scoreColumn}{$lastDataRow}), 2)");
                    $sheet->setCellValue($scoreColumn.$maxRow, "=MAX({$scoreColumn}5:{$scoreColumn}{$lastDataRow})");
                    $sheet->setCellValue($scoreColumn.$minRow, "=MIN({$scoreColumn}5:{$scoreColumn}{$lastDataRow})");
                } else {
                    $sheet->setCellValue($scoreColumn.$avgRow, '0');
                    $sheet->setCellValue($scoreColumn.$maxRow, '0');
                    $sheet->setCellValue($scoreColumn.$minRow, '0');
                }

                // Styling area rekapitulasi
                $summaryRange = $labelColumn.$avgRow.':'.$scoreColumn.$minRow;

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
                $sheet->getStyle($labelColumn.$avgRow.':'.$labelColumn.$minRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle($scoreColumn.$avgRow.':'.$scoreColumn.$minRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}
