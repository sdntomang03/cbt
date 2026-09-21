<?php

namespace App\Http\Controllers;

use App\Imports\QuestionImport;
use App\Imports\QuestionPreviewImport;
use App\Exports\QuestionTemplateExport;
use App\Models\Exam;
use App\Models\Level;
use App\Models\Question;
use App\Models\Subject;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Maatwebsite\Excel\Facades\Excel;

class SoalController extends Controller
{
    public function index(Request $request, Exam $exam)
    {
        $sections = $this->ensureExamSections($exam);
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);
        $sectionId = $request->input('section_id');

        $questions = $exam->questions()
            ->with(['options', 'matches', 'subject', 'level', 'section.section'])
            ->when($search, function ($query) use ($search) {
                $query->where('content', 'LIKE', "%{$search}%");
            })
            ->when($sectionId, function ($query) use ($sectionId, $sections) {
                $query->whereIn('exam_section_id', $sections->where('id', $sectionId)->pluck('id'));
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return view('soal.index', compact('exam', 'questions', 'sections'));
    }

    public function create(Request $request, Exam $exam)
    {
        $subjects = Subject::all();
        $levels = Level::all();
        $sections = $this->ensureExamSections($exam);
        $defaultSectionId = $request->input('section_id', $sections->first()->id ?? null);

        return view('soal.create', compact('exam', 'subjects', 'levels', 'sections', 'defaultSectionId'));
    }

    public function store(Request $request, Exam $exam)
    {
        // 1. TAMBAHKAN 'exam_section_id' KE DALAM VALIDASI
        $data = $request->validate([
            'type' => 'required|in:single_choice,complex_choice,tkp,essay,true_false,matching',
            'content' => 'required',
            'exam_section_id' => 'required|integer',
            'explanation' => 'nullable',
            'options' => 'array',
            'subject_id' => 'nullable',
            'level_id' => 'nullable',
        ]);

        try {
            return DB::transaction(function () use ($data, $request, $exam) {
                $section = $exam->sections()->findOrFail($data['exam_section_id']);

                // 3. SIMPAN SOAL MELALUI RELASI SEKSI ($section->questions) BUKAN $exam->questions
                $question = $section->questions()->create([
                    'user_id' => Auth::id(),
                    'type' => $data['type'],
                    'content' => base64_decode($data['content']),
                    'explanation' => base64_decode($data['explanation'] ?? ''),
                    'subject_id' => $data['subject_id'] ?? null,
                    'level_id' => $data['level_id'] ?? null,
                    'school_id' => Auth::user()->school_id ?? Auth::user()->sekolah_id,
                ]);

                // 4. Panggil detail saver (menyimpan opsi/matching)
                $this->saveQuestionDetails($question, $request->options, $data['type']);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Soal dan pilihan jawaban berhasil disimpan!',
                ]);
            });
        } catch (Exception $e) {
            Log::error('Gagal menyimpan soal: '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    public function edit(Exam $exam, Question $soal)
    {
        $this->ensureQuestionBelongsToExam($exam, $soal);
        $soal->load(['options', 'matches']);
        $subjects = Subject::all();
        $levels = Level::all();

        $sections = $this->ensureExamSections($exam);

        return view('soal.edit', compact('exam', 'soal', 'subjects', 'levels', 'sections'));
    }

    public function update(Request $request, Exam $exam, Question $soal)
    {
        $this->ensureQuestionBelongsToExam($exam, $soal);

        // 1. TAMBAHKAN 'exam_section_id' KE DALAM VALIDASI
        $data = $request->validate([
            'type' => 'required|in:single_choice,complex_choice,tkp,essay,true_false,matching',
            'content' => 'required',
            'exam_section_id' => 'required|integer',
            'explanation' => 'nullable',
            'options' => 'array',
            'subject_id' => 'nullable|exists:subjects,id',
            'level_id' => 'nullable|exists:levels,id',
        ]);

        return DB::transaction(function () use ($data, $request, $soal, $exam) {
            $exam->sections()->findOrFail($data['exam_section_id']);

            // 2. UPDATE DATA SOAL TERMASUK 'exam_section_id'
            $soal->update([
                'type' => $data['type'],
                'content' => base64_decode($data['content']),
                'exam_section_id' => $data['exam_section_id'], // <-- PERBARUI RELASI SEKSI JIKA DIUBAH GURU
                'subject_id' => $data['subject_id'],
                'level_id' => $data['level_id'],
                'explanation' => base64_decode($data['explanation'] ?? ''),
            ]);

            // Bersihkan data opsi/matching lama
            $soal->options()->delete();
            $soal->matches()->delete();

            // Simpan opsi/matching baru
            $this->saveQuestionDetails($soal, $request->options, $data['type']);

            return response()->json(['message' => 'Soal berhasil diperbarui!']);
        });
    }

    public function destroy(Exam $exam, Question $soal)
    {
        $this->ensureQuestionBelongsToExam($exam, $soal);
        $soal->delete();

        return response()->json(['message' => 'Soal berhasil dikeluarkan dari ujian']);
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
                            'score_weight' => $type === 'tkp' ? (float) ($item['score_weight'] ?? 0) : 0,
                            'school_id' => $schoolId,
                        ]);
                    }
                }
            } catch (Exception $e) {
                // Jika baris tertentu gagal, lempar exception agar DB::transaction melakukan Rollback
                throw new Exception('Gagal menyimpan detail pada baris ke-'.($index + 1).'. Pesan: '.$e->getMessage());
            }
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new QuestionTemplateExport(), 'template_import_soal.xlsx');
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
            $section = $this->ensureExamSections($exam)->first();
            Excel::import(new QuestionImport($exam, $section->id, Auth::id(), Auth::user()->school_id), $request->file('file_excel'));

            return redirect()->back()->with('success', 'Soal berhasil diimport dari Excel!');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengimport soal. Pastikan format sesuai template. Error: '.$e->getMessage());
        }
    }

    public function previewImportExcel(Request $request, Exam $exam)
        {
            $request->validate([
                'file_excel' => 'required|mimes:xlsx,xls,csv|max:5120',
            ]);

            try {
                $previewImport = new QuestionPreviewImport();
                Excel::import($previewImport, $request->file('file_excel'));
                $rows = $previewImport->rows ?? collect();

                if ($rows->isEmpty()) {
                    return back()->withErrors(['file_excel' => 'Tidak ada baris soal yang valid pada file Excel.']);
                }

                $jsonDataEncoded = base64_encode(json_encode($rows->map(fn ($row) => $row->toArray())->values()));

                return view('soal.preview_excel', [
                    'exam' => $exam,
                    'rows' => $rows,
                    'jsonDataEncoded' => $jsonDataEncoded,
                ]);
            } catch (Exception $e) {
                return back()->withErrors(['file_excel' => 'Gagal membaca file Excel: '.$e->getMessage()]);
            }
        }

    public function storeImportExcel(Request $request, Exam $exam)
        {
            $request->validate([
                'excel_data' => 'required|string',
                'selected_indexes' => 'required|array',
                'selected_indexes.*' => 'integer|min:0',
            ]);

            $rows = json_decode(base64_decode($request->excel_data), true);
            if (! is_array($rows)) {
                return back()->withErrors(['excel_data' => 'Data preview Excel tidak valid. Silakan unggah ulang file.']);
            }

            try {
                $section = $this->ensureExamSections($exam)->first();
                (new QuestionImport(
                    $exam->id,
                    $section->id,
                    Auth::id(),
                    Auth::user()->school_id,
                    array_map('intval', $request->selected_indexes)
                ))->collection(collect($rows));

                return redirect()->route('admin.exams.soal.index', $exam)
                    ->with('success', 'Soal Excel terpilih berhasil diimport.');
            } catch (Exception $e) {
                return back()->withErrors(['error' => 'Gagal menyimpan soal Excel: '.$e->getMessage()]);
            }
        }

    public function showImportJson(Exam $exam)
    {
        return view('soal.import_json', compact('exam'));
    }

    // Method untuk memproses file/teks dan menampilkan layar Preview
    public function previewImportJson(Request $request, Exam $exam)
    {
        // Validasi, pastikan file_json dan text_json boleh kosong (nullable),
        // tapi kita akan cek manual di bawah agar minimal salah satu diisi.
        $request->validate([
            'file_json' => 'nullable|file|mimetypes:application/json,text/plain',
            'text_json' => 'nullable|string',
        ]);

        // Jika dua-duanya kosong
        if (! $request->hasFile('file_json') && empty($request->text_json)) {
            return back()->withErrors(['error' => 'Harap unggah file JSON atau paste teks JSON di kolom yang disediakan.']);
        }

        // Ambil string JSON dari File (jika ada) atau dari Teks Paste (jika file kosong)
        if ($request->hasFile('file_json')) {
            $jsonContent = file_get_contents($request->file('file_json')->getPathname());
        } else {
            $jsonContent = $request->text_json;
        }

        // Bersihkan hasil jika pengguna terlanjur mencopy backticks markdown (```json ... ```)
        $jsonContent = preg_replace('/```(?:json)?\s*(.*?)\s*```/s', '$1', $jsonContent);

        // Decode string menjadi array
        $soals = json_decode($jsonContent, true);

        // Cek validitas format JSON
        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($soals)) {
            return back()->withErrors(['error' => 'Format JSON tidak valid atau rusak. Error: '.json_last_error_msg()]);
        }

        // Antisipasi jika root array JSON berada di dalam property 'data'
        if (isset($soals['data']) && is_array($soals['data'])) {
            $soals = $soals['data'];
        }

        // Enkripsi seluruh data JSON menjadi teks base64 untuk disisipkan ke form Preview (Stateless)
        $jsonDataEncoded = base64_encode(json_encode($soals));

        return view('soal.preview_json', compact('exam', 'soals', 'jsonDataEncoded'));
    }

    // Method untuk menyimpan data yang dicentang (Final)
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
            $section = $this->ensureExamSections($exam)->first();

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
                $question = $section->questions()->create([
                    'user_id' => $userId,
                    'school_id' => $schoolId,
                    'type' => $item['type'],
                    'content' => $kontenSoal,
                    'explanation' => $item['explanation'] ?? $item['pembahasan'] ?? null,
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
                            // --- MODIFIKASI UNTUK JAWABAN ESSAY GANDA ---

                            // Jika format array berupa string biasa: ["Jawaban 1", "Jawaban 2"]
                            if (is_string($opsi)) {
                                $text = $opsi;
                                $isCorrect = 1; // Asumsi variasi string essay adalah jawaban benar
                            } else {
                                // Deteksi key JSON untuk option text (fleksibel: untuk format Object JSON lama)
                                $text = $opsi['option_text'] ?? $opsi['text'] ?? null;
                                $isCorrect = (isset($opsi['is_correct']) && $opsi['is_correct'] == true) ? 1 : 0;
                            }

                            if (! empty($text)) {
                                $question->options()->create([
                                    'school_id' => $schoolId,
                                    'option_text' => $text,
                                    'is_correct' => $isCorrect,
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

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Gagal simpan JSON soal: '.$e->getMessage());

            // Ubah route redirect gagal ini sesuai dengan nama route Anda
            return back()->withErrors(['error' => 'Terjadi kesalahan sistem saat menyimpan: '.$e->getMessage()]);
        }
    }

    public function uploadImage(Request $request)
    {
        $manager = ImageManager::usingDriver(Driver::class);

        // 1. FILE FISIK (Misal: dari Snipping Tool / Upload Manual)
        if ($request->hasFile('image')) {
            $filename = 'soal_images/'.Str::random(20).'.webp';

            $encoded = $manager
                ->decode($request->file('image')->getPathname())
                ->encode(new WebpEncoder(quality: 85));

            Storage::disk('public')->put($filename, (string) $encoded);

            return response()->json(['url' => asset('storage/'.$filename)]);
        }

        // 2. BYPASS URL EKSTERNAL (Dari Copy-Paste Website)
        if ($request->filled('image_url')) {
            try {
                $response = Http::withOptions(['verify' => false])
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36',
                    ])
                    ->get($request->image_url);

                if ($response->successful()) {
                    $body = $response->body();

                    if (empty($body)) {
                        return response()->json(['error' => 'Gambar kosong dari sumber'], 400);
                    }

                    $filename = 'soal_images/'.Str::random(20).'.webp';

                    $encoded = $manager
                        ->decode($body)
                        ->encode(new WebpEncoder(quality: 85));

                    Storage::disk('public')->put($filename, (string) $encoded);

                    return response()->json(['url' => asset('storage/'.$filename)]);
                }

                return response()->json(['error' => 'Web sumber menolak akses (Status: '.$response->status().')'], 400);

            } catch (Exception $e) {
                return response()->json(['error' => 'Server gagal memproses: '.$e->getMessage()], 500);
            }
        }

        return response()->json(['error' => 'Tidak ada gambar yang diproses'], 400);
    }

    public function showBankSoal(Request $request, Exam $exam)
    {
        $search = $request->input('search');
        $subjectId = $request->input('subject_id');
        $levelId = $request->input('level_id');

        // Tangkap parameter per_page (default 20 jika kosong)
        $perPage = $request->input('per_page', 20);

        $subjects = Subject::orderBy('name')->get();
        $levels = Level::orderBy('name')->get();

        $existingQuestionIds = $exam->questions()->pluck('questions.id')->toArray();

        $schoolId = Auth::user()->school_id ?? Auth::user()->sekolah_id;
        $bankQuestions = Question::with(['subject', 'level'])
            ->where('school_id', $schoolId)
            ->whereNotIn('id', $existingQuestionIds)
            ->when($search, function ($query) use ($search) {
                $query->where('content', 'LIKE', "%{$search}%");
            })
            ->when($subjectId, function ($query) use ($subjectId) {
                $query->where('subject_id', $subjectId);
            })
            ->when($levelId, function ($query) use ($levelId) {
                $query->where('level_id', $levelId);
            })
            ->latest()
            ->paginate($perPage) // Gunakan variabel $perPage di sini
            ->withQueryString();

        return view('soal.bank_soal', compact('exam', 'bankQuestions', 'subjects', 'levels'));
    }

    // 2. Memasukkan soal yang dicentang ke dalam Ujian
    public function attachBankSoal(Request $request, Exam $exam)
    {
        $request->validate([
            'question_ids' => 'required|array',
            'question_ids.*' => 'exists:questions,id',
        ]);

        $schoolId = Auth::user()->school_id ?? Auth::user()->sekolah_id;
        $section = $this->ensureExamSections($exam)->first();
        $sourceQuestions = Question::with(['options', 'matches'])
            ->where('school_id', $schoolId)
            ->whereIn('id', $request->question_ids)
            ->get();

        foreach ($sourceQuestions as $source) {
            $copy = $source->replicate();
            $copy->exam_section_id = $section->id;
            $copy->user_id = Auth::id();
            $copy->save();

            foreach ($source->options as $option) {
                $newOption = $option->replicate();
                $newOption->question_id = $copy->id;
                $newOption->save();
            }

            foreach ($source->matches as $match) {
                $newMatch = $match->replicate();
                $newMatch->question_id = $copy->id;
                $newMatch->save();
            }
        }

        return redirect()->route('admin.exams.soal.index', $exam)
            ->with('success', 'Berhasil menambahkan '.$sourceQuestions->count().' soal dari Bank Soal.');
    }

    private function ensureExamSections(Exam $exam)
    {
        if (! $exam->sections()->exists()) {
            $defaultSection = \App\Models\Section::firstOrCreate(
                ['abbreviation' => 'UTAMA'],
                ['name' => 'Sesi Utama']
            );
            $exam->sections()->create([
                'section_id' => $defaultSection->id,
                'scoring_profile_id' => $exam->scoring_profile_id,
                'order' => 1,
            ]);
        }

        return $exam->sections()
            ->with('section')
            ->orderBy('order')
            ->orderBy('id')
            ->get();
    }

    private function ensureQuestionBelongsToExam(Exam $exam, Question $question): void
    {
        abort_unless(
            $exam->sections()->whereKey($question->exam_section_id)->exists(),
            404
        );
    }

    public function chartGenerator()
    {
        // Halaman ini bersifat statis di awal karena datanya di-render dan diedit
        // langsung via Javascript di dalam tampilan Blade.
        return view('soal.chart_generator');
    }

    /**
     * Menampilkan halaman AI Generator Soal
     */
    public function aiGenerator(Exam $exam)
    {
        $subjects = Subject::all();
        $levels = Level::all();

        return view('soal.ai_generator', compact('exam', 'subjects', 'levels'));
    }

    public function aiGenerate(Request $request, Exam $exam)
    {
        $request->validate([
            'prompt' => ['required', 'string', 'max:30000'],
        ]);

        $apiKey = config('services.deepseek.key');
        if (! is_string($apiKey) || trim($apiKey) === '') {
            return response()->json([
                'message' => 'DEEPSEEK_API_KEY belum dikonfigurasi di file .env.',
            ], 503);
        }

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->timeout(90)
                ->post(config('services.deepseek.url'), [
                    'model' => config('services.deepseek.model', 'deepseek-chat'),
                    'temperature' => 0.7,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Anda adalah generator soal pendidikan. Ikuti format JSON yang diminta secara ketat.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $request->string('prompt')->toString(),
                        ],
                    ],
                ]);
        } catch (Exception $e) {
            Log::error('DeepSeek request failed.', [
                'exam_id' => $exam->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Tidak dapat terhubung ke layanan DeepSeek.',
            ], 502);
        }

        if ($response->failed()) {
            Log::error('DeepSeek API returned an error.', [
                'exam_id' => $exam->id,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            return response()->json([
                'message' => $response->json('error.message')
                    ?? 'DeepSeek gagal membuat soal.',
            ], 502);
        }

        $content = $response->json('choices.0.message.content');
        if (! is_string($content) || trim($content) === '') {
            Log::error('DeepSeek response did not contain generated content.', [
                'exam_id' => $exam->id,
            ]);

            return response()->json([
                'message' => 'Respons DeepSeek tidak memiliki hasil soal.',
            ], 502);
        }

        $jsonContent = preg_replace('/```(?:json)?\s*(.*?)\s*```/s', '$1', $content);
        $decodedContent = json_decode(trim($jsonContent), true);
        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decodedContent)) {
            Log::error('DeepSeek returned invalid question JSON.', [
                'exam_id' => $exam->id,
                'json_error' => json_last_error_msg(),
            ]);

            return response()->json([
                'message' => 'DeepSeek mengembalikan format soal yang tidak valid. Silakan coba lagi.',
            ], 502);
        }

        return response()->json([
            'content' => json_encode($decodedContent, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    /**
     * Memproses teks JSON hasil dari AI ke layar Preview yang sudah ada
     */
    public function aiPreview(Request $request, Exam $exam)
    {
        $request->validate([
            'json_data' => 'required|string',
        ]);

        // Bersihkan hasil AI dari markdown ```json ... ``` jika ada
        $jsonContent = preg_replace('/```(?:json)?\s*(.*?)\s*```/s', '$1', $request->json_data);

        $soals = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($soals)) {
            return back()->withErrors(['error' => 'Format JSON dari AI tidak valid atau gagal di-generate. Coba generate ulang. Error: '.json_last_error_msg()]);
        }

        if (isset($soals['data']) && is_array($soals['data'])) {
            $soals = $soals['data'];
        }

        // Enkripsi seluruh data JSON menjadi teks base64 untuk disisipkan ke form (Stateless)
        $jsonDataEncoded = base64_encode(json_encode($soals));

        // Panggil view preview_json yang sudah Anda buat sebelumnya
        return view('soal.preview_json', compact('exam', 'soals', 'jsonDataEncoded'));
    }
}
