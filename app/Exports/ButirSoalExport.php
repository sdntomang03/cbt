<?php

namespace App\Exports;

use App\Models\ExamSession;
use App\Models\StudentAnswer;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles; // Tambahkan ini
use PhpOffice\PhpSpreadsheet\Style\Alignment; // Tambahkan ini
use PhpOffice\PhpSpreadsheet\Style\Border; // Tambahkan ini
use PhpOffice\PhpSpreadsheet\Style\Fill; // Tambahkan ini
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet; // Tambahkan ini

class ButirSoalExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $examSessionId;

    public function __construct($examSessionId)
    {
        $this->examSessionId = $examSessionId;
    }

    public function view(): View
    {
        $session = ExamSession::with('exam')->findOrFail($this->examSessionId);

        // Ambil semua soal untuk ujian ini
        $questions = $session->exam->questions;

        // Ambil daftar siswa yang sudah menyelesaikan (status 'completed')
        $students = $session->students()->wherePivot('status', 'completed')->get();

        $analysisData = [];

        // Hitung statistik untuk setiap soal
        foreach ($questions as $index => $q) {
            $totalSiswaMenjawab = 0;
            $totalBenar = 0;

            $answers = StudentAnswer::where('exam_session_id', $this->examSessionId)
                ->where('question_id', $q->id)
                ->get();

            foreach ($answers as $ans) {
                if ($students->contains('id', $ans->user_id)) {
                    $totalSiswaMenjawab++;
                    if ($ans->score > 0) {
                        $totalBenar++;
                    }
                }
            }

            $tingkatKesukaran = $totalSiswaMenjawab > 0 ? ($totalBenar / $totalSiswaMenjawab) : 0;

            $kategori = 'Sedang';
            if ($tingkatKesukaran < 0.3) {
                $kategori = 'Sukar';
            } elseif ($tingkatKesukaran > 0.7) {
                $kategori = 'Mudah';
            }

            $dayaPembeda = 'Analisis Lanjut';

            $analysisData[] = [
                'nomor' => $index + 1,
                // Hilangkan spasi berlebih pada teks soal
                'soal' => trim(preg_replace('/\s+/', ' ', strip_tags($q->content))),
                'tipe' => $q->type,
                'total_menjawab' => $totalSiswaMenjawab,
                'total_benar' => $totalBenar,
                'tingkat_kesukaran' => round($tingkatKesukaran, 2),
                'kategori' => $kategori,
                'daya_pembeda' => $dayaPembeda,
            ];
        }

        return view('exports.butir_soal', [
            'session' => $session,
            'analysisData' => $analysisData,
        ]);
    }

    /**
     * Konfigurasi Styling Excel
     */
    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();

        // 1. Style untuk Judul (Baris 1 dan 2)
        $sheet->getStyle('A1:H2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF4F46E5'], // Warna Indigo
            ],
        ]);

        // 2. Style untuk Header Tabel (Baris 3)
        $sheet->getStyle('A3:H3')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF374151'], // Warna Abu-abu Gelap (Slate)
            ],
        ]);

        // 3. Tambahkan Border untuk semua data tabel (Mulai baris 3 sampai akhir)
        $sheet->getStyle('A3:H'.$highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF9CA3AF'],
                ],
            ],
        ]);

        // 4. Rata Tengah untuk kolom Nomor, Tipe, dan Angka Analisis
        $sheet->getStyle('A4:B'.$highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D4:H'.$highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // 5. Khusus kolom Soal (C), atur Auto Wrap agar teks panjang turun ke bawah (tidak memanjang menembus sel)
        $sheet->getStyle('C4:C'.$highestRow)->getAlignment()->setWrapText(true);
        $sheet->getColumnDimension('C')->setWidth(60); // Set lebar kolom soal lebih lebar
        $sheet->getStyle('C4:C'.$highestRow)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);

        return [];
    }
}
