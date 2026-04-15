<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Level;
use App\Models\Question;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SoalController extends Controller
{
    public function index(Exam $exam)
    {
        $questions = $exam->questions()
            ->with(['options', 'matches', 'subject', 'level'])
            ->latest()
            ->get();

        return view('soal.index', compact('exam', 'questions'));
    }

    public function create(Exam $exam)
    {
        $subjects = Subject::all();
        $levels = Level::all();

        return view('soal.create', compact('exam', 'subjects', 'levels'));
    }

    public function store(Request $request, Exam $exam)
    {
        $data = $request->validate([
            'type' => 'required|in:single_choice,complex_choice,essay,true_false,matching',
            'content' => 'required',
            'options' => 'array',
            'subject_id' => 'nullable|exists:subjects,id',
            'level_id' => 'nullable|exists:levels,id',
        ]);

        return DB::transaction(function () use ($data, $request, $exam) {
            $question = $exam->questions()->create([
                'user_id' => Auth::id(),
                'type' => $data['type'],
                'content' => $data['content'],
                'subject_id' => $data['subject_id'],
                'level_id' => $data['level_id'],
                'school_id' => Auth::user()->school_id,
            ]);

            $this->saveQuestionDetails($question, $request->options, $data['type']);

            return response()->json(['message' => 'Soal berhasil disimpan!']);
        });
    }

    public function edit(Exam $exam, Question $soal)
    {
        $soal->load(['options', 'matches']);
        $subjects = Subject::all();
        $levels = Level::all();

        return view('soal.edit', compact('exam', 'soal', 'subjects', 'levels'));
    }

    public function update(Request $request, Exam $exam, Question $soal)
    {
        $data = $request->validate([
            'type' => 'required|in:single_choice,complex_choice,essay,true_false,matching',
            'content' => 'required',
            'options' => 'array',
            'subject_id' => 'nullable|exists:subjects,id',
            'level_id' => 'nullable|exists:levels,id',
        ]);

        return DB::transaction(function () use ($data, $request, $soal) {
            $soal->update([
                'type' => $data['type'],
                'content' => $data['content'],
                'subject_id' => $data['subject_id'],
                'level_id' => $data['level_id'],
            ]);

            $soal->options()->delete();
            $soal->matches()->delete();

            $this->saveQuestionDetails($soal, $request->options, $data['type']);

            return response()->json(['message' => 'Soal berhasil diperbarui!']);
        });
    }

    public function destroy(Exam $exam, Question $soal)
    {
        $soal->delete();

        return response()->json(['message' => 'Soal berhasil dihapus']);
    }

    private function saveQuestionDetails($question, $items, $type)
    {
        if (empty($items)) {
            return;
        }

        if ($type === 'matching') {
            $matches = [];
            foreach ($items as $item) {
                if (! empty($item['premise_text']) && ! empty($item['target_text'])) {
                    $matches[] = [
                        'premise_text' => $item['premise_text'],
                        'target_text' => $item['target_text'],
                        'school_id' => Auth::user()->school_id,
                    ];
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
                        'school_id' => Auth::user()->school_id,
                    ];
                }
            }
            if (count($options) > 0) {
                $question->options()->createMany($options);
            }
        }
    }
}
