<?php

namespace App\Http\Controllers\Admin;

use App\Exports\MathExamRecapExport;
use App\Http\Controllers\Controller;
use App\Models\MathExam;
use App\Models\MathExamQuestion;
use App\Models\MathExamUser;
use App\Models\School;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class MathExamController extends Controller
{
    public function index()
    {
        $exams = MathExam::withCount('examUsers')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.math.index', compact('exams'));
    }

    public function create()
    {
        $students = User::role('siswa')->with('school')->orderBy('name')->get();
        $schools = School::orderBy('name')->get();

        return view('admin.math.create', compact('students', 'schools'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:users,id',
            'types' => 'required|array|min:1',
            'digits' => 'required|array', // Menerima array multi-dimensi [type][num1] & [type][num2]
            'total_questions' => 'required|integer|min:1|max:200',
            'duration_minutes' => 'required|integer|min:1',
        ]);

        $exam = MathExam::create([
            'title' => $request->title,
            'types' => $request->types,
            'digits' => $request->digits,
            'total_questions' => $request->total_questions,
            'duration_minutes' => $request->duration_minutes,
        ]);

        $selectedTypes = $request->types;
        $totalQuestions = $request->total_questions;
        $totalTypes = count($selectedTypes);

        // Kuota Soal
        $baseQuota = floor($totalQuestions / $totalTypes);
        $remainder = $totalQuestions % $totalTypes;

        $examBlueprint = [];
        foreach ($selectedTypes as $index => $type) {
            $quota = $baseQuota + ($index < $remainder ? 1 : 0);
            for ($i = 0; $i < $quota; $i++) {
                $examBlueprint[] = $type;
            }
        }

        $allQuestionsData = [];
        $examUsersData = [];

        foreach ($request->student_ids as $studentId) {
            $examUsersData[] = [
                'math_exam_id' => $exam->id,
                'student_id' => $studentId,
                'status' => 'not_started',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $studentBlueprint = $examBlueprint;
            shuffle($studentBlueprint);

            foreach ($studentBlueprint as $currentType) {
                // Generate soal menggunakan Helper canggih kita
                $questionData = $this->generateMathQuestion($currentType, $request->digits);

                $allQuestionsData[] = [
                    'math_exam_id' => $exam->id,
                    'student_id' => $studentId,
                    'num1' => $questionData['num1'],
                    'num2' => $questionData['num2'],
                    'operator' => $questionData['operator'],
                    'correct_answer' => $questionData['correct_answer'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        MathExamUser::insert($examUsersData);
        foreach (array_chunk($allQuestionsData, 500) as $chunk) {
            MathExamQuestion::insert($chunk);
        }

        $countStudents = count($request->student_ids);

        return redirect()->back()->with('success', "Ujian '{$exam->title}' berhasil dibuat untuk $countStudents siswa!");
    }

    // =========================================================================
    // HELPER: LOGIKA GENERATE SOAL SUPER CANGGIH (PASTI & MAKSIMAL)
    // =========================================================================
    private function getDigitRange($settingString)
    {
        $isMax = str_contains($settingString, '_max');
        $digit = (int) str_replace('_max', '', $settingString);
        $digit = max(1, $digit);

        if ($isMax) {
            $min = 1;
            $max = pow(10, $digit) - 1;
        } else {
            $min = $digit == 1 ? 1 : pow(10, $digit - 1);
            $max = pow(10, $digit) - 1;
        }

        return ['min' => $min, 'max' => $max];
    }

    private function generateMathQuestion($type, $digitsConfig)
    {
        $set1 = $this->getDigitRange($digitsConfig[$type]['num1'] ?? '1');
        $set2 = $this->getDigitRange($digitsConfig[$type]['num2'] ?? '1');

        $n1 = rand($set1['min'], $set1['max']);
        $n2 = rand($set2['min'], $set2['max']);

        $correct = 0;
        $operator = '';

        switch ($type) {
            case 'addition':
                $operator = '+';
                $correct = $n1 + $n2;
                break;
            case 'subtraction':
                $operator = '-';
                // Hindari angka negatif untuk SD (Angka Kiri harus lebih besar)
                if ($n1 < $n2) {
                    $temp = $n1;
                    $n1 = $n2;
                    $n2 = $temp;
                }
                $correct = $n1 - $n2;
                break;
            case 'multiplication':
                $operator = 'x';
                $correct = $n1 * $n2;
                break;
            case 'division':
                $operator = ':';

                // Algoritma Cerdas: Menjamin tidak ada sisa bagi (desimal)
                $n2 = max(2, $n2); // Pembagi (Angka Kanan) minimal 2 agar tidak terlalu mudah

                // Cari rentang Hasil Bagi (Correct Answer) agar Angka Kiri sesuai range yang diminta
                $minMultiplier = (int) ceil($set1['min'] / $n2);
                $maxMultiplier = (int) floor($set1['max'] / $n2);

                if ($minMultiplier > $maxMultiplier) {
                    // Fallback aman jika setting guru tidak masuk akal (misal: 1 digit dibagi 3 digit)
                    $correct = rand(1, 9);
                    $n1 = $n2 * $correct;
                } else {
                    $correct = rand($minMultiplier, $maxMultiplier);
                    $n1 = $correct * $n2;
                }
                break;
        }

        return [
            'num1' => $n1,
            'num2' => $n2,
            'operator' => $operator,
            'correct_answer' => $correct,
        ];
    }
    // =========================================================================

    public function show($id)
    {
        $exam = MathExam::with(['examUsers.student.school', 'questions'])->findOrFail($id);
        $completedUsers = $exam->examUsers->where('status', 'completed');

        $stats = [
            'total_students' => $exam->examUsers->count(),
            'completed_count' => $completedUsers->count(),
            'average_score' => $completedUsers->count() > 0 ? round($completedUsers->avg('score'), 2) : 0,
            'highest_score' => $completedUsers->count() > 0 ? $completedUsers->max('score') : 0,
            'lowest_score' => $completedUsers->count() > 0 ? $completedUsers->min('score') : 0,
        ];

        $existingStudentIds = $exam->examUsers->pluck('student_id')->toArray();
        $availableStudents = User::role('siswa')
            ->whereNotIn('id', $existingStudentIds)
            ->with('school')
            ->orderBy('name')
            ->get();

        return view('admin.math.show', compact('exam', 'stats', 'availableStudents'));
    }

    public function destroy($id)
    {
        $exam = MathExam::findOrFail($id);
        $exam->delete();

        return redirect()->route('admin.math.index')->with('success', 'Ujian beserta seluruh data nilai siswa berhasil dihapus.');
    }

    public function showStudentResult($examUserId)
    {
        $examUser = MathExamUser::with(['exam', 'student.school'])->findOrFail($examUserId);
        $questions = MathExamQuestion::where('math_exam_id', $examUser->math_exam_id)
            ->where('student_id', $examUser->student_id)
            ->get();

        return view('admin.math.result', compact('examUser', 'questions'));
    }

    public function exportRecap($id)
    {
        $exam = MathExam::with(['examUsers.student.school'])->findOrFail($id);
        $fileName = 'Rekap_Nilai_'.str_replace(' ', '_', $exam->title).'.xlsx';

        return Excel::download(new MathExamRecapExport($id), $fileName);
    }

    public function resetStudentExam($examUserId)
    {
        try {
            $examUser = MathExamUser::findOrFail($examUserId);
            $examUser->update([
                'status' => 'not_started',
                'score' => 0,
                'started_at' => null,
                'finished_at' => null,
            ]);

            return redirect()->back()->with('success', 'Ujian peserta berhasil direset.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mereset! Pesan Error: '.$e->getMessage());
        }
    }

    public function addStudent(Request $request, $id)
    {
        $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:users,id',
        ]);

        $exam = MathExam::findOrFail($id);

        $selectedTypes = $exam->types;
        $totalQuestions = $exam->total_questions;
        $totalTypes = count($selectedTypes);

        $baseQuota = floor($totalQuestions / $totalTypes);
        $remainder = $totalQuestions % $totalTypes;

        $examBlueprint = [];
        foreach ($selectedTypes as $index => $type) {
            $quota = $baseQuota + ($index < $remainder ? 1 : 0);
            for ($i = 0; $i < $quota; $i++) {
                $examBlueprint[] = $type;
            }
        }

        $allQuestionsData = [];
        $examUsersData = [];

        foreach ($request->student_ids as $studentId) {
            if (MathExamUser::where('math_exam_id', $exam->id)->where('student_id', $studentId)->exists()) {
                continue;
            }

            $examUsersData[] = [
                'math_exam_id' => $exam->id,
                'student_id' => $studentId,
                'status' => 'not_started',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $studentBlueprint = $examBlueprint;
            shuffle($studentBlueprint);

            foreach ($studentBlueprint as $currentType) {
                // Gunakan helper yang sama persis dengan fungsi Store!
                $questionData = $this->generateMathQuestion($currentType, $exam->digits);

                $allQuestionsData[] = [
                    'math_exam_id' => $exam->id,
                    'student_id' => $studentId,
                    'num1' => $questionData['num1'],
                    'num2' => $questionData['num2'],
                    'operator' => $questionData['operator'],
                    'correct_answer' => $questionData['correct_answer'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (! empty($examUsersData)) {
            MathExamUser::insert($examUsersData);
            foreach (array_chunk($allQuestionsData, 500) as $chunk) {
                MathExamQuestion::insert($chunk);
            }
        }

        return redirect()->back()->with('success', 'Berhasil menambahkan '.count($examUsersData).' siswa baru ke dalam ujian.');
    }

    public function printWorksheets($id)
    {
        $exam = MathExam::with([
            'examUsers.student',
            'examUsers.questions' => function ($query) use ($id) {
                $query->where('math_exam_id', $id);
            },
        ])->findOrFail($id);

        $data = [
            'exam' => $exam,
            'examUsers' => $exam->examUsers,
        ];

        // Pastikan disetel ke A4 Landscape
        $pdf = Pdf::loadView('admin.math.pdf_worksheets', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->stream('Lembar_Kerja_'.str_replace(' ', '_', $exam->title).'.pdf');
    }
}
