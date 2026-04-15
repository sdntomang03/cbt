<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Level;
use App\Models\Question;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{
    // 1. TAMPILAN DAFTAR SOAL (INDEX)
    public function index(Exam $exam)
    {
        $questions = $exam->questions()
            ->with(['options', 'matches', 'subject', 'level'])
            ->latest()
            ->get();

        return view('admin.questions.index', compact('exam', 'questions'));
    }

    // 2. TAMPILAN FORM BUAT SOAL (CREATE)
    public function create(Exam $exam)
    {
        $subjects = Subject::all();
        $levels = Level::all();

        return view('admin.questions.create', compact('exam', 'subjects', 'levels'));
    }

    // 3. PROSES SIMPAN SOAL BARU (STORE)
    public function store(Request $request, Exam $exam)
    {
        $data = $request->validate([
            'type' => 'required|in:single_choice,complex_choice,essay,true_false,matching',
            'content' => 'required',
            'options' => 'array',
            'subject_id' => 'nullable|exists:subjects,id',
            'level_id' => 'nullable|exists:levels,id',
        ]);

        DB::transaction(function () use ($data, $request, $exam) {
            $question = $exam->questions()->create([
                'user_id' => Auth::id(),
                'type' => $data['type'],
                'content' => $data['content'],
                'subject_id' => $data['subject_id'],
                'level_id' => $data['level_id'],
            ]);
            $this->saveQuestionDetails($question, $request->options, $data['type']);
        });

        // Redirect kembali ke daftar soal
        return redirect()->route('admin.exams.questions.index', $exam->id)
            ->with('success', 'Soal berhasil ditambahkan!');
    }

    // 4. TAMPILAN FORM EDIT SOAL (EDIT)
    public function edit(Exam $exam, Question $question)
    {
        $subjects = Subject::all();
        $levels = Level::all();

        // Load relasi agar bisa ditampilkan di form
        $question->load(['options', 'matches']);

        return view('admin.questions.edit', compact('exam', 'question', 'subjects', 'levels'));
    }

    // 5. PROSES UPDATE SOAL (UPDATE)
    public function update(Request $request, Exam $exam, Question $question)
    {
        $data = $request->validate([
            'type' => 'required|in:single_choice,complex_choice,essay,true_false,matching',
            'content' => 'required',
            'options' => 'array',
            'subject_id' => 'nullable|exists:subjects,id',
            'level_id' => 'nullable|exists:levels,id',
        ]);

        DB::transaction(function () use ($data, $request, $question) {
            $question->update([
                'type' => $data['type'],
                'content' => $data['content'],
                'subject_id' => $data['subject_id'],
                'level_id' => $data['level_id'],
            ]);

            $question->options()->delete();
            $question->matches()->delete();

            $this->saveQuestionDetails($question, $request->options, $data['type']);
        });

        return redirect()->route('admin.exams.questions.index', $exam->id)
            ->with('success', 'Soal berhasil diperbarui!');
    }

    // 6. PROSES HAPUS SOAL (DESTROY)
    public function destroy(Exam $exam, Question $question)
    {
        $question->delete();

        return redirect()->back()->with('success', 'Soal berhasil dihapus!');
    }

    // HELPER: Simpan detail opsi
    private function saveQuestionDetails($question, $items, $type)
    {
        if (empty($items)) {
            return;
        }

        if ($type === 'matching') {
            $matches = [];
            foreach ($items as $item) {
                if (! empty($item['premise_text']) && ! empty($item['target_text'])) {
                    $matches[] = ['premise_text' => $item['premise_text'], 'target_text' => $item['target_text']];
                }
            }
            if (count($matches) > 0) {
                $question->matches()->createMany($matches);
            }
        } else {
            $options = [];
            foreach ($items as $item) {
                if (! empty($item['option_text'])) {
                    $options[] = [
                        'option_text' => $item['option_text'],
                        'is_correct' => filter_var($item['is_correct'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    ];
                }
            }
            if (count($options) > 0) {
                $question->options()->createMany($options);
            }
        }
    }
}
