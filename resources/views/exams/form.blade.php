<x-app-layout>
    @push('styles')
    <link href="https://fastly.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
    <link href="https://fastly.jsdelivr.net/npm/quill-resize-module@2.1.2/dist/resize.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/katex.min.css">
    <style>
        /* ══ Toolbar ══ */
        .ql-toolbar.ql-snow {
            border: none !important;
            border-bottom: 1px solid #f1f5f9 !important;
            background-color: #f8fafc;
            border-radius: 1rem 1rem 0 0;
            padding: 12px 20px;
        }

        /* ══ Container ══ */
        .ql-container.ql-snow {
            border: none !important;
            font-family: 'Nunito', sans-serif;
            font-size: 15px;
        }

        /* ══ Editor area ══ */
        .ql-editor {
            min-height: 300px;
            padding: 1.75rem 2rem;
        }

        .ql-editor:focus {
            outline: none;
        }

        .ql-editor.ql-blank::before {
            color: #cbd5e1;
            font-style: normal;
            font-weight: 500;
        }

        /* ══ Tombol custom: </> | Simbol Ω | TeX ══ */
        .ql-customSymbol,
        .ql-latexTemplate,
        .ql-editHtml {
            width: 32px !important;
        }

        .ql-customSymbol::after,
        .ql-latexTemplate::after,
        .ql-editHtml::after {
            font-family: 'Nunito', sans-serif;
            font-weight: 900;
            font-size: 14px;
            color: #475569;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            transition: color 0.2s;
        }

        .ql-customSymbol::after {
            content: "Ω";
            font-size: 16px;
        }

        .ql-latexTemplate::after {
            content: "TeX";
            font-size: 13px;
        }

        .ql-editHtml::after {
            content: "</>";
            font-size: 13px;
        }

        .ql-customSymbol:hover::after,
        .ql-latexTemplate:hover::after,
        .ql-latexTemplate.ql-active::after,
        .ql-editHtml:hover::after,
        .ql-editHtml.ql-active::after {
            color: #4f46e5;
        }

        /* ══ HTML Editor ══ */
        .html-source-editor {
            width: 100%;
            min-height: 300px;
            font-family: 'IBM Plex Mono', monospace;
            font-size: 14px;
            padding: 16px;
            border: none !important;
            background-color: #0f172a;
            color: #38bdf8;
            line-height: 1.6;
            resize: vertical;
            outline: none;
            display: none;
            border-radius: 0 0 1rem 1rem;
        }

        /* ══ Loading Overlay ══ */
        .ql-upload-loading {
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 100;
            border-radius: inherit;
            font-size: 0.85rem;
            font-weight: 700;
            color: #4f46e5;
            gap: 8px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* ══ Tailwind Typography Adjustments ══ */
        .prose blockquote {
            border-left-color: #6366f1 !important;
            background: linear-gradient(to right, #f5f3ff, #fafafa);
            padding: 1rem 1.5rem !important;
            border-radius: 0 0.75rem 0.75rem 0;
            color: #4338ca !important;
            font-style: italic;
            font-weight: 600;
        }
    </style>
    @endpush

    <x-slot name="header">
        <div class="flex items-center gap-4 px-4 sm:px-6 lg:px-8">
            <a href="{{ route('admin.exams.index') }}"
                class="w-10 h-10 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-slate-500 hover:bg-slate-50 hover:text-indigo-600 transition">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="font-extrabold text-2xl text-slate-800 tracking-tight leading-none">
                    {{ isset($exam) ? 'Edit Konfigurasi Ujian' : 'Buat Ujian Baru' }}
                </h2>
                <p class="text-[10px] text-slate-400 mt-2 font-black uppercase tracking-widest">Manajemen Ujian / Form
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto">

        @if($errors->any())
        <div class="mb-6 p-4 bg-amber-50 border border-amber-200 text-amber-700 rounded-xl text-sm font-bold shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <i class="fas fa-info-circle text-lg"></i> Periksa kembali isian Anda:
            </div>
            <ul class="list-disc list-inside ml-2">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form action="{{ isset($exam) ? route('admin.exams.update', $exam) : route('admin.exams.store') }}"
            method="POST" enctype="multipart/form-data"
            class="bg-white shadow-sm sm:rounded-[2rem] border border-slate-100 overflow-hidden mb-10" x-data="{
                isPublic: {{ old('is_public', isset($exam) && $exam->is_public ? 'true' : 'false') }},
                enableViolation: {{ old('enable_violation', isset($exam) && $exam->enable_violation ? 'true' : 'false') }},
                isPremium: {{ old('is_premium', isset($exam) && $exam->is_premium ? 'true' : 'false') }}
            }">

            @csrf
            @if(isset($exam)) @method('PUT') @endif

            {{-- ═══════════════════ 1. INFORMASI DASAR ══════════════════ --}}
            <div class="p-6 sm:p-10 border-b border-slate-100 space-y-6">
                <h3 class="font-black text-slate-800 text-lg flex items-center gap-2 mb-6">
                    <div
                        class="w-8 h-8 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center text-sm">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    Informasi Dasar
                </h3>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">
                        Judul Ujian <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="title" value="{{ old('title', $exam->title ?? '') }}" required
                        class="w-full rounded-xl border-slate-200 focus:ring-indigo-500 font-bold text-slate-700 py-3 px-4"
                        placeholder="Contoh: Ujian Matematika Pecahan Kelas 4">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">
                            Jenis (Kategori) <span class="text-rose-500">*</span>
                        </label>
                        <select name="exam_type_id" required
                            class="w-full rounded-xl border-slate-200 focus:ring-indigo-500 font-bold text-slate-700 py-3 px-4 bg-slate-50">
                            <option value="">-- Pilih Tipe --</option>
                            @foreach($examTypes as $type)
                            <option value="{{ $type->id }}" {{ old('exam_type_id', $exam->exam_type_id ?? '') ==
                                $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">
                            Tingkat / Kelas <span class="text-rose-500">*</span>
                        </label>
                        <select name="level_id" required
                            class="w-full rounded-xl border-slate-200 focus:ring-indigo-500 font-bold text-slate-700 py-3 px-4 bg-slate-50">
                            <option value="">-- Pilih Level --</option>
                            @foreach($levels as $level)
                            <option value="{{ $level->id }}" {{ old('level_id', $exam->level_id ?? '') == $level->id ?
                                'selected' : '' }}>{{ $level->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">
                            Mata Pelajaran <span class="text-rose-500">*</span>
                        </label>
                        <select name="subject_id" required
                            class="w-full rounded-xl border-slate-200 focus:ring-indigo-500 font-bold text-slate-700 py-3 px-4 bg-slate-50">
                            <option value="">-- Pilih Mapel --</option>
                            @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ old('subject_id', $exam->subject_id ?? '') ==
                                $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">
                            Durasi (Menit) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" name="duration_minutes"
                            value="{{ old('duration_minutes', $exam->duration_minutes ?? 60) }}" required
                            class="w-full rounded-xl border-slate-200 focus:ring-indigo-500 font-bold text-slate-700 py-3 px-4">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">Status Publikasi</label>
                        <select name="status"
                            class="w-full rounded-xl border-slate-200 font-bold text-slate-700 py-3 px-4 bg-slate-50 uppercase text-xs">
                            @foreach(\App\Enums\ExamStatus::cases() as $status)
                            <option value="{{ $status->value }}" {{ old('status', $exam->status->value ?? 'draft') ==
                                $status->value ? 'selected' : '' }}>{{ $status->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════ 2. ATURAN & SENSOR ══════════════════ --}}
            <div class="p-6 sm:p-10 border-b border-slate-100 space-y-6 bg-slate-50/30">
                <h3 class="font-black text-slate-800 text-lg flex items-center gap-2 mb-6">
                    <div class="w-8 h-8 bg-amber-50 text-amber-600 rounded-lg flex items-center justify-center text-sm">
                        <i class="fas fa-cogs"></i>
                    </div>
                    Aturan & Sensor
                </h3>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <label
                        class="flex items-center gap-3 p-4 bg-white rounded-xl cursor-pointer hover:bg-indigo-50 transition border border-slate-200 shadow-sm">
                        <input type="checkbox" name="random_question" {{ old('random_question', $exam->random_question
                        ?? false) ? 'checked' : '' }} class="rounded text-indigo-600 border-slate-300 w-5 h-5">
                        <span class="text-xs font-black text-slate-600">Acak Soal</span>
                    </label>
                    <label
                        class="flex items-center gap-3 p-4 bg-white rounded-xl cursor-pointer hover:bg-indigo-50 transition border border-slate-200 shadow-sm">
                        <input type="checkbox" name="random_answer" {{ old('random_answer', $exam->random_answer ??
                        false) ? 'checked' : '' }} class="rounded text-indigo-600 border-slate-300 w-5 h-5">
                        <span class="text-xs font-black text-slate-600">Acak Jawaban</span>
                    </label>
                    <label
                        class="flex items-center gap-3 p-4 bg-white rounded-xl cursor-pointer hover:bg-indigo-50 transition border border-slate-200 shadow-sm">
                        <input type="checkbox" name="show_explanation" {{ old('show_explanation',
                            $exam->show_explanation ?? false) ? 'checked' : '' }} class="rounded text-indigo-600
                        border-slate-300 w-5 h-5">
                        <span class="text-xs font-black text-slate-600">Bahas Hasil</span>
                    </label>
                    <label
                        class="flex items-center gap-3 p-4 bg-white rounded-xl cursor-pointer hover:bg-indigo-50 transition border border-slate-200 shadow-sm">
                        <input type="checkbox" name="require_token" {{ old('require_token', $exam->require_token ??
                        true) ? 'checked' : '' }} class="rounded text-indigo-600 border-slate-300 w-5 h-5">
                        <span class="text-xs font-black text-slate-600">Wajib Token</span>
                    </label>
                </div>

                <div
                    class="p-5 bg-rose-50 rounded-xl border border-rose-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="enable_violation" x-model="enableViolation"
                            class="rounded text-rose-500 border-slate-300 w-5 h-5 focus:ring-rose-500">
                        <div>
                            <div class="text-sm font-black text-rose-700">Sensor Pelanggaran Tab</div>
                            <div class="text-xs text-rose-500">Kunci ujian otomatis jika siswa keluar layar</div>
                        </div>
                    </label>
                    <div x-show="enableViolation" x-transition
                        class="flex items-center gap-3 bg-white p-2 rounded-lg shadow-sm border border-rose-100">
                        <span class="text-xs font-bold text-rose-400 pl-2">Maks. Keluar Tab:</span>
                        <input type="number" name="max_tolerances"
                            value="{{ old('max_tolerances', $exam->max_tolerances ?? 3) }}"
                            class="w-20 rounded-md border-rose-200 py-1.5 text-sm text-center font-black text-rose-600">
                    </div>
                </div>
            </div>

            {{-- ═══════════════════ 3. MODE PUBLIK & SEO ══════════════════ --}}
            <div class="p-6 sm:p-10 border-b border-slate-100 space-y-6">
                <h3 class="font-black text-slate-800 text-lg flex items-center gap-2 mb-6">
                    <div
                        class="w-8 h-8 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center text-sm">
                        <i class="fas fa-globe"></i>
                    </div>
                    Mode Publik & SEO
                </h3>

                <label
                    class="flex items-center justify-between p-5 bg-emerald-50/50 rounded-2xl border border-emerald-200 cursor-pointer hover:bg-emerald-50 transition">

                    <div>
                        <div class="text-base font-black text-emerald-700">Tampilkan di Katalog Publik</div>
                        <div class="text-xs text-emerald-600/70 mt-1">Ujian dapat diakses & dikerjakan oleh pengunjung
                            tanpa login.</div>
                    </div>

                    <div class="flex items-center gap-4">

                        <a href="{{ route('admin.live-preview') }}" target="_blank" @click.stop
                            class="inline-flex items-center justify-center px-4 py-1.5 text-sm font-bold text-emerald-700 bg-white border border-emerald-300 rounded-lg shadow-sm hover:bg-emerald-100 transition-colors focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            Live Preview
                        </a>

                        <div class="relative">
                            <input type="checkbox" name="is_public" x-model="isPublic" class="sr-only">
                            <div class="w-14 h-7 rounded-full transition-colors duration-200 ease-in-out"
                                :class="isPublic ? 'bg-emerald-500' : 'bg-slate-200'">
                                <div class="w-5 h-5 bg-white rounded-full shadow-md transform transition-transform duration-200 ease-in-out mt-1"
                                    :class="isPublic ? 'translate-x-8 ml-0.5' : 'translate-x-1'"></div>
                            </div>
                        </div>

                    </div>
                </label>

                <div x-show="isPublic" class="space-y-5 pt-4">

                    {{-- 0. SETTING PREMIUM (BARU) --}}
                    <label
                        class="flex items-center justify-between p-4 bg-gradient-to-r from-amber-50 to-orange-50 rounded-2xl border border-amber-200 cursor-pointer hover:shadow-md transition">
                        <div>
                            <div class="text-base font-black text-amber-800 flex items-center gap-2">
                                <i class="fas fa-crown text-amber-500"></i> Jadikan Ujian Premium
                            </div>
                            <div class="text-xs text-amber-700/80 mt-1">Hanya user dengan langganan PRO yang dapat
                                mengerjakan ujian ini.</div>
                        </div>

                        <div class="relative">
                            <input type="checkbox" name="is_premium" x-model="isPremium" class="sr-only">
                            <div class="w-14 h-7 rounded-full transition-colors duration-200 ease-in-out"
                                :class="isPremium ? 'bg-amber-500' : 'bg-slate-200'">
                                <div class="w-5 h-5 bg-white rounded-full shadow-md transform transition-transform duration-200 ease-in-out mt-1"
                                    :class="isPremium ? 'translate-x-8 ml-0.5' : 'translate-x-1'"></div>
                            </div>
                        </div>
                    </label>

                    {{-- 1. Deskripsi Singkat untuk Card Detail --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">
                            Deskripsi Singkat <span class="text-slate-400 font-normal">(Tampil di Card/Katalog)</span>
                        </label>
                        <textarea name="description" rows="2"
                            class="w-full rounded-xl border-slate-200 focus:ring-indigo-500 text-slate-700 py-3 px-4 resize-none"
                            placeholder="Tuliskan penjelasan singkat tentang ujian ini...">{{ old('description', $exam->description ?? '') }}</textarea>
                    </div>

                    {{-- 2. Meta Description untuk SEO --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">
                            Meta Description <span class="text-slate-400 font-normal">(Opsional - Untuk SEO
                                Google)</span>
                        </label>
                        <textarea name="meta_description" rows="2"
                            class="w-full rounded-xl border-slate-200 focus:ring-indigo-500 text-slate-700 py-3 px-4 resize-none"
                            placeholder="Deskripsi untuk hasil pencarian mesin telusur (maks. 160 karakter)">{{ old('meta_description', $exam->meta_description ?? '') }}</textarea>
                    </div>

                    {{-- 3. Grid untuk Meta Keywords & Thumbnail --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-2">
                                Meta Keywords <span class="text-slate-400 font-normal">(Opsional)</span>
                            </label>
                            <input type="text" name="meta_keywords"
                                value="{{ old('meta_keywords', $exam->meta_keywords ?? '') }}"
                                class="w-full rounded-xl border-slate-200 focus:ring-indigo-500 text-slate-700 py-3 px-4"
                                placeholder="matematika, tryout, kelas 4">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-2">
                                Thumbnail / Gambar Banner <span class="text-slate-400 font-normal">(Opsional)</span>
                            </label>
                            <input type="file" name="thumbnail" accept="image/*"
                                class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-black file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100 transition cursor-pointer">
                            @if(isset($exam) && $exam->thumbnail)
                            <div class="mt-3 relative inline-block">
                                <img src="{{ asset('storage/' . $exam->thumbnail) }}"
                                    class="h-20 rounded-lg object-cover border border-slate-200">
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- 4. QUILL EDITOR (X-DATA KHUSUS) --}}
                    <div x-data="examEditor()">
                        <label class="block text-xs font-bold text-slate-700 mb-2">
                            Artikel Landing Page (Materi / Info)
                        </label>
                        <div
                            class="border border-slate-200 rounded-2xl bg-white shadow-sm flex flex-col relative focus-within:ring-2 focus-within:ring-indigo-200 transition-all">
                            {{-- x-ignore mencegah Alpine merusak DOM Toolbar Quill saat update state --}}
                            <div x-ignore id="editor-wrapper">
                                <div id="editorArtikel"></div>
                            </div>
                        </div>
                        <input type="hidden" name="content" id="hiddenContent"
                            value="{{ old('content', $exam->content ?? '') }}">
                        <p class="text-xs text-slate-400 mt-2 font-bold flex items-center justify-between">
                            <span><i class="fas fa-magic text-indigo-400 mr-1"></i> Mendukung format Tailwind Typography
                                (Prose) & Paste Gambar.</span>
                        </p>
                    </div>
                </div>
            </div>

            {{-- ══ Tombol Aksi ══ --}}
            <div class="px-6 py-5 bg-slate-50 flex justify-end gap-4">
                <a href="{{ route('admin.exams.index') }}"
                    class="px-6 py-3 text-slate-500 font-black rounded-xl hover:bg-slate-200 transition">Batal</a>
                <button type="submit"
                    class="px-10 py-3 bg-indigo-600 text-white font-black rounded-xl hover:bg-indigo-700 shadow-xl shadow-indigo-100 transition-all active:scale-95 flex items-center gap-2">
                    <i class="fas fa-save"></i>
                    {{ isset($exam) ? 'Simpan Perubahan' : 'Buat Ujian' }}
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/katex.min.js"></script>
    <script src="https://fastly.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <script src="https://fastly.jsdelivr.net/npm/quill-resize-module@2.1.2/dist/resize.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <script>
        // ── Daftarkan Dependensi Global ──
    window.katex = window.katex || katex;
    if (window.Quill && window.QuillResize) {
        Quill.register('modules/resize', QuillResize.default);
    }

    document.addEventListener('alpine:init', () => {
        Alpine.data('examEditor', () => {
            let myEditor = null;

            // =========================================================
            // FUNGSI-FUNGSI HELPER
            // =========================================================
            function uploadImageToServer(file, quill) {
                const savedRange = quill.getSelection() || { index: quill.getLength() };
                const allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

                if (!allowed.includes(file.type)) {
                    return Swal.fire('Format Tidak Didukung', 'Hanya JPG, PNG, GIF, atau WEBP yang diizinkan.', 'warning');
                }
                if (file.size > 2 * 1024 * 1024) {
                    return Swal.fire('File Terlalu Besar', 'Ukuran gambar maksimal 2MB.', 'warning');
                }

                const container = quill.container;
                const loader = document.createElement('div');
                loader.className = 'ql-upload-loading';
                loader.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation:spin 1s linear infinite"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Mengupload...';
                container.style.position = 'relative';
                container.appendChild(loader);

                const formData = new FormData();
                formData.append('image', file);

                axios.post('{{ route("admin.soal.upload-image") }}', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' },
                })
                .then(response => {
                    const url = response.data.url;
                    if (!url) throw new Error('URL tidak ditemukan');
                    quill.focus();
                    quill.insertEmbed(savedRange.index, 'image', url);
                    quill.setSelection(savedRange.index + 1);
                })
                .catch(error => {
                    const detail = error.response?.data?.message || 'Terjadi kesalahan tidak diketahui.';
                    Swal.fire('Gagal Upload', detail, 'error');
                })
                .finally(() => loader.remove());
            }

            function scanAndUploadImages(quill) {
                const images = quill.root.querySelectorAll('img');
                images.forEach(img => {
                    const src = img.getAttribute('src');
                    if (!src || img.dataset.uploading) return;
                    if (src.includes(window.location.hostname) || src.startsWith('/')) return;

                    if (src.startsWith('data:image')) {
                        img.dataset.uploading = 'true';
                        img.style.opacity = '0.4';
                        fetch(src)
                            .then(res => res.blob())
                            .then(blob => {
                                const ext = blob.type.split('/')[1] || 'png';
                                const file = new File([blob], `auto-upload.${ext}`, { type: blob.type });
                                const formData = new FormData();
                                formData.append('image', file);
                                return axios.post('{{ route("admin.soal.upload-image") }}', formData);
                            })
                            .then(response => {
                                if (response.data.url) {
                                    const blot = Quill.find(img);
                                    if (blot) {
                                        const index = quill.getIndex(blot);
                                        quill.deleteText(index, 1);
                                        quill.insertEmbed(index, 'image', response.data.url);
                                    }
                                }
                            })
                            .catch(() => { img.style.opacity = '1'; delete img.dataset.uploading; });
                    }
                });
            }

            function setupPasteHandler(quill) {
                quill.on('text-change', function(delta, oldDelta, source) {
                    if (source === 'user') {
                        setTimeout(() => scanAndUploadImages(quill), 200);
                    }
                });
                quill.root.addEventListener('paste', function (e) {
                    const clipboardData = e.clipboardData || window.clipboardData;
                    if (!clipboardData) return;
                    const items = Array.from(clipboardData.items);
                    const hasText = items.some(item => item.type === 'text/plain' || item.type === 'text/html');
                    const imageItem = items.find(item => item.type.startsWith('image/'));

                    if (imageItem && !hasText) {
                        e.stopPropagation();
                        e.preventDefault();
                        const file = imageItem.getAsFile();
                        if (file) uploadImageToServer(file, quill);
                    }
                }, true);
            }

            function imageHandler(quillInstance) {
                const input = document.createElement('input');
                input.setAttribute('type', 'file');
                input.setAttribute('accept', 'image/jpeg,image/png,image/gif,image/webp');
                input.click();
                input.onchange = () => {
                    if (input.files[0]) uploadImageToServer(input.files[0], quillInstance);
                };
            }

            function getResizeConfig() {
                return {
                    embedTags: ['VIDEO', 'IFRAME'],
                    tools: [
                        'left', 'center', 'right', 'full', 'edit',
                        {
                            text: 'Alt',
                            attrs: { title: 'Set image alt', class: 'btn-alt' },
                            verify(el) { return el && el.tagName === 'IMG'; },
                            handler(evt, btn, el) {
                                const alt = window.prompt('Teks Alt gambar:', el.alt || '');
                                if (alt !== null) el.setAttribute('alt', alt);
                            },
                        },
                    ],
                };
            }

            // =========================================================
            // FITUR TAMBAHAN: Simbol, Template LaTeX, & Mode HTML
            // =========================================================
            function openSymbolPicker(quill) {
                const range = quill.getSelection(true);
                const symbols = ['±','×','÷','≈','≠','≤','≥','∞','∴','°','π','α','β','θ','µ','Ω','∑','∫','√','½','¼','¾'];
                let html = '<div style="display:grid;grid-template-columns:repeat(6,1fr);gap:8px;margin-top:12px">';
                symbols.forEach(s => {
                    html += `<button class="symbol-btn" data-val="${s}"
                        style="padding:10px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;font-size:18px;font-weight:900;cursor:pointer">${s}</button>`;
                });
                html += '</div>';

                Swal.fire({
                    title: '<span style="font-size:16px;font-weight:900;color:#1e293b">Pilih Simbol</span>',
                    html,
                    showConfirmButton: false,
                    showCloseButton: true,
                    didOpen: () => {
                        document.querySelectorAll('.symbol-btn').forEach(btn => {
                            btn.addEventListener('click', () => {
                                const cursor = range ? range.index : quill.getLength();
                                quill.insertText(cursor, btn.getAttribute('data-val'));
                                Swal.close();
                            });
                        });
                    }
                });
            }

            function openLatexPicker(quill) {
                const range = quill.getSelection(true);
                const templates = [
                    { label: 'Pecahan',      code: '$\\frac{a}{b}$'           },
                    { label: 'Akar Kuadrat', code: '$\\sqrt{x}$'              },
                    { label: 'Pangkat',      code: '$x^{2}$'                  },
                    { label: 'Subskrip',     code: '$x_{2}$'                  },
                    { label: 'Integral',     code: '$\\int_{a}^{b}$'          },
                    { label: 'Limit',        code: '$\\lim_{x \\to \\infty}$' },
                    { label: 'Sigma',        code: '$\\sum_{i=1}^{n}$'        },
                    { label: 'Derajat',      code: '$90^{\\circ}$'            },
                ];

                let html = '<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:8px;margin-top:12px">';
                templates.forEach(t => {
                    const safe = t.code.replace(/\\/g, '\\\\');
                    html += `<button class="latex-btn" data-val="${safe}"
                        style="padding:10px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer;text-align:left;transition:all 0.2s;">
                        <span style="display:block;font-size:10px;color:#64748b;margin-bottom:4px;text-transform:uppercase;letter-spacing:1px">${t.label}</span>
                        <span style="font-family:monospace;color:#4f46e5">${safe}</span>
                    </button>`;
                });
                html += '</div>';

                Swal.fire({
                    title: '<span style="font-size:16px;font-weight:900;color:#1e293b"><i class="fas fa-square-root-alt mr-2 text-indigo-500"></i> Template LaTeX</span>',
                    html,
                    showConfirmButton: false,
                    showCloseButton: true,
                    didOpen: () => {
                        document.querySelectorAll('.latex-btn').forEach(btn => {
                            btn.addEventListener('mouseenter', () => {
                                btn.style.borderColor = '#4f46e5';
                                btn.style.backgroundColor = '#eef2ff';
                            });
                            btn.addEventListener('mouseleave', () => {
                                btn.style.borderColor = '#e2e8f0';
                                btn.style.backgroundColor = '#f8fafc';
                            });
                            btn.addEventListener('click', () => {
                                const cursor = range ? range.index : quill.getLength();
                                const val    = btn.getAttribute('data-val');
                                quill.insertText(cursor, val);
                                const bi = val.indexOf('{');
                                quill.setSelection(
                                    bi !== -1 ? cursor + bi + 1 : cursor + val.length,
                                    bi !== -1 ? 1 : 0
                                );
                                Swal.close();
                            });
                        });
                    }
                });
            }

            // =========================================================
            // FUNGSI BYPASS PENYIMPANAN HTML
            // =========================================================
            function toggleHtmlEdit(quill) {
                const container = quill.container;
                const wrapper = container.parentNode;
                let txtArea = wrapper.querySelector('.html-source-editor');
                const hiddenInput = document.getElementById('hiddenContent');

                if (!txtArea) {
                    txtArea = document.createElement('textarea');
                    txtArea.className = 'html-source-editor';
                    wrapper.insertBefore(txtArea, container.nextSibling);

                    // LANGSUNG SYNC KE HIDDEN INPUT SAAT MENGETIK HTML MENTAH
                    txtArea.addEventListener('input', function () {
                        hiddenInput.value = this.value;
                    });
                }

                const qlEditor = container.querySelector('.ql-editor');
                const toolbarBtn = quill.getModule('toolbar').container.querySelector('.ql-editHtml');
                const isHtmlMode = txtArea.style.display === 'block';

                if (isHtmlMode) {
                    // DARI HTML KEMBALI KE VISUAL (Quill akan otomatis menghapus <div> di sini)
                    quill.root.innerHTML = txtArea.value;
                    txtArea.style.display = 'none';
                    qlEditor.style.display = 'block';
                    toolbarBtn.classList.remove('ql-active');

                    // Update hidden input dengan hasil yang sudah diformat Quill
                    hiddenInput.value = quill.root.innerHTML;
                } else {
                    // MASUK KE MODE HTML
                    // Ambil dari hidden input agar struktur <div> tidak hilang
                    txtArea.value = hiddenInput.value || quill.root.innerHTML;
                    txtArea.style.display = 'block';
                    qlEditor.style.display = 'none';
                    toolbarBtn.classList.add('ql-active');
                }
            }

            // =========================================================
            // MENYUSUN TOOLBAR UTAMA
            // =========================================================
            function mainToolbar() {
                return {
                    container: [
                        [{ size: [] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ script: 'sub' }, { script: 'super' }],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        [{ align: [] }],
                        ['blockquote'],
                        ['link', 'image', 'video', 'formula', 'latexTemplate', 'customSymbol', 'editHtml'],
                        ['clean'],
                    ],
                    handlers: {
                        image() { imageHandler(this.quill); },
                        customSymbol() { openSymbolPicker(this.quill); },
                        latexTemplate() { openLatexPicker(this.quill); },
                        editHtml() { toggleHtmlEdit(this.quill); },
                    }
                };
            }

            // =========================================================
            // INISIALISASI & ALPINE STATE
            // =========================================================
            return {
                init() {
                    // Pantau status Mode Publik, render Quill saat terbuka
                    this.$watch('isPublic', (val) => {
                        if (val) this.mountEditor();
                    });

                    // Render otomatis jika status awal adalah Publik (contoh saat halaman Edit)
                    if (this.isPublic) {
                        this.mountEditor();
                    }

                    // TANGKAP EVENT SUBMIT FORM
                    // Paksa kirim HTML utuh jika pengguna menyimpan dalam mode HTML
                    const form = document.querySelector('form');
                    if (form) {
                        form.addEventListener('submit', () => {
                            const txtArea = document.querySelector('.html-source-editor');
                            const hiddenInput = document.getElementById('hiddenContent');

                            if (txtArea && txtArea.style.display === 'block') {
                                hiddenInput.value = txtArea.value;
                            }
                        });
                    }
                },

                mountEditor() {
                    if (myEditor) return;

                    // Beri jeda Alpine untuk menyelesaikan transisi render DOM (x-show)
                    setTimeout(() => {
                        const el = document.getElementById('editorArtikel');
                        const hiddenInput = document.getElementById('hiddenContent');
                        if (!el || !hiddenInput) return;

                        // Sisipkan nilai awal ke dalam DOM sebelum Quill terpasang
                        el.innerHTML = hiddenInput.value || '';

                        myEditor = new Quill(el, {
                            theme: 'snow',
                            placeholder: 'Ketik panduan, materi pendukung, atau tata tertib di sini...',
                            modules: {
                                formula: true,
                                resize: getResizeConfig(),
                                toolbar: mainToolbar(),
                            }
                        });

                        // Terapkan Tailwind Typography
                        myEditor.root.classList.add('prose', 'prose-slate', 'max-w-none');

                        // Pasang Event Listeners
                        setupPasteHandler(myEditor);

                        // HANYA UPDATE INPUT SAAT DI MODE VISUAL (Bypass Mode HTML)
                        myEditor.on('text-change', () => {
                            const txtArea = document.querySelector('.html-source-editor');
                            if (!txtArea || txtArea.style.display !== 'block') {
                                const html = myEditor.root.innerHTML;
                                hiddenInput.value = (html === '<p><br></p>') ? '' : html;
                            }
                        });

                        setTimeout(() => scanAndUploadImages(myEditor), 500);

                    }, 150);
                }
            }
        });
    });
    </script>
    @endpush
</x-app-layout>