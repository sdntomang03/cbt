<?php

namespace App\Http\Controllers;

use App\Enums\ExamStatus;
use App\Exports\GradesExport;
use App\Models\Exam; // Pastikan Enum sudah dibuat sebelumnya
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class ExamController extends Controller
{
    // Menampilkan daftar ujian milik guru yang login
    public function index(Request $request)
    {
        $user = Auth::user();
        $schools = [];

        // 1. Logika pengambilan data Ujian (Exams)
        $query = Exam::query();

        if ($user->hasRole('admin')) {
            // Jika Admin: Bisa melihat semua atau filter berdasarkan school_id
            $schools = School::all(); // Isi variabel $schools agar tidak error di Blade

            if ($request->filled('school_id')) {
                $query->where('school_id', $request->school_id);
            }
        } else {
            // Jika Guru/Operator: Hanya melihat ujian di sekolahnya atau miliknya sendiri
            $query->where('school_id', $user->school_id);
            // Jika ingin lebih spesifik hanya yang dia buat:
            // $query->where('teacher_id', $user->id);
        }

        $exams = $query->latest()->paginate(10)->withQueryString();

        // 2. Kirim $exams DAN $schools ke view
        return view('exams.index', compact('exams', 'schools'));
    }

    // Form Tambah Ujian
    public function create()
    {
        return view('exams.create');
    }

    // Simpan Ujian Baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'duration_minutes' => 'required|integer|min:1',
            'status' => ['required', Rule::enum(ExamStatus::class)], // Validasi Enum
        ]);

        // Auto generate data
        $validated['slug'] = Str::slug($request->title).'-'.Str::random(5);
        $validated['teacher_id'] = Auth::id();
        $validated['random_question'] = $request->has('random_question');
        $validated['random_answer'] = $request->has('random_answer');

        Exam::create($validated);

        return redirect()->route('admin.exams.index')->with('success', 'Ujian berhasil dibuat!');
    }

    // Form Edit Ujian
    public function edit(Exam $exam)
    {
        // Pastikan hanya pemilik yang bisa edit
        if ($exam->teacher_id !== Auth::id()) {
            abort(403);
        }

        return view('exams.edit', compact('exam'));
    }

    // Update Ujian
    public function update(Request $request, Exam $exam)
    {
        if ($exam->teacher_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'duration_minutes' => 'required|integer|min:1',
            'status' => ['required', Rule::enum(ExamStatus::class)],
        ]);

        // Update Slug jika judul berubah
        if ($request->title !== $exam->title) {
            $validated['slug'] = Str::slug($request->title).'-'.Str::random(5);
        }

        $validated['random_question'] = $request->has('random_question');
        $validated['random_answer'] = $request->has('random_answer');

        $exam->update($validated);

        return redirect()->route('admin.exams.index')->with('success', 'Ujian diperbarui!');
    }

    // Hapus Ujian
    public function destroy(Exam $exam)
    {
        if ($exam->teacher_id !== Auth::id()) {
            abort(403);
        }

        $exam->delete();

        return redirect()->route('admin.exams.index')->with('success', 'Ujian dihapus!');
    }

    public function exportGrades(Request $request, $examId)
    {
        $user = auth()->user();
        $schoolIdFilter = null;

        if ($user->hasRole('admin')) {
            // Admin bisa filter sekolah tertentu dari request, atau semua jika kosong
            $schoolIdFilter = $request->get('school_id');
        } else {
            // Guru atau Operator dipaksa hanya sekolah mereka sendiri
            $schoolIdFilter = $user->school_id;
        }

        $fileName = 'Nilai_Exam_'.$examId.'_'.now()->format('Y-m-d').'.xlsx';

        return Excel::download(new GradesExport($examId, $schoolIdFilter), $fileName);
    }
}
