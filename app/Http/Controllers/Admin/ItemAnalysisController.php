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
                        'content' => 'Anda adalah ahli evaluasi pendidikan. Berikan kesimpulan analisis butir soal dalam Bahasa Indonesia, ringkas, objektif, dan berbentuk HTML sederhana dengan rekomendasi revisi.',
                    ],
                    [
                        'role' => 'user',
                        'content' => 'Simpulkan analisis berikut. Jelaskan kualitas tes, soal yang perlu dipertahankan, direvisi, atau dibuang. Jangan mengarang data. Data: '.json_encode([
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

        return response()->json(['content' => $content]);
    }
}
