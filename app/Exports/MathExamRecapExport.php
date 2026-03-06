<?php

namespace App\Exports;

use App\Models\MathExam;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class MathExamRecapExport implements FromView, ShouldAutoSize
{
    protected $examId;

    public function __construct($examId)
    {
        $this->examId = $examId;
    }

    public function view(): View
    {
        $exam = MathExam::with(['examUsers.student.school'])->findOrFail($this->examId);

        return view('admin.math.exports.exam_recap', [
            'exam' => $exam,
        ]);
    }
}
