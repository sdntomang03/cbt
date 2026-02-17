<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Level;
use App\Models\Question;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuestionAjaxController extends Controller
{
    public function index(Exam $exam)
    {
        if (request()->wantsJson()) {
            return response()->json([
                'questions' => $exam->questions()
                    // Load 'options' untuk PG/Essay, Load 'matches' untuk Menjodohkan
                    ->with(['options', 'matches', 'subject', 'level'])
                    ->latest()
                    ->get(),
            ]);
        }

        $subjects = Subject::all();
        $levels = Level::all();

        return view('questions.index-ajax', compact('exam', 'subjects', 'levels'));
    }

    public function store(Request $request, Exam $exam)
    {
        // Validasi input
        $data = $request->validate([
            'type' => 'required|in:single_choice,complex_choice,essay,true_false,matching',
            'content' => 'required',
            'options' => 'array', // Array ini polimorfik (bisa berisi opsi atau pasangan matching)
            'subject_id' => 'nullable|exists:subjects,id',
            'level_id' => 'nullable|exists:levels,id',
        ]);

        return DB::transaction(function () use ($data, $request, $exam) {
            // 1. Buat Soal
            $question = $exam->questions()->create([
                'user_id' => Auth::id(),
                'type' => $data['type'],
                'content' => $data['content'],
                'subject_id' => $data['subject_id'],
                'level_id' => $data['level_id'],
            ]);

            // 2. Simpan Detail Jawaban berdasarkan Tipe
            $this->saveQuestionDetails($question, $request->options, $data['type']);

            return response()->json(['message' => 'Soal berhasil disimpan!']);
        });
    }

    public function update(Request $request, Question $question)
    {
        $data = $request->validate([
            'type' => 'required|in:single_choice,complex_choice,essay,true_false,matching',
            'content' => 'required',
            'options' => 'array',
            'subject_id' => 'nullable|exists:subjects,id',
            'level_id' => 'nullable|exists:levels,id',
        ]);

        return DB::transaction(function () use ($data, $request, $question) {
            // 1. Update Soal Utama
            $question->update([
                'type' => $data['type'],
                'content' => $data['content'],
                'subject_id' => $data['subject_id'],
                'level_id' => $data['level_id'],
            ]);

            // 2. Hapus Data Lama (Bersihkan kedua tabel relasi untuk keamanan)
            $question->options()->delete();
            $question->matches()->delete();

            // 3. Simpan Data Baru
            $this->saveQuestionDetails($question, $request->options, $data['type']);

            return response()->json(['message' => 'Soal berhasil diperbarui!']);
        });
    }

    /**
     * Helper untuk menyimpan opsi atau matching pairs sesuai tipe tabel
     */
    private function saveQuestionDetails($question, $items, $type)
    {
        if (empty($items)) {
            return;
        }

        if ($type === 'matching') {
            // Simpan ke tabel question_matches
            $matches = [];
            foreach ($items as $item) {
                // Pastikan kedua sisi ada isinya (atau minimal salah satu)
                if (! empty($item['premise_text']) && ! empty($item['target_text'])) {
                    $matches[] = [
                        'premise_text' => $item['premise_text'],
                        'target_text' => $item['target_text'],
                    ];
                }
            }

            if (count($matches) > 0) {
                $question->matches()->createMany($matches);
            }

        } else {
            // Simpan ke tabel question_options (PG, Essay, dll)
            $options = [];
            foreach ($items as $item) {
                // Essay atau PG butuh option_text
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

    public function destroy(Question $question)
    {
        $question->delete();

        return response()->json(['message' => 'Soal berhasil dihapus']);
    }
}
