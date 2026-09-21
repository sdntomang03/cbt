<?php

namespace App\Exports;

use App\Models\ExamSession;
use App\Services\ItemAnalysisService;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ButirSoalExport implements FromView, ShouldAutoSize, WithStyles
{
    public function __construct(protected int $examSessionId) {}

    public function view(): View
    {
        $session = ExamSession::with('exam')->findOrFail($this->examSessionId);
        $result = app(ItemAnalysisService::class)->analyze($session->exam_id, $session->id);

        return view('exports.butir_soal', [
            'session' => $session,
            'analysisData' => collect($result['items'] ?? [])->map(function (array $item, int $index) {
                return [
                    'nomor' => $index + 1,
                    'soal' => trim(preg_replace('/\s+/', ' ', $item['content'])),
                    'tipe' => $item['type'],
                    'total_menjawab' => $item['answered'],
                    'rata_rata' => $item['average_score'],
                    'tingkat_kesukaran' => $item['tk'],
                    'kategori' => $item['tk_label'],
                    'daya_pembeda' => $item['db'],
                    'kategori_daya_beda' => $item['db_label'],
                    'validitas' => $item['validity'],
                    'status' => $item['valid'] ? 'Valid' : 'Revisi',
                ];
            })->all(),
        ]);
    }

    public function styles(Worksheet $sheet): array
    {
        $highestRow = max(4, $sheet->getHighestRow());
        $sheet->getStyle('A1:K2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4F46E5']],
        ]);
        $sheet->getStyle('A3:K3')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF374151']],
        ]);
        $sheet->getStyle('A3:K'.$highestRow)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF9CA3AF']]],
        ]);
        $sheet->getStyle('A4:K'.$highestRow)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
        $sheet->getStyle('A4:A'.$highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D4:K'.$highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B4:B'.$highestRow)->getAlignment()->setWrapText(true);
        $sheet->getColumnDimension('B')->setWidth(60);

        return [];
    }
}
