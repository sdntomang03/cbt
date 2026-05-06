<?php

namespace App\Http\Controllers;

use App\Enums\ExamStatus;
use App\Exports\GradesExport;
use App\Models\Exam; // Pastikan Enum sudah dibuat sebelumnya
use App\Models\ExamType;
use App\Models\Level;
use App\Models\School;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class ExamController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $schoolId = $user->school_id;

        // Ambil semua Jenis Ujian untuk navigasi tab/sidebar
        $examTypes = ExamType::where('school_id', $schoolId)->get();

        // AMBIL DATA LEVEL DAN SUBJECT UNTUK DROPDOWN MODAL
        // Asumsi: Level dan Subject terikat dengan school_id.
        // Jika tabel level bersifat global (tidak punya school_id), gunakan \App\Models\Level::all();
        $levels = Level::where('school_id', $schoolId)->get();
        $subjects = Subject::where('school_id', $schoolId)->get();

        // Ambil ID tipe yang sedang aktif (default ke tipe pertama jika ada)
        $activeTypeId = $request->get('exam_type_id', $examTypes->first()?->id);

        // Query Ujian berdasarkan tipe yang dipilih
        // Tambahkan 'level' dan 'subject' di dalam with() agar query lebih efisien
        $query = Exam::with(['examType', 'level', 'subject'])
            ->withCount('questions')
            ->where('school_id', $schoolId);

        if ($activeTypeId) {
            $query->where('exam_type_id', $activeTypeId);
        }

        $exams = $query->latest()->paginate(10)->withQueryString();
        $schools = $user->hasRole('admin') ? School::all() : [];

        // Jangan lupa tambahkan 'levels' dan 'subjects' ke dalam compact()
        return view('exams.index', compact('exams', 'examTypes', 'activeTypeId', 'schools', 'levels', 'subjects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'exam_type_id' => 'required|exists:exam_types,id', // Sesuai model
            'duration_minutes' => 'required|integer|min:1',
            'status' => ['required', Rule::enum(ExamStatus::class)],
            'level_id' => 'required|exists:levels,id',
            'subject_id' => 'required|exists:subjects,id',
            'show_explanation' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($request->title).'-'.Str::random(5);
        $validated['teacher_id'] = Auth::id();
        $validated['school_id'] = Auth::user()->school_id;
        $validated['random_question'] = $request->has('random_question');
        $validated['random_answer'] = $request->has('random_answer');
        $validated['show_explanation'] = $request->has('show_explanation');

        Exam::create($validated);

        return redirect()->back()->with('success', 'Ujian berhasil dibuat!');
    }

    public function update(Request $request, Exam $exam)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'exam_type_id' => 'required|exists:exam_types,id', // Sesuai model
            'duration_minutes' => 'required|integer|min:1',
            'status' => ['required', Rule::enum(ExamStatus::class)],
            'level_id' => 'required|exists:levels,id',
            'subject_id' => 'required|exists:subjects,id',
            'show_explanation' => 'boolean',
        ]);

        if ($request->title !== $exam->title) {
            $validated['slug'] = Str::slug($request->title).'-'.Str::random(5);
        }

        $validated['random_question'] = $request->has('random_question');
        $validated['random_answer'] = $request->has('random_answer');
        $validated['show_explanation'] = $request->has('show_explanation');
        $exam->update($validated);

        return redirect()->back()->with('success', 'Ujian diperbarui!');
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

    // Method untuk menyimpan Tipe Ujian Baru
    public function storeType(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        ExamType::create([
            'name' => $request->name,
            'school_id' => Auth::user()->school_id,
        ]);

        return redirect()->back()->with('success', 'Tipe Ujian baru berhasil ditambahkan!');
    }
}
