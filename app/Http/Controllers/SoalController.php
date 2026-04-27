<?php

namespace App\Http\Controllers;

use App\Imports\QuestionImport;
use App\Models\Exam;
use App\Models\Level;
use App\Models\Question;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

        try {
            return DB::transaction(function () use ($data, $request, $exam) {
                $question = $exam->questions()->create([
                    'user_id' => Auth::id(),
                    'type' => $data['type'],
                    'content' => base64_decode($data['content']), // Pastikan ini di-decode jika dikirim via Base64
                    'subject_id' => $data['subject_id'],
                    'level_id' => $data['level_id'],
                    'school_id' => Auth::user()->school_id,
                ]);

                // Panggil detail saver
                $this->saveQuestionDetails($question, $request->options, $data['type']);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Soal dan pilihan jawaban berhasil disimpan!',
                ]);
            });
        } catch (\Exception $e) {
            // Jika gagal, log errornya untuk debugging
            Log::error('Gagal menyimpan soal: '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
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
                'content' => base64_decode($data['content']),
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

        $schoolId = Auth::user()->school_id;

        foreach ($items as $index => $item) {
            try {
                if ($type === 'matching') {
                    // Simpan Matching 1 per 1
                    if (! empty($item['premise_text']) && ! empty($item['target_text'])) {
                        $question->matches()->create([
                            'premise_text' => $item['premise_text'],
                            'target_text' => $item['target_text'],
                            'school_id' => $schoolId,
                        ]);
                    }
                } else {
                    // Simpan Options (Pilihan Ganda/Essay) 1 per 1
                    if (! empty($item['option_text'])) {
                        $question->options()->create([
                            'option_text' => base64_decode($item['option_text']), // Decode Base64 di sini jika perlu
                            'is_correct' => filter_var($item['is_correct'] ?? false, FILTER_VALIDATE_BOOLEAN),
                            'school_id' => $schoolId,
                        ]);
                    }
                }
            } catch (\Exception $e) {
                // Jika baris tertentu gagal, lempar exception agar DB::transaction melakukan Rollback
                throw new \Exception('Gagal menyimpan detail pada baris ke-'.($index + 1).'. Pesan: '.$e->getMessage());
            }
        }
    }

    public function downloadTemplate(): BinaryFileResponse
    {
        $path = public_path('templates/template_import_soal.xlsx');

        // Pastikan Anda menaruh file template.xlsx di folder public/templates/
        if (! file_exists($path)) {
            abort(404, 'Template file not found.');
        }

        return response()->download($path);
    }

    /**
     * Proses Import file Excel
     */
    public function import(Request $request, Exam $exam)
    {
        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls,csv|max:5120', // Maks 5MB
        ]);

        try {
            Excel::import(new QuestionImport($exam->id, Auth::id(), Auth::user()->school_id), $request->file('file_excel'));

            return redirect()->back()->with('success', 'Soal berhasil diimport dari Excel!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengimport soal. Pastikan format sesuai template. Error: '.$e->getMessage());
        }
    }

    public function showImportJson(Exam $exam)
    {
        return view('soal.import_json', compact('exam'));
    }

    // 2. Method untuk memproses file JSON
    public function importJson(Request $request, Exam $exam)
    {
        $request->validate([
            'file_json' => 'required|file|mimetypes:application/json,text/plain',
        ], [
            'file_json.required' => 'Silakan pilih file JSON terlebih dahulu.',
            'file_json.mimetypes' => 'File harus berformat JSON.',
        ]);

        $jsonContent = file_get_contents($request->file('file_json')->getPathname());
        $soals = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($soals)) {
            return back()->withErrors(['file_json' => 'Format file JSON tidak valid atau rusak.']);
        }

        // Buka bungkus array jika JSON menggunakan key "data"
        if (isset($soals['data']) && is_array($soals['data'])) {
            $soals = $soals['data'];
        }

        try {
            DB::transaction(function () use ($soals, $exam) {
                $schoolId = Auth::user()->school_id ?? Auth::user()->sekolah_id;
                $userId = Auth::id();

                foreach ($soals as $item) {
                    // Lewati baris jika tipe atau konten soal kosong
                    if (empty($item['type']) || empty($item['content'])) {
                        continue;
                    }

                    // Cek apakah konten berbentuk base64 atau plain text/HTML
                    // Jika data dari JSON sudah berupa teks biasa, tidak perlu di base64_decode
                    $isBase64 = (base64_encode(base64_decode($item['content'], true)) === $item['content']);
                    $kontenSoal = $isBase64 ? base64_decode($item['content']) : $item['content'];

                    $question = $exam->questions()->create([
                        'user_id' => $userId,
                        'school_id' => $schoolId,
                        'type' => $item['type'],
                        'content' => $kontenSoal,
                        'subject_id' => $item['subject_id'] ?? null,
                        'level_id' => $item['level_id'] ?? null,
                    ]);

                    // Langsung lempar array 'options' ke detail saver milik Bapak
                    if (isset($item['options']) && is_array($item['options'])) {
                        $this->saveQuestionDetails($question, $item['options'], $item['type']);
                    }
                }
            });

            return redirect()->route('admin.exams.soal.index', $exam->id)
                ->with('success', 'Berhasil mengimpor '.count($soals).' soal dari file JSON.');

        } catch (\Exception $e) {
            Log::error('Gagal import JSON soal: '.$e->getMessage());

            return back()->withErrors(['error' => 'Gagal mengimpor soal: '.$e->getMessage()]);
        }
    }
}
