<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Services\ItemAnalysisService;

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
                'students as completed_count' => fn ($q) => $q->where('exam_session_user.status', 'completed'),
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
}
