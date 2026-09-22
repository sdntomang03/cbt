<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Services\ItemAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ItemAnalysisController extends Controller
{
    public function __construct(protected ItemAnalysisService $service) {}

    /**
     * Pilih sesi ujian yang ingin dianalisis
     */
    public function index(Exam $exam)
    {
        $sessions = ExamSession::where('exam_id', $exam->id)
            ->withCount([
                'students as completed_count' => fn ($q) => $q->where('exam_attempts.status', 'completed'),
            ])
            ->orderByDesc('start_time')
            ->get();

        return view('admin.analysis.index', compact('exam', 'sessions'));
    }

    /**
     * Tampilkan hasil analisis untuk satu sesi
     */
    public function show(Exam $exam, ExamSession $session)
    {
        // Pastikan sesi milik exam ini
        abort_if($session->exam_id !== $exam->id, 404);

        $result = $this->service->analyze($exam->id, $session->id);

        if (isset($result['error'])) {
            return back()->with('error', $result['error']);
        }

        return view('admin.analysis.show', [
            'exam' => $exam,
            'session' => $session,
            'items' => $result['items'],
            'alpha' => $result['alpha'],
            'summary' => $result['summary'],
            'total_students' => $result['total_students'],
        ]);
    }

    /**
     * Export JSON (bisa dikembangkan ke Excel)
     */
    public function export(Exam $exam, ExamSession $session)
    {
        abort_if($session->exam_id !== $exam->id, 404);

        $result = $this->service->analyze($exam->id, $session->id);

        return response()->json($result);
    }

    public function conclusion(Request $request, Exam $exam, ExamSession $session)
    {
        abort_if($session->exam_id !== $exam->id, 404);

        $result = $this->service->analyze($exam->id, $session->id);
        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], 422);
        }

        $key = config('services.deepseek.key');
        if (! is_string($key) || trim($key) === '') {
            return response()->json(['message' => 'DEEPSEEK_API_KEY belum dikonfigurasi.'], 503);
        }

        $items = collect($result['items'])->map(fn ($item) => [
            'no' => $item['number'],
            'isi_soal' => trim(preg_replace('/\s+/', ' ', strip_tags((string) ($item['content'] ?? '')))),
            'tipe_soal' => $item['type'] ?? null,
            'kesukaran' => [$item['tk'], $item['tk_label']],
            'daya_beda' => [$item['db'], $item['db_label']],
            'validitas' => $item['validity'],
            'distraktor_tidak_efektif' => collect($item['distractors'])
                ->where('is_correct', false)
                ->where('effective', false)
                ->count(),
        ])->values()->all();

        try {
            $response = Http::withToken($key)->acceptJson()->timeout(90)->post(config('services.deepseek.url'), [
                'model' => config('services.deepseek.model', 'deepseek-chat'),
                'temperature' => 0.2,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Anda adalah ahli evaluasi pendidikan. Berikan kesimpulan analisis butir soal dalam Bahasa Indonesia. Kembalikan HANYA HTML sederhana tanpa pembuka, tanpa penutup, tanpa ```html, tanpa CSS, tanpa JavaScript, tanpa tag html/head/body/style/script, dan tanpa atribut style. Gunakan hanya tag h3, h4, p, ul, ol, li, strong, dan br. Wajib memuat bagian: Kesimpulan, Butir yang Dipertahankan, Butir yang Direvisi, dan Saran Materi Tindak Lanjut. Saran materi WAJIB disimpulkan dari isi/narasi soal yang memiliki tingkat kesukaran tinggi atau daya pembeda rendah, lalu sebutkan konsep atau kompetensi yang kemungkinan belum dipahami peserta. Jangan hanya menyebut nomor soal. Jika isi soal kosong, terlalu pendek, atau tidak dapat dibaca, barulah gunakan nomor butir sebagai fallback. Jangan menulis kalimat di luar HTML.',
                    ],
                    [
                        'role' => 'user',
                        'content' => 'Simpulkan analisis berikut. Jelaskan kualitas tes, soal yang perlu dipertahankan, direvisi, atau dibuang. Untuk saran materi tindak lanjut, baca dan gunakan isi/narasi setiap soal sebagai sumber utama: kelompokkan soal sulit atau berdaya beda rendah berdasarkan materi, konsep, keterampilan, atau kompetensi yang diuji; jelaskan materi yang perlu diajarkan ulang dan bentuk tindak lanjutnya. Nomor soal hanya boleh digunakan jika isi soal tidak tersedia atau tidak terbaca. Jangan mengarang data. Data: '.json_encode([
                            'jumlah_peserta' => $result['total_students'],
                            'reliabilitas_alpha' => $result['alpha'],
                            'ringkasan' => $result['summary'],
                            'butir' => $items,
                        ], JSON_UNESCAPED_UNICODE),
                    ],
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('DeepSeek item analysis conclusion failed.', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Tidak dapat terhubung ke DeepSeek.'], 502);
        }

        if ($response->failed()) {
            Log::error('DeepSeek item analysis conclusion error.', ['status' => $response->status()]);
            return response()->json(['message' => 'DeepSeek gagal membuat kesimpulan.'], 502);
        }

        $content = $response->json('choices.0.message.content');
        if (! is_string($content) || trim($content) === '') {
            return response()->json(['message' => 'Respons DeepSeek kosong.'], 502);
        }

        return response()->json(['content' => $this->sanitizeConclusion($content)]);
    }

    private function sanitizeConclusion(string $content): string
    {
        $content = trim($content);
        $content = preg_replace('/```(?:html?|markdown)?/i', '', $content);
        $content = str_replace('```', '', $content);
        $content = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $content);
        $content = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $content);
        $content = preg_replace('/[^{}<>]+\{[^{}]*\}/s', '', $content);
        $content = trim($content);

        $firstTag = preg_match('/<(?:h[1-6]|p|ul|ol|div|table)\b[^>]*>/i', $content, $match, PREG_OFFSET_CAPTURE)
            ? $match[0][1]
            : null;
        if ($firstTag !== null && $firstTag > 0) {
            $content = substr($content, $firstTag);
        }

        $content = strip_tags($content, '<p><br><strong><b><em><i><ul><ol><li><h2><h3><h4>');

        $content = preg_replace('/<([a-z0-9]+)(?:\s[^>]*)?>/i', '<$1>', $content);
        if (trim(strip_tags($content)) === '') {
            return '<p>Tidak ada kesimpulan yang dapat ditampilkan.</p>';
        }

        return $content;
    }
}
