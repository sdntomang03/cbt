@php
$isEdit = isset($module);
$titlePage = $isEdit ? 'Ubah Modul Belajar' : 'Tambah Modul Belajar Baru';
$actionUrl = $isEdit ? route('admin.modules.update', $module) : route('admin.modules.store');
@endphp

<x-app-layout>
    <div class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            {{-- KEPALA FORM --}}
            <div class="mb-8">
                <a href="{{ route('admin.modules.index') }}"
                    class="inline-flex items-center text-xs font-black text-slate-400 hover:text-indigo-600 uppercase tracking-wider transition-colors mb-3">
                    <i class="fas fa-arrow-left mr-2 text-[10px]"></i> Kembali ke Daftar Modul
                </a>
                <h1 class="text-2xl font-black text-slate-800 tracking-tight">{{ $titlePage }}</h1>
                <p class="text-sm font-medium text-slate-500 mt-1">Lengkapi parameter data materi belajar di bawah ini
                    dengan lengkap dan sesuai prosedur kurikulum bimbingan belajar.</p>
            </div>

            {{-- CONTAINER BODY FORM --}}
            <form action="{{ $actionUrl }}" method="POST" enctype="multipart/form-data" class="space-y-8"
                id="moduleForm">
                @csrf
                @if($isEdit) @method('PUT') @endif

                <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 p-8 sm:p-10 space-y-6">

                    {{-- FIELD 1: JUDUL UTAMA --}}
                    <div>
                        <label for="title" class="block text-sm font-bold text-slate-700 mb-2">Judul Materi Modul <span
                                class="text-rose-500">*</span></label>
                        <input type="text" name="title" id="title" required
                            value="{{ old('title', $module->title ?? '') }}"
                            class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-3.5 @error('title') border-rose-500 @enderror"
                            placeholder="Contoh: Trik Cepat Menghitung Aljabar Linear Dua Variabel">
                        @error('title') <p class="text-xs font-semibold text-rose-500 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- FIELD 2: DUA KOLOM KATEGORISASI (RELASI) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label for="subject_id" class="block text-sm font-bold text-slate-700 mb-2">Mata Pelajaran
                                <span class="text-rose-500">*</span></label>
                            <select name="subject_id" id="subject_id" required
                                class="w-full border-slate-200 bg-slate-50 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 text-sm cursor-pointer block p-3.5">
                                <option value="" disabled selected>-- Pilih Mata Pelajaran --</option>
                                @foreach($subjects as $sub)
                                <option value="{{ $sub->id }}" {{ old('subject_id', $module->subject_id ?? '') ==
                                    $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="level_id" class="block text-sm font-bold text-slate-700 mb-2">Tingkat Kelas
                                <span class="text-rose-500">*</span></label>
                            <select name="level_id" id="level_id" required
                                class="w-full border-slate-200 bg-slate-50 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 text-sm cursor-pointer block p-3.5">
                                <option value="" disabled selected>-- Pilih Tingkat Kelas --</option>
                                @foreach($levels as $lvl)
                                <option value="{{ $lvl->id }}" {{ old('level_id', $module->level_id ?? '') == $lvl->id ?
                                    'selected' : '' }}>{{ $lvl->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- FIELD 3: DESKRIPSI SINGKAT --}}
                    <div>
                        <label for="description" class="block text-sm font-bold text-slate-700 mb-2">Sinopsis /
                            Deskripsi Singkat</label>
                        <textarea name="description" id="description" rows="3"
                            class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-3.5"
                            placeholder="Tuliskan ringkasan 1-2 kalimat mengenai apa yang akan dipelajari siswa di modul ini untuk menarik minat baca mereka.">{{ old('description', $module->description ?? '') }}</textarea>
                    </div>

                    {{-- FIELD 4: LONG CONTENT (RICH TEXT AREA DENGAN QUILL & KATEX) --}}
                    <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/50">
                        <label for="content" class="block text-sm font-bold text-slate-700 mb-2">Isi Materi Pembelajaran
                            Lengkap</label>

                        {{-- Textarea asli disembunyikan untuk menampung data saat form disubmit --}}
                        <textarea name="content" id="content"
                            class="hidden">{{ old('content', $module->content ?? '') }}</textarea>

                        {{-- Wadah Editor Quill --}}
                        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-inner">
                            <div id="quill-editor" class="bg-white">{!! old('content', $module->content ?? '') !!}</div>
                        </div>

                        <div
                            class="mt-3 flex items-start gap-2 bg-indigo-50 border border-indigo-100 p-3 rounded-lg text-indigo-700 text-xs">
                            <i class="fas fa-info-circle mt-0.5"></i>
                            <p>
                                Klik ikon <strong><kbd>&sum;</kbd> (Formula)</strong> di bilah alat untuk mengetik rumus
                                matematika menggunakan format LaTeX (contoh: <code>c = \pm\sqrt{a^2 + b^2}</code>).
                            </p>
                        </div>
                    </div>

                    {{-- FIELD 5: INPUT FILE MULTIMEDIA (COVER, VIDEO, PDF) --}}
                    <div class="border-t border-slate-100 pt-6 space-y-6">
                        <h3 class="text-sm font-black text-slate-800 uppercase tracking-wider"><i
                                class="fas fa-paperclip mr-1 text-indigo-500"></i> Lampiran & Media Pembelajaran</h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Foto Sampul Modul
                                    (Thumbnail)</label>
                                <input type="file" name="thumbnail" accept="image/*"
                                    class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100 file:cursor-pointer bg-slate-50 p-2 rounded-xl border border-slate-200">
                                <p class="text-[11px] text-slate-400 mt-1.5">Format: JPG, PNG, WEBP. Maksimal ukuran
                                    2MB.</p>
                                @if($isEdit && $module->thumbnail)
                                <div class="mt-2 text-xs font-bold text-indigo-600 flex items-center gap-1"><i
                                        class="fas fa-image"></i> Gambar Terpasang Aktif.</div>
                                @endif
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Berkas Cetak PDF
                                    Materi</label>
                                <input type="file" name="document_path" accept="application/pdf"
                                    class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-rose-50 file:text-rose-600 hover:file:bg-rose-100 file:cursor-pointer bg-slate-50 p-2 rounded-xl border border-slate-200">
                                <p class="text-[11px] text-slate-400 mt-1.5">Format berkas harus murni .pdf. Maksimal
                                    ukuran 5MB.</p>
                                @if($isEdit && $module->document_path)
                                <div class="mt-2 text-xs font-bold text-rose-600 flex items-center gap-1"><i
                                        class="fas fa-file-pdf"></i> Dokumen PDF Terpasang Aktif.</div>
                                @endif
                            </div>
                        </div>

                        <div>
                            <label for="video_url" class="block text-sm font-bold text-slate-700 mb-2">Tautan Video
                                Penjelasan Pembuat (YouTube URL Embed)</label>
                            <input type="url" name="video_url" id="video_url"
                                value="{{ old('video_url', $module->video_url ?? '') }}"
                                class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-3.5"
                                placeholder="Contoh: https://www.youtube.com/embed/dQw4w9WgXcQ">
                            <p class="text-[11px] text-slate-400 mt-1.5">Gunakan format link embed agar video dapat
                                diputar langsung di halaman panel baca milik siswa.</p>
                        </div>
                    </div>

                    {{-- FIELD 6: PENGATURAN HAK AKSES & SKOR GAME (GAMIFIKASI) --}}
                    <div class="border-t border-slate-100 pt-6 grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <div>
                            <label for="estimated_time_minutes"
                                class="block text-sm font-bold text-slate-700 mb-2">Estimasi Waktu Baca (Menit)</label>
                            <input type="number" name="estimated_time_minutes" id="estimated_time_minutes" required
                                min="1"
                                value="{{ old('estimated_time_minutes', $module->estimated_time_minutes ?? 10) }}"
                                class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-3.5">
                        </div>
                        <div>
                            <label for="reward_points" class="block text-sm font-bold text-slate-700 mb-2">Hadiah Klaim
                                Skor Poin</label>
                            <input type="number" name="reward_points" id="reward_points" required min="0"
                                value="{{ old('reward_points', $module->reward_points ?? 0) }}"
                                class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-3.5">
                        </div>
                        <div>
                            <label for="status" class="block text-sm font-bold text-slate-700 mb-2">Status Penerbitan
                                Modul <span class="text-rose-500">*</span></label>
                            <select name="status" id="status" required
                                class="w-full border-slate-200 bg-slate-50 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 text-sm cursor-pointer block p-3.5">
                                <option value="draft" {{ old('status', $module->status ?? '') == 'draft' ? 'selected' :
                                    '' }}>Draft (Sembunyikan)</option>
                                <option value="published" {{ old('status', $module->status ?? '') == 'published' ?
                                    'selected' : '' }}>Published (Terbitkan)</option>
                                <option value="archived" {{ old('status', $module->status ?? '') == 'archived' ?
                                    'selected' : '' }}>Archived (Arsipkan)</option>
                            </select>
                        </div>
                    </div>

                    {{-- FIELD 7: TOGGLE CHECKBOX (CONTROL PREMIUM & PUBLIC) --}}
                    <div class="border-t border-slate-100 pt-6 flex flex-col sm:flex-row gap-8">
                        {{-- Bawaan hidden input agar value false tetap terkirim saat checkbox kosong --}}
                        <input type="hidden" name="is_public" value="0">
                        <label class="flex items-center gap-3 cursor-pointer select-none group">
                            <input type="checkbox" name="is_public" value="1" {{ old('is_public', $module->is_public ??
                            true) ? 'checked' : '' }} class="w-5 h-5 text-indigo-600 bg-slate-50 border-slate-300
                            rounded-lg focus:ring-indigo-500">
                            <div>
                                <span
                                    class="block text-sm font-bold text-slate-700 group-hover:text-indigo-600 transition-colors">Tampilkan
                                    Modul Secara Publik</span>
                                <span class="block text-xs text-slate-400">Jika dicentang, modul ini akan muncul di
                                    katalog perpustakaan front-end siswa.</span>
                            </div>
                        </label>

                        <input type="hidden" name="is_premium" value="0">
                        <label class="flex items-center gap-3 cursor-pointer select-none group">
                            <input type="checkbox" name="is_premium" value="1" {{ old('is_premium', $module->is_premium
                            ?? false) ? 'checked' : '' }} class="w-5 h-5 text-amber-500 bg-slate-50 border-slate-300
                            rounded-lg focus:ring-amber-500">
                            <div>
                                <span
                                    class="block text-sm font-bold text-slate-700 group-hover:text-amber-600 transition-colors">Kunci
                                    Sebagai Modul Premium (Berbayar)</span>
                                <span class="block text-xs text-slate-400">Jika aktif, hanya siswa dengan status paket
                                    premium aktif yang bisa membaca isi modul.</span>
                            </div>
                        </label>
                    </div>

                </div>

                {{-- FOOTER BUTTON SUBMIT --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                    <a href="{{ route('admin.modules.index') }}"
                        class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold py-3.5 px-6 rounded-xl text-sm transition-all shadow-sm">
                        Batalkan Sesi
                    </a>
                    <button type="submit"
                        class="bg-slate-900 hover:bg-indigo-600 text-white font-bold py-3.5 px-8 rounded-xl text-sm transition-all shadow-md hover:shadow-lg flex items-center gap-2 cursor-pointer">
                        <i class="fas fa-save text-xs"></i> Simpan Data Modul
                    </button>
                </div>

            </form>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- SCRIPT & STYLE UNTUK RICH TEXT EDITOR --}}
    {{-- ========================================== --}}

    {{-- CSS KaTeX dan Quill --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

    <style>
        /* Modifikasi Tampilan Quill agar senada dengan Tailwind */
        .ql-toolbar.ql-snow {
            background-color: #f8fafc;
            /* bg-slate-50 */
            border: none !important;
            border-bottom: 1px solid #e2e8f0 !important;
            font-family: inherit;
            padding: 12px 16px;
        }

        .ql-container.ql-snow {
            border: none !important;
            font-family: inherit;
            font-size: 1rem;
        }

        .ql-editor {
            min-height: 400px;
            padding: 1.5rem;
            color: #334155;
            line-height: 1.7;
        }

        /* Penyesuaian Modal Formula Quill */
        .ql-snow .ql-tooltip {
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
            padding: 12px 16px;
            left: 50% !important;
            transform: translateX(-50%);
        }

        /* Memperbesar ukuran teks LaTeX saat di-edit */
        .ql-snow .ql-tooltip input[type=text] {
            width: 300px;
            font-family: monospace;
            padding: 8px;
        }
    </style>

    {{-- Library JS KaTeX dan Quill --}}
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

    {{-- Inisialisasi Editor --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Konfigurasi Toolbar
            var toolbarOptions = [
                [{ 'header': [1, 2, 3, 4, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{ 'align': [] }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'script': 'sub'}, { 'script': 'super' }],
                [{ 'color': [] }, { 'background': [] }],
                ['link', 'image', 'video', 'formula'],
                ['clean']
            ];

            // Render Quill
            var quill = new Quill('#quill-editor', {
                theme: 'snow',
                modules: {
                    formula: true, // Wajib bernilai true untuk KaTeX LaTeX
                    toolbar: toolbarOptions
                },
                placeholder: 'Tuliskan materi pembelajaran secara lengkap di sini...'
            });

            // Sinkronisasi Quill dengan Textarea Hidden sebelum form dikirim
            var form = document.getElementById('moduleForm');
            var contentInput = document.getElementById('content');

            form.addEventListener('submit', function(e) {
                // Ambil kode HTML dari editor Quill
                var htmlContent = quill.root.innerHTML;

                // Masukkan ke textarea agar terkirim ke Laravel backend
                if(htmlContent === '<p><br></p>') {
                    contentInput.value = '';
                } else {
                    contentInput.value = htmlContent;
                }
            });
        });
    </script>
</x-app-layout>