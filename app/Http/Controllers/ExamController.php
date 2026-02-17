<?php

namespace App\Http\Controllers;

use App\Enums\ExamStatus;
use App\Models\Exam; // Pastikan Enum sudah dibuat sebelumnya
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ExamController extends Controller
{
    // Menampilkan daftar ujian milik guru yang login
    public function index()
    {
        $exams = Exam::where('teacher_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('exams.index', compact('exams'));
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
}
