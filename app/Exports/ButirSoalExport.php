<?php

namespace App\Exports;

use App\Models\ExamSession;
use App\Models\Question;
use App\Models\StudentAnswer;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ButirSoalExport implements FromView, ShouldAutoSize
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
        $questions = Question::where('exam_id', $session->exam_id)->get();

        // Ambil daftar siswa yang sudah menyelesaikan (status 'completed')
        $students = $session->students()->wherePivot('status', 'completed')->get();

        $analysisData = [];

        // Hitung statistik untuk setiap soal
        foreach ($questions as $index => $q) {
            $totalSiswaMenjawab = 0;
            $totalBenar = 0;
            $skorTertinggi = 0;
            $skorTerendah = 0; // Anda mungkin perlu mengatur default skor minimum yang logis

            // Ambil semua jawaban untuk soal ini pada sesi ini
            $answers = StudentAnswer::where('exam_session_id', $this->examSessionId)
                ->where('question_id', $q->id)
                ->get();

            foreach ($answers as $ans) {
                // Pastikan siswa yang menjawab statusnya 'completed'
                if ($students->contains('id', $ans->user_id)) {
                    $totalSiswaMenjawab++;
                    // Anda perlu menyesuaikan logika "Benar" berdasarkan tipe soal
                    // Ini contoh sederhana, asumsi nilai maksimal = 1
                    if ($ans->score > 0) {
                        $totalBenar++;
                    }
                }
            }

            // Hitung Tingkat Kesukaran (Proposi Jawaban Benar)
            $tingkatKesukaran = $totalSiswaMenjawab > 0 ? ($totalBenar / $totalSiswaMenjawab) : 0;

            // Kategori Tingkat Kesukaran (Opsional)
            $kategori = 'Sedang';
            if ($tingkatKesukaran < 0.3) {
                $kategori = 'Sukar';
            } elseif ($tingkatKesukaran > 0.7) {
                $kategori = 'Mudah';
            }

            // Menghitung Daya Pembeda sedikit lebih kompleks dan memerlukan pembagian kelompok atas/bawah
            // Untuk contoh ini, kami menempatkan placeholder. Anda perlu mengimplementasikan logika statistiknya.
            $dayaPembeda = 'Implementasi Daya Pembeda';

            $analysisData[] = [
                'nomor' => $index + 1,
                'soal' => strip_tags($q->content), // Hapus tag HTML
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
}
