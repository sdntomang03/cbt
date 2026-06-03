<?php

namespace App\Services;

use App\Models\StudentAnswer;
use Illuminate\Support\Collection;

class ItemAnalysisService
{
    /**
     * Entry point utama — jalankan semua analisis untuk satu Exam.
     */
    public function analyze(int $examId, int $sessionId): array
    {
        // 1. Ambil semua jawaban siswa dalam sesi ini
        $answers = StudentAnswer::where('exam_session_id', $sessionId)
            ->with(['question.options', 'question.matches'])
            ->get();

        if ($answers->isEmpty()) {
            return ['error' => 'Belum ada jawaban yang masuk pada sesi ini.'];
        }

        // 2. Kelompokkan per siswa → ambil skor dari DB
        $studentScores = $this->buildStudentScoreMatrix($answers);

        if (count($studentScores) < 2) {
            return ['error' => 'Minimal 2 peserta dibutuhkan untuk melakukan analisis statistik.'];
        }

        // 3. Ambil semua soal
        $questions = Exam::find($examId)->questions()
            ->with(['options', 'matches'])
            ->get();

        $totalStudents = count($studentScores);
        $studentIds = array_keys($studentScores);

        // 4. Hitung per butir soal
        $items = [];
        foreach ($questions as $q) {
            $items[] = $this->analyzeItem($q, $studentScores, $studentIds, $totalStudents, $sessionId);
        }

        // 5. Reliabilitas Cronbach Alpha (seluruh soal)
        $alpha = $this->cronbachAlpha($studentScores);

        // 6. Ringkasan
        $summary = $this->buildSummary($items, $alpha, $totalStudents);

        return [
            'items' => $items,
            'alpha' => $alpha,
            'summary' => $summary,
            'total_students' => $totalStudents,
        ];
    }

    // =========================================================================
    // PRIVATE: Matrix Skor Siswa
    // =========================================================================

    /**
     * Kembalikan array: [ user_id => ['total' => float, 'items' => [question_id => skor]] ]
     */
    private function buildStudentScoreMatrix(Collection $answers): array
    {
        $matrix = [];

        foreach ($answers as $ans) {
            $uid = $ans->user_id;
            $q = $ans->question;

            if (! $q) {
                continue;
            }

            // PERBAIKAN FATAL: Langsung ambil skor dari Database,
            // Jangan dihitung ulang agar mendukung nilai Essay atau nilai override manual dari guru.
            $matrix[$uid]['items'][$q->id] = (float) $ans->score;
        }

        // Hitung total skor per siswa (hanya dari butir soal yang dianalisis)
        foreach ($matrix as $uid => &$data) {
            $data['total'] = array_sum($data['items']);
        }

        return $matrix;
    }

    // =========================================================================
    // PRIVATE: Analisis Per Butir
    // =========================================================================

    private function analyzeItem($q, array $studentScores, array $studentIds, int $N, int $sessionId): array
    {
        // Kumpulkan skor item per siswa
        $itemScores = [];
        foreach ($studentIds as $uid) {
            $itemScores[$uid] = $studentScores[$uid]['items'][$q->id] ?? 0.0;
        }

        // --- Tingkat Kesukaran ---
        $tk = $this->difficultyIndex($itemScores, $N);

        // --- Daya Beda ---
        $db = $this->discriminationIndex($itemScores, $studentScores, $studentIds, $N);

        // --- Validitas Butir (Point-Biserial / Korelasi Pearson) ---
        $validity = $this->pointBiserial($itemScores, $studentScores, $studentIds);

        // --- Efektivitas Distraktor (hanya untuk pilihan ganda & kompleks) ---
        $distractors = [];
        if (in_array($q->type, ['single_choice', 'complex_choice'])) {
            $distractors = $this->distractorEffectiveness($q, $sessionId, $studentIds, $N);
        }

        return [
            'id' => $q->id,
            'number' => $q->id,
            'content' => strip_tags($q->content),
            'type' => $q->type,
            'tk' => round($tk, 4),
            'tk_label' => $this->tkLabel($tk),
            'db' => round($db, 4),
            'db_label' => $this->dbLabel($db),
            'validity' => round($validity, 4),
            'valid' => $validity >= 0.3, // Ambang batas r hitung umum (0.3)
            'distractors' => $distractors,
        ];
    }

    // =========================================================================
    // RUMUS STATISTIK
    // =========================================================================

    private function difficultyIndex(array $itemScores, int $N): float
    {
        if ($N === 0) {
            return 0;
        }

        return array_sum($itemScores) / $N;
    }

    private function discriminationIndex(array $itemScores, array $studentScores, array $studentIds, int $N): float
    {
        // Urutkan siswa berdasarkan skor total (Tertinggi ke Terendah)
        usort($studentIds, fn ($a, $b) => $studentScores[$b]['total'] <=> $studentScores[$a]['total']);

        // Ambil 27% kelompok atas & bawah
        $k = max(1, (int) round($N * 0.27));
        $upper = array_slice($studentIds, 0, $k);
        $lower = array_slice($studentIds, -$k);

        $pUpper = array_sum(array_intersect_key($itemScores, array_flip($upper))) / $k;
        $pLower = array_sum(array_intersect_key($itemScores, array_flip($lower))) / $k;

        return $pUpper - $pLower;
    }

