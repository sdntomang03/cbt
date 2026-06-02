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
use Illuminate\Support\Facades\Storage;
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
            'exam_type_id' => 'required|exists:exam_types,id',
            'duration_minutes' => 'required|integer|min:1',
            'status' => ['required', Rule::enum(ExamStatus::class)],
            'level_id' => 'required|exists:levels,id',
            'subject_id' => 'required|exists:subjects,id',
            'max_tolerances' => 'nullable|integer|min:1',

            // VALIDASI SEO & PUBLIKASI
            'description' => 'nullable|string',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        try {
            $validated['slug'] = Str::slug($request->title).'-'.Str::random(5);
            $validated['teacher_id'] = Auth::id();
            $validated['school_id'] = Auth::user()->school_id;

            // Tangkap Data Boolean (Checkbox)
            $validated['random_question'] = $request->has('random_question');
            $validated['random_answer'] = $request->has('random_answer');
            $validated['show_explanation'] = $request->has('show_explanation');
            $validated['require_token'] = $request->has('require_token');
            $validated['enable_violation'] = $request->has('enable_violation');
            $validated['is_public'] = $request->has('is_public');

            $validated['max_tolerances'] = $request->max_tolerances ?? 3;

            // =========================================================
            // PROSES UPLOAD THUMBNAIL & KONVERSI KE WEBP
            // =========================================================
            if ($request->hasFile('thumbnail')) {
                $file = $request->file('thumbnail');

                // Cek apakah file yang diupload sudah berekstensi WebP
                if ($file->getClientOriginalExtension() === 'webp') {
                    // Jika sudah WebP, simpan secara normal
                    $validated['thumbnail'] = $file->store('exam_thumbnails', 'public');
                } else {
                    // Konversi gambar ke WebP secara Native menggunakan GD PHP
                    $image = imagecreatefromstring(file_get_contents($file->getRealPath()));

                    // Pertahankan background transparan (untuk PNG)
                    imagepalettetotruecolor($image);
                    imagealphablending($image, false);
                    imagesavealpha($image, true);

                    // Generate nama file acak
                    $filename = 'exam_thumbnails/'.Str::random(40).'.webp';

                    // Tangkap output gambar ke dalam memori
                    ob_start();
                    imagewebp($image, null, 80); // Kualitas 80 dari 100
                    $imageContent = ob_get_clean();

                    // Bersihkan memori RAM
                    imagedestroy($image);

                    // Simpan file WebP
                    Storage::disk('public')->put($filename, $imageContent);
                    $validated['thumbnail'] = $filename;
                }
            }

            Exam::create($validated);

            return redirect()->route('admin.exams.index')->with('success', 'Ujian berhasil dibuat!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan ujian: '.$e->getMessage());
        }
    }

    public function update(Request $request, Exam $exam)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'exam_type_id' => 'required|exists:exam_types,id',
            'duration_minutes' => 'required|integer|min:1',
            'status' => ['required', Rule::enum(ExamStatus::class)],
            'level_id' => 'required|exists:levels,id',
            'subject_id' => 'required|exists:subjects,id',
            'max_tolerances' => 'nullable|integer|min:1',

            // VALIDASI SEO & PUBLIKASI
            'description' => 'nullable|string',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        try {
            if ($request->title !== $exam->title) {
                $validated['slug'] = Str::slug($request->title).'-'.Str::random(5);
            }

            $validated['random_question'] = $request->has('random_question');
            $validated['random_answer'] = $request->has('random_answer');
            $validated['show_explanation'] = $request->has('show_explanation');
            $validated['require_token'] = $request->has('require_token');
            $validated['enable_violation'] = $request->has('enable_violation');
            $validated['is_public'] = $request->has('is_public');
            $validated['max_tolerances'] = $request->max_tolerances ?? 3;

            // =========================================================
            // PROSES UPLOAD THUMBNAIL & KONVERSI KE WEBP
            // =========================================================
            if ($request->hasFile('thumbnail')) {
                // 1. Hapus gambar lama jika ada
                if ($exam->thumbnail) {
                    Storage::disk('public')->delete($exam->thumbnail);
                }

                $file = $request->file('thumbnail');

                // 2. Cek apakah file yang diupload sudah berekstensi WebP
                if ($file->getClientOriginalExtension() === 'webp') {
                    // Jika sudah WebP, langsung simpan secara normal
                    $validated['thumbnail'] = $file->store('exam_thumbnails', 'public');
                } else {
                    // 3. Konversi gambar ke WebP secara Native menggunakan GD PHP
                    $image = imagecreatefromstring(file_get_contents($file->getRealPath()));

                    // Pertahankan background transparan (untuk PNG)
                    imagepalettetotruecolor($image);
                    imagealphablending($image, false);
                    imagesavealpha($image, true);

                    // Generate nama file acak
                    $filename = 'exam_thumbnails/'.Str::random(40).'.webp';

                    // Tangkap output gambar ke dalam memori
                    ob_start();
                    imagewebp($image, null, 80); // Angka 80 adalah tingkat Kualitas (0-100)
                    $imageContent = ob_get_clean();

                    // Bersihkan memori RAM
                    imagedestroy($image);

                    // Simpan file WebP menggunakan Storage Laravel
                    Storage::disk('public')->put($filename, $imageContent);
                    $validated['thumbnail'] = $filename;
                }
            }

            $exam->update($validated);

            return redirect()->route('admin.exams.index')->with('success', 'Ujian berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui ujian: '.$e->getMessage());
        }
    }

    public function destroy(Exam $exam)
    {
        if ($exam->teacher_id !== Auth::id()) {
            abort(403);
        }

        // Hapus file gambar thumbnail fisik dari storage jika ada
        if ($exam->thumbnail) {
            Storage::disk('public')->delete($exam->thumbnail);
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

        // 1. Ambil data ujian dari database
        // (Ganti 'Exam' menjadi 'MathExam' jika Anda menggunakan model MathExam)
        $exam = Exam::findOrFail($examId);

        // 2. Bersihkan judul dari spasi dan karakter aneh (misal: "Ujian MTK 1!" menjadi "ujian_mtk_1")
        $safeTitle = Str::slug($exam->title, '_');

        // 3. Rangkai nama file dengan judul ujian
        $fileName = 'Nilai_'.$safeTitle.'_'.now()->format('Y-m-d').'.xlsx';

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

    /**
     * Menampilkan Halaman Form Buat Ujian
     */
    public function create()
    {
        $schoolId = Auth::user()->school_id;
        $examTypes = ExamType::where('school_id', $schoolId)->get();
        $levels = Level::where('school_id', $schoolId)->get();
        $subjects = Subject::where('school_id', $schoolId)->get();

        return view('exams.form', compact('examTypes', 'levels', 'subjects'));
    }

    /**
     * Menampilkan Halaman Form Edit Ujian
     */
    public function edit(Exam $exam)
    {
        abort_if($exam->teacher_id !== Auth::id() && ! Auth::user()->hasRole('admin'), 403);

        $schoolId = Auth::user()->school_id;
        $examTypes = ExamType::where('school_id', $schoolId)->get();
        $levels = Level::where('school_id', $schoolId)->get();
        $subjects = Subject::where('school_id', $schoolId)->get();

        return view('exams.form', compact('exam', 'examTypes', 'levels', 'subjects'));
    }

    public function preview(Request $request)
    {
        // Buat model tiruan, tidak disimpan ke database
        $exam = new Exam;

        // Tangkap inputan dari form test
        $exam->title = $request->input('title', 'Judul Ujian Preview');
        $exam->description = $request->input('description', 'Ini adalah deskripsi mode preview.');
        $exam->content = $request->input('content', '<p>Tidak ada konten yang diisi.</p>');
        $exam->duration_minutes = $request->input('duration_minutes', 60);
        $exam->questions_count = 10; // Angka statis untuk desain
        $exam->thumbnail = null;

        // Kirim ke view preview beserta flag 'isPreview'
        return view('exams.preview', [
            'exam' => $exam,
            'isPreview' => true,
        ]);
    }

    public function livePreview()
    {
        return view('exams.live-preview');
    }
}
