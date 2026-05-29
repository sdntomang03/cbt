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
        // Tambahkan subject_id dan level_id ke dalam validasi agar tidak dibuang
        $data = $request->validate([
            'type' => 'required|in:single_choice,complex_choice,essay,true_false,matching',
            'content' => 'required',
            'options' => 'array',
            'subject_id' => 'nullable', // Wajib ada agar masuk ke array $data
            'level_id' => 'nullable',   // Wajib ada agar masuk ke array $data
        ]);

        try {
            return DB::transaction(function () use ($data, $request, $exam) {
                // Gunakan operator ?? null untuk keamanan tambahan
                $question = $exam->questions()->create([
                    'user_id' => Auth::id(),
                    'type' => $data['type'],
                    'content' => base64_decode($data['content']),
                    'subject_id' => $data['subject_id'] ?? null,
                    'level_id' => $data['level_id'] ?? null,
                    'school_id' => Auth::user()->school_id ?? Auth::user()->sekolah_id,
                ]);

                // Panggil detail saver
                $this->saveQuestionDetails($question, $request->options, $data['type']);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Soal dan pilihan jawaban berhasil disimpan!',
                ]);
            });
        } catch (\Exception $e) {
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
                            'premise_text' => base64_decode($item['premise_text']), // Decode Base64 di sini jika perlu
                            'target_text' => base64_decode($item['target_text']), // Decode Base64 di sini jika perlu
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
            Excel::import(new QuestionImport($exam, Auth::id(), Auth::user()->school_id), $request->file('file_excel'));

            return redirect()->back()->with('success', 'Soal berhasil diimport dari Excel!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal import soal. Pastikan format sesuai template. Error: '.$e->getMessage());
        }
    }

    public function showImportJson(Exam $exam)
    {
        return view('soal.import_json', compact('exam'));
    }

    // Method untuk memproses file dan menampilkan layar Preview
    public function previewImportJson(Request $request, Exam $exam)
    {
        $request->validate([
            'file_json' => 'required|file|mimetypes:application/json,text/plain',
        ]);

        $jsonContent = file_get_contents($request->file('file_json')->getPathname());
        $soals = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($soals)) {
            return back()->withErrors(['file_json' => 'Format file JSON tidak valid atau rusak.']);
        }

        if (isset($soals['data']) && is_array($soals['data'])) {
            $soals = $soals['data'];
        }

        // Enkripsi seluruh data JSON menjadi teks base64 untuk disisipkan ke form (Stateless)
        $jsonDataEncoded = base64_encode(json_encode($soals));

        return view('soal.preview_json', compact('exam', 'soals', 'jsonDataEncoded')); // Sesuaikan path view-nya jika perlu
    }

    // Method untuk menyimpan data yang dicentang (Final)
    public function storeImportJson(Request $request, Exam $exam)
    {
        $request->validate([
            'json_data' => 'required',
            'selected_indexes' => 'required|array', // Memastikan ada soal yang dicentang
        ]);

        // Kembalikan teks base64 menjadi array data soal
        $soals = json_decode(base64_decode($request->json_data), true);
        $selectedIndexes = $request->selected_indexes;

        DB::beginTransaction();
        try {
            $schoolId = Auth::user()->school_id ?? Auth::user()->sekolah_id;
            $userId = Auth::id();
            $jumlahDisimpan = 0;

            // Looping hanya untuk index soal yang dicentang oleh user
            foreach ($selectedIndexes as $index) {
                if (! isset($soals[$index])) {
                    continue;
                }

                $item = $soals[$index];
                if (empty($item['type']) || empty($item['content'])) {
                    continue;
                }

                $isBase64 = (base64_encode(base64_decode($item['content'], true)) === $item['content']);
                $kontenSoal = $isBase64 ? base64_decode($item['content']) : $item['content'];

                // 1. Simpan Induk Soal
                $question = $exam->questions()->create([
                    'user_id' => $userId,
                    'school_id' => $schoolId,
                    'type' => $item['type'],
                    'content' => $kontenSoal,
                    'subject_id' => $item['subject_id'] ?? null,
                    'level_id' => $item['level_id'] ?? null,
                ]);

                // 2. Simpan Opsi (Pisahkan logika Matching dan PG/Essay)
                if (isset($item['options']) && is_array($item['options'])) {
                    foreach ($item['options'] as $opsi) {

                        if ($item['type'] === 'matching') {
                            // Deteksi key JSON untuk premise dan target (fleksibel)
                            $premise = $opsi['premise_text'] ?? $opsi['premise'] ?? null;
                            $target = $opsi['target_text'] ?? $opsi['target'] ?? null;

                            if (! empty($premise) && ! empty($target)) {
                                $question->matches()->create([
                                    'school_id' => $schoolId,
                                    'premise_text' => $premise,
                                    'target_text' => $target,
                                ]);
                            }
                        } else {
                            // Deteksi key JSON untuk option text (fleksibel)
                            $text = $opsi['option_text'] ?? $opsi['text'] ?? null;

                            if (! empty($text)) {
                                $question->options()->create([
                                    'school_id' => $schoolId,
                                    'option_text' => $text,
                                    'is_correct' => (isset($opsi['is_correct']) && $opsi['is_correct'] == true) ? 1 : 0,
                                ]);
                            }
                        }

                    }
                }
                $jumlahDisimpan++;
            }

            DB::commit();

            return redirect()->route('admin.exams.soal.index', $exam)
                ->with('success', "Berhasil menambahkan $jumlahDisimpan soal ke dalam ujian.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal simpan JSON soal: '.$e->getMessage());

            // Ubah route redirect gagal ini sesuai dengan nama route Bapak
            return back()->withErrors(['error' => 'Terjadi kesalahan sistem saat menyimpan: '.$e->getMessage()]);
        }
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $file = $request->file('image');
        $filename = 'questions/'.uniqid().'.webp';
        $path = storage_path('app/public/'.$filename);

        // 1. Ambil konten file
        $fileContent = file_get_contents($file->getRealPath());

        // 2. Buat resource gambar dari string
        $img = @imagecreatefromstring($fileContent);

        if (! $img) {
            return response()->json(['error' => 'Gagal memproses gambar'], 422);
        }

        // 3. Pastikan folder tujuan ada
        if (! file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        // 4. Konversi & Simpan ke WebP
        // 80 adalah kualitas (0-100)
        if (imagewebp($img, $path, 80)) {
            imagedestroy($img);

            return response()->json(['url' => asset('storage/'.$filename)]);
        }

        return response()->json(['error' => 'Gagal menyimpan gambar ke server'], 500);
    }
}
