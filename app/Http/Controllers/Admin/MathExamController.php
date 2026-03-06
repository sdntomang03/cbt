<?php

namespace App\Http\Controllers\Admin;

use App\Exports\MathExamRecapExport;
use App\Http\Controllers\Controller;
use App\Models\MathExam;
use App\Models\MathExamQuestion;
use App\Models\MathExamUser;
use App\Models\School;
use App\Models\User; // Pastikan model School dipanggil
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class MathExamController extends Controller
{
    public function index()
    {
        // Gunakan withCount('examUsers') untuk menghitung jumlah siswa yang ikut ujian ini
        $exams = MathExam::withCount('examUsers')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.math.index', compact('exams'));
    }

    public function create()
    {
        // Ambil data siswa beserta relasi sekolahnya
        $students = User::role('siswa')->with('school')->orderBy('name')->get();

        // Ambil data sekolah untuk dropdown filter
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
            'digits' => 'required|array',
            'total_questions' => 'required|integer|min:1|max:200',
            'duration_minutes' => 'required|integer|min:1',
        ]);

        // 1. BUAT PENGATURAN UJIAN
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

        // ====================================================================
        // HITUNG KUOTA SOAL PER JENIS AGAR ADIL UNTUK SEMUA SISWA
        // ====================================================================
        $baseQuota = floor($totalQuestions / $totalTypes); // Kuota dasar per jenis
        $remainder = $totalQuestions % $totalTypes; // Sisa soal jika tidak habis dibagi

        // Array untuk menyimpan blueprint (cetak biru) urutan jenis soal
        $examBlueprint = [];

        foreach ($selectedTypes as $index => $type) {
            // Jika ada sisa soal, berikan ke jenis-jenis pertama agar genap
            $quota = $baseQuota + ($index < $remainder ? 1 : 0);

            // Masukkan jenis soal ke blueprint sebanyak kuota-nya
            for ($i = 0; $i < $quota; $i++) {
                $examBlueprint[] = $type;
            }
        }
        // ====================================================================

        $allQuestionsData = [];
        $examUsersData = [];

        // 2. LOOPING UNTUK SETIAP SISWA
        foreach ($request->student_ids as $studentId) {

            $examUsersData[] = [
                'math_exam_id' => $exam->id,
                'student_id' => $studentId,
                'status' => 'not_started',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Acak urutan tipe soal khusus untuk siswa ini (agar urutan soal Siswa A & B beda)
            $studentBlueprint = $examBlueprint;
            shuffle($studentBlueprint);

            // Siapkan soal berdasarkan blueprint yang sudah teracak
            foreach ($studentBlueprint as $currentType) {
                $currentDigit = $request->digits[$currentType] ?? 1;

                $min = $currentDigit == 1 ? 1 : pow(10, $currentDigit - 1);
                $max = pow(10, $currentDigit) - 1;

                $n1 = rand($min, $max);
                $n2 = rand($min, $max);
                $correct = 0;
                $operator = '';

                switch ($currentType) {
                    case 'addition':
                        $operator = '+';
                        $correct = $n1 + $n2;
                        break;
                    case 'subtraction':
                        $operator = '-';
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
                        $divMin = $currentDigit == 1 ? 2 : pow(10, $currentDigit - 1);
                        $divMax = pow(10, $currentDigit) - 1;
                        $correct = rand($divMin, $divMax);
                        $n2 = rand(2, ($currentDigit == 1 ? 9 : 15));
                        $n1 = $correct * $n2;
                        break;
                }

                $allQuestionsData[] = [
                    'math_exam_id' => $exam->id,
                    'student_id' => $studentId,
                    'num1' => $n1,
                    'num2' => $n2,
                    'operator' => $operator,
                    'correct_answer' => $correct,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // 3. SIMPAN SEMUA DATA KE DATABASE
        \App\Models\MathExamUser::insert($examUsersData);

        foreach (array_chunk($allQuestionsData, 500) as $chunk) {
            \App\Models\MathExamQuestion::insert($chunk);
        }

        $countStudents = count($request->student_ids);

        return redirect()->back()->with('success', "Ujian '{$exam->title}' berhasil dibuat untuk $countStudents siswa!");
    }

    // Menampilkan Rekap Daftar Nilai Siswa
    public function show($id)
    {
        // PERHATIKAN BAGIAN INI: Tambahkan 'examUsers.questions' di dalam array
        $exam = MathExam::with(['examUsers.student.school', 'questions'])->findOrFail($id);

        // Pisahkan data untuk menghitung statistik (Hanya yang sudah selesai)
        $completedUsers = $exam->examUsers->where('status', 'completed');

        $stats = [
            'total_students' => $exam->examUsers->count(),
            'completed_count' => $completedUsers->count(),
            'average_score' => $completedUsers->count() > 0 ? round($completedUsers->avg('score'), 2) : 0,
            'highest_score' => $completedUsers->count() > 0 ? $completedUsers->max('score') : 0,
            'lowest_score' => $completedUsers->count() > 0 ? $completedUsers->min('score') : 0,
        ];

        return view('admin.math.show', compact('exam', 'stats'));
    }

    public function destroy($id)
    {
        $exam = MathExam::findOrFail($id);

        // Hapus ujian. (Otomatis menghapus data di tabel math_exam_users & math_exam_questions berkat cascade)
        $exam->delete();

        return redirect()->route('admin.math.index')->with('success', 'Ujian beserta seluruh data nilai siswa berhasil dihapus.');
    }

    // Menampilkan Lembar Jawaban Detail per Siswa
    public function showStudentResult($examUserId)
    {
        // Cari data Sesi Ujian Siswa
        $examUser = MathExamUser::with(['exam', 'student.school'])->findOrFail($examUserId);

        // Ambil semua soal yang dikerjakan oleh siswa ini pada ujian ini
        $questions = MathExamQuestion::where('math_exam_id', $examUser->math_exam_id)
            ->where('student_id', $examUser->student_id)
            ->get();

        return view('admin.math.result', compact('examUser', 'questions'));
    }

    // Export Rekap Keseluruhan Nilai Kelas
    public function exportRecap($id)
    {
        $exam = MathExam::with(['examUsers.student.school'])->findOrFail($id);

        $fileName = 'Rekap_Nilai_'.str_replace(' ', '_', $exam->title).'.xlsx';

        return Excel::download(new MathExamRecapExport($id), $fileName);
    }

    // Reset Ujian Siswa
    public function resetStudentExam($examUserId)
    {
        try {
            // Cari data Sesi Ujian Siswa
            $examUser = MathExamUser::findOrFail($examUserId);

            // 1. Kembalikan status menjadi not_started dan kosongkan nilai
            $examUser->update([
                'status' => 'not_started',
                'score' => 0,
                'started_at' => null,
                'finished_at' => null,
            ]);

            return redirect()->back()->with('success', 'Ujian peserta berhasil direset.');

        } catch (\Exception $e) {
            // Jika terjadi Error 500, kita tangkap dan tampilkan pesan aslinya
            return redirect()->back()->with('error', 'Gagal mereset! Pesan Error: '.$e->getMessage());
        }
    }
}