    private function pointBiserial(array $itemScores, array $studentScores, array $studentIds): float
    {
        $N = count($studentIds);
        if ($N < 2) {
            return 0;
        }

        $X = []; // skor item
        $Y = []; // skor total

        foreach ($studentIds as $uid) {
            $X[] = $itemScores[$uid] ?? 0;
            $Y[] = $studentScores[$uid]['total'];
        }

        $sumX = array_sum($X);
        $sumY = array_sum($Y);
        $sumXY = 0;
        $sumX2 = 0;
        $sumY2 = 0;

        for ($i = 0; $i < $N; $i++) {
            $sumXY += $X[$i] * $Y[$i];
            $sumX2 += $X[$i] ** 2;
            $sumY2 += $Y[$i] ** 2;
        }

        $num = $N * $sumXY - $sumX * $sumY;
        $denX = $N * $sumX2 - $sumX ** 2;
        $denY = $N * $sumY2 - $sumY ** 2;
        $den = sqrt($denX * $denY);

        return ($den == 0) ? 0 : $num / $den;
    }

    private function cronbachAlpha(array $studentScores): float
    {
        if (count($studentScores) < 2) {
            return 0;
        }

        $questionIds = [];
        foreach ($studentScores as $data) {
            foreach (array_keys($data['items']) as $qid) {
                $questionIds[$qid] = true;
            }
        }
        $questionIds = array_keys($questionIds);
        $k = count($questionIds);

        if ($k < 2) {
            return 0;
        }

        $itemVariances = [];
        foreach ($questionIds as $qid) {
            $scores = array_map(fn ($d) => $d['items'][$qid] ?? 0, $studentScores);
            $itemVariances[] = $this->variance($scores);
        }

        $totalScores = array_map(fn ($d) => $d['total'], $studentScores);
        $totalVariance = $this->variance($totalScores);

        if ($totalVariance == 0) {
            return 0;
        }

        $alpha = ($k / ($k - 1)) * (1 - array_sum($itemVariances) / $totalVariance);

        return round(max(0, min(1, $alpha)), 4);
    }

    private function variance(array $values): float
    {
        $n = count($values);
        if ($n < 2) {
            return 0;
        }

        $mean = array_sum($values) / $n;
        $sq = array_map(fn ($v) => ($v - $mean) ** 2, $values);

        return array_sum($sq) / $n;
    }

    private function distractorEffectiveness($q, int $sessionId, array $studentIds, int $N): array
    {
        $rawAnswers = StudentAnswer::where('exam_session_id', $sessionId)
            ->where('question_id', $q->id)
            ->whereIn('user_id', $studentIds)
            ->pluck('answer')
            ->toArray();

        $result = [];
        foreach ($q->options as $opt) {
            $count = 0;

            // PERBAIKAN: Mendukung jawaban Array (Pilihan Ganda Kompleks)
            foreach ($rawAnswers as $ans) {
                $ansData = $ans;
                if (is_string($ansData)) {
                    $decoded = json_decode($ansData, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $ansData = $decoded;
                    }
                }

                // Jadikan array agar aman dilooping
                $ansArray = is_array($ansData) ? $ansData : [$ansData];
                $ansArrayString = array_map('strval', $ansArray); // Pastikan komparasi sebagai string

                if (in_array((string) $opt->id, $ansArrayString, true)) {
                    $count++;
                }
            }

            $pct = $N > 0 ? round($count / $N * 100, 1) : 0;

            $result[] = [
                'id' => $opt->id,
                'text' => strip_tags($opt->option_text ?? ''),
                'is_correct' => (bool) $opt->is_correct,
                'count' => $count,
                'percent' => $pct,
                // Distraktor efektif jika dipilih >= 5% siswa dan BUKAN kunci jawaban
                'effective' => ! $opt->is_correct && $pct >= 5,
            ];
        }

        return $result;
    }

    // =========================================================================
    // LABEL INTERPRETASI
    // =========================================================================

    private function tkLabel(float $tk): string
    {
        if ($tk > 0.70) {
            return 'Mudah';
        }
        if ($tk >= 0.30) {
            return 'Sedang';
        }

        return 'Sulit';
    }

    private function dbLabel(float $db): string
    {
        if ($db >= 0.40) {
            return 'Sangat Baik';
        }
        if ($db >= 0.30) {
            return 'Baik';
        }
        if ($db >= 0.20) {
            return 'Cukup';
        }
        if ($db > 0.00) {
            return 'Jelek';
        }

        return 'Sangat Jelek (Revisi/Buang)'; // Daya beda negatif sangat fatal
    }

    private function alphaLabel(float $alpha): string
    {
        if ($alpha >= 0.90) {
            return 'Sangat Tinggi';
        }
        if ($alpha >= 0.70) {
            return 'Tinggi';
        }
        if ($alpha >= 0.50) {
            return 'Cukup';
        }

        return 'Rendah';
    }

    private function buildSummary(array $items, float $alpha, int $N): array
    {
        $total = count($items);
        if ($total === 0) {
            return [];
        }

        return [
            'total_items' => $total,
            'total_students' => $N,
            'valid_count' => count(array_filter($items, fn ($i) => $i['valid'])),
            'invalid_count' => count(array_filter($items, fn ($i) => ! $i['valid'])),
            'mudah' => count(array_filter($items, fn ($i) => $i['tk_label'] === 'Mudah')),
            'sedang' => count(array_filter($items, fn ($i) => $i['tk_label'] === 'Sedang')),
            'sulit' => count(array_filter($items, fn ($i) => $i['tk_label'] === 'Sulit')),
            'db_sangat_baik' => count(array_filter($items, fn ($i) => $i['db_label'] === 'Sangat Baik')),
            'db_baik' => count(array_filter($items, fn ($i) => $i['db_label'] === 'Baik')),
            'db_cukup' => count(array_filter($items, fn ($i) => $i['db_label'] === 'Cukup')),
            'db_jelek' => count(array_filter($items, fn ($i) => in_array($i['db_label'], ['Jelek', 'Sangat Jelek (Revisi/Buang)']))),
            'alpha' => $alpha,
            'alpha_label' => $this->alphaLabel($alpha),
        ];
    }
}
