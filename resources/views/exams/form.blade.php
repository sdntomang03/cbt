<x-app-layout>
    @push('styles')
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        .ql-toolbar.ql-snow {
            border: none !important;
            border-bottom: 1px solid #e2e8f0 !important;
            background-color: #f8fafc;
            padding: 12px;
            font-family: 'Nunito', sans-serif;
            border-top-left-radius: 1rem;
            border-top-right-radius: 1rem;
        }

        .ql-editHtml {
            width: 36px !important;
        }

        .ql-editHtml::after {
            content: "</>";
            font-family: 'Nunito', sans-serif;
            font-weight: 900;
            font-size: 13px;
            color: #475569;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            transition: color 0.2s;
        }

        .ql-editHtml:hover::after,
        .ql-editHtml.ql-active::after {
            color: #4f46e5;
        }

        .html-source-editor {
            width: 100%;
            min-height: 250px;
            font-family: 'IBM Plex Mono', 'Courier New', monospace;
            font-size: 13px;
            padding: 16px;
            border: none !important;
            background-color: #0f172a;
            color: #38bdf8;
            line-height: 1.6;
            resize: vertical;
            outline: none;
            display: none;
            border-bottom-left-radius: 1rem;
            border-bottom-right-radius: 1rem;
        }

        .ql-latexTemplate {
            width: 36px !important;
        }

        .ql-latexTemplate::after {
            content: "TeX";
            font-family: 'Nunito', sans-serif;
            font-weight: 900;
            font-size: 13px;
            color: #475569;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            transition: color 0.2s;
        }

        .ql-latexTemplate:hover::after,
        .ql-latexTemplate.ql-active::after {
            color: #4f46e5;
        }

        .ql-container.ql-snow {
            border: none !important;
            font-family: 'Nunito', sans-serif;
            font-size: 14px;
            border-bottom-left-radius: 1rem;
            border-bottom-right-radius: 1rem;
        }

        .ql-editor {
            min-height: 250px;
            padding: 1.5rem;
        }

        .ql-editor:focus {
            outline: none;
        }
    </style>
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <link href="https://fastly.jsdelivr.net/npm/quill-resize-module@2.1.2/dist/resize.css" rel="stylesheet">
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
            <div class="flex items-center gap-2 mb-2"><i class="fas fa-info-circle text-lg"></i> Periksa kembali isian
                Anda:</div>
            <ul class="list-disc list-inside ml-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form action="{{ isset($exam) ? route('admin.exams.update', $exam) : route('admin.exams.store') }}"
            method="POST" enctype="multipart/form-data"
            class="bg-white shadow-sm sm:rounded-[2rem] border border-slate-100 overflow-hidden mb-10"
            x-data="{ isPublic: {{ old('is_public', isset($exam) && $exam->is_public ? 'true' : 'false') }}, enableViolation: {{ old('enable_violation', isset($exam) && $exam->enable_violation ? 'true' : 'false') }} }">

            @csrf
            @if(isset($exam)) @method('PUT') @endif

            {{-- 1. INFORMASI DASAR --}}
            <div class="p-6 sm:p-10 border-b border-slate-100 space-y-6">
                <h3 class="font-black text-slate-800 text-lg flex items-center gap-2 mb-6">
                    <div
                        class="w-8 h-8 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center text-sm">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    Informasi Dasar
                </h3>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">Judul Ujian <span
                            class="text-rose-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $exam->title ?? '') }}" required
                        class="w-full rounded-xl border-slate-200 focus:ring-indigo-500 font-bold text-slate-700 py-3 px-4"
                        placeholder="Contoh: Ujian Matematika Pecahan Kelas 4">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">Jenis (Kategori) <span
                                class="text-rose-500">*</span></label>
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
                        <label class="block text-xs font-bold text-slate-700 mb-2">Tingkat / Kelas <span
                                class="text-rose-500">*</span></label>
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
                        <label class="block text-xs font-bold text-slate-700 mb-2">Mata Pelajaran <span
                                class="text-rose-500">*</span></label>
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
                        <label class="block text-xs font-bold text-slate-700 mb-2">Durasi (Menit) <span
                                class="text-rose-500">*</span></label>
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

            {{-- 2. PENGATURAN UJIAN --}}
            <div class="p-6 sm:p-10 border-b border-slate-100 space-y-6 bg-slate-50/30">
                <h3 class="font-black text-slate-800 text-lg flex items-center gap-2 mb-6">
                    <div class="w-8 h-8 bg-amber-50 text-amber-600 rounded-lg flex items-center justify-center text-sm">
                        <i class="fas fa-cogs"></i>
                    </div>
                    Aturan & Sensor
                </h3>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <label
                        class="flex items-center gap-3 p-4 bg-white rounded-xl cursor-pointer group hover:bg-indigo-50 transition border border-slate-200 shadow-sm">
                        <input type="checkbox" name="random_question" {{ old('random_question', $exam->random_question
                        ?? false) ? 'checked' : '' }} class="rounded text-indigo-600 border-slate-300 w-5 h-5">
                        <span class="text-xs font-black text-slate-600">Acak Soal</span>
                    </label>
                    <label
                        class="flex items-center gap-3 p-4 bg-white rounded-xl cursor-pointer group hover:bg-indigo-50 transition border border-slate-200 shadow-sm">
                        <input type="checkbox" name="random_answer" {{ old('random_answer', $exam->random_answer ??
                        false) ? 'checked' : '' }} class="rounded text-indigo-600 border-slate-300 w-5 h-5">
                        <span class="text-xs font-black text-slate-600">Acak Jawaban</span>
                    </label>
                    <label
                        class="flex items-center gap-3 p-4 bg-white rounded-xl cursor-pointer group hover:bg-indigo-50 transition border border-slate-200 shadow-sm">
                        <input type="checkbox" name="show_explanation" {{ old('show_explanation',
                            $exam->show_explanation ?? false) ? 'checked' : '' }} class="rounded text-indigo-600
                        border-slate-300 w-5 h-5">
                        <span class="text-xs font-black text-slate-600">Bahas Hasil</span>
                    </label>
                    <label
                        class="flex items-center gap-3 p-4 bg-white rounded-xl cursor-pointer group hover:bg-indigo-50 transition border border-slate-200 shadow-sm">
                        <input type="checkbox" name="require_token" {{ old('require_token', $exam->require_token ??
                        true) ? 'checked' : '' }} class="rounded text-indigo-600 border-slate-300 w-5 h-5">
                        <span class="text-xs font-black text-slate-600">Wajib Token</span>
                    </label>
                </div>

                <div
                    class="p-5 bg-rose-50 rounded-xl border border-rose-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <label class="flex items-center gap-3 cursor-pointer group">
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

            {{-- 3. SEO & PUBLIKASI --}}
            <div class="p-6 sm:p-10 border-b border-slate-100 space-y-6">
                <h3 class="font-black text-slate-800 text-lg flex items-center gap-2 mb-6">
                    <div
                        class="w-8 h-8 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center text-sm">
                        <i class="fas fa-globe"></i>
                    </div>
                    Mode Publik & SEO
                </h3>

                <label
                    class="flex items-center justify-between p-5 bg-emerald-50/50 rounded-2xl border border-emerald-200 cursor-pointer group hover:bg-emerald-50 transition">
                    <div>
                        <div class="text-base font-black text-emerald-700">Tampilkan di Katalog Publik</div>
                        <div class="text-xs text-emerald-600/70 mt-1">Ujian dapat diakses & dikerjakan oleh pengunjung
                            tanpa login.</div>
                    </div>
                    <div class="relative">
                        <input type="checkbox" name="is_public" x-model="isPublic" class="sr-only">
                        <div class="w-14 h-7 rounded-full transition-colors duration-200 ease-in-out"
                            :class="isPublic ? 'bg-emerald-500' : 'bg-slate-200'">
                            <div class="w-5 h-5 bg-white rounded-full shadow-md transform transition-transform duration-200 ease-in-out mt-1"
                                :class="isPublic ? 'translate-x-8 ml-0.5' : 'translate-x-1'"></div>
                        </div>
                    </div>
                </label>

                <div x-show="isPublic" x-transition class="space-y-5 pt-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">Deskripsi Singkat</label>
                        <textarea name="description" rows="2"
                            class="w-full rounded-xl border-slate-200 focus:ring-indigo-500 text-slate-700 py-3 px-4 resize-none">{{ old('description', $exam->description ?? '') }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-2">Meta Keywords <span
                                    class="text-slate-400 font-normal">(Opsional)</span></label>
                            <input type="text" name="meta_keywords"
                                value="{{ old('meta_keywords', $exam->meta_keywords ?? '') }}"
                                class="w-full rounded-xl border-slate-200 focus:ring-indigo-500 text-slate-700 py-3 px-4"
                                placeholder="matematika, tryout, kelas 4">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-2">Thumbnail / Gambar Banner <span
                                    class="text-slate-400 font-normal">(Opsional)</span></label>
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

                    {{-- QUILL EDITOR UNTUK ARTIKEL --}}
                    <div x-data="fullQuillEditor()">
                        <label class="block text-xs font-bold text-slate-700 mb-2">Artikel Landing Page (Materi /
                            Info)</label>
                        <div
                            class="border border-slate-200 rounded-2xl overflow-hidden focus-within:ring focus-within:ring-indigo-200 transition-all bg-white">
                            <div x-ref="editor" class="w-full text-slate-700 text-sm"></div>
                        </div>
                        <input type="hidden" name="content" id="hiddenContent"
                            value="{{ old('content', $exam->content ?? '') }}">
                        <p class="text-xs text-slate-400 mt-2 font-bold"><i
                                class="fas fa-magic text-indigo-400 mr-1"></i> Mendukung Paste (Ctrl+V) gambar secara
                            langsung.</p>
                    </div>
                </div>
            </div>

            <div class="px-6 py-5 bg-slate-50 flex justify-end gap-4">
                <a href="{{ route('admin.exams.index') }}"
                    class="px-6 py-3 text-slate-500 font-black rounded-xl hover:bg-slate-200 transition">Batal</a>
                <button type="submit"
                    class="px-10 py-3 bg-indigo-600 text-white font-black rounded-xl hover:bg-indigo-700 shadow-xl shadow-indigo-100 transition-all active:scale-95 flex items-center gap-2">
                    <i class="fas fa-save"></i> {{ isset($exam) ? 'Simpan Perubahan' : 'Buat Ujian' }}
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script src="https://fastly.jsdelivr.net/npm/quill-resize-module@2.1.2/dist/resize.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        function toggleHtmlEdit(quill) {
    const container = quill.container;
    const wrapper   = container.parentNode;

    let txtArea = wrapper.querySelector('.html-source-editor');
    if (!txtArea) {
        txtArea           = document.createElement('textarea');
        txtArea.className = 'html-source-editor';
        wrapper.insertBefore(txtArea, container.nextSibling);
        txtArea.addEventListener('input', function () {
            quill.root.innerHTML = this.value;
            quill.emitter.emit('text-change');
        });
    }

    const qlEditor   = container.querySelector('.ql-editor');
    const toolbarBtn = quill.getModule('toolbar').container
                           .querySelector('.ql-editHtml');
    const isHtmlMode = txtArea.style.display === 'block';

    if (isHtmlMode) {
        quill.clipboard.dangerouslyPasteHTML(txtArea.value);
        txtArea.style.display  = 'none';
        qlEditor.style.display = 'block';
        toolbarBtn?.classList.remove('ql-active');
    } else {
        txtArea.value          = quill.root.innerHTML;
        txtArea.style.display  = 'block';
        qlEditor.style.display = 'none';
        toolbarBtn?.classList.add('ql-active');
    }
}
        document.addEventListener('alpine:init', () => {
     // ── Daftarkan Modul Resize secara Global (Dengan Pengecekan) ──
if (window.Quill && window.QuillResize) {
    // Cek dulu apakah modul resize belum terdaftar di memori Quill
    if (!Quill.imports['modules/resize']) {
        Quill.register('modules/resize', QuillResize.default || window.QuillResize);
    }
}
            Alpine.data('fullQuillEditor', () => ({
        quill: null,
        init() {
            this.quill = new Quill(this.$refs.editor, {
                theme: 'snow',
                placeholder: 'Tulis panduan, materi pendukung, atau tata tertib di sini...',
                modules: {
                    resize: {
                        embedTags: ['IMG', 'VIDEO', 'IFRAME'],
                        tools: ['left', 'center', 'right', 'full']
                    },
                    toolbar: {
                        // 1. TAMBAHKAN NAMA TOMBOL KE DALAM CONTAINER BARIS INI
                        container: [
                            [{ 'header': [1, 2, 3, false] }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                            // Masukkan 'latexTemplate' di samping 'image' dan 'video'
                             ['link', 'image', 'video', 'latexTemplate', 'editHtml'],
                            ['clean']
                        ],
                        handlers: {
                            // 2. PASTIKAN HANDLER MENGGUNAKAN (this) KARENA BERADA DI DALAM OBYEK ALPINE
                            image() { this.imageHandler(); },
                            // Panggil fungsi global dengan melempar object quill saat ini
                            latexTemplate() { openLatexPicker(this.quill); },
                            editHtml() { toggleHtmlEdit(this.quill); },
                        }
                    }
                }
            });

                    // Muat data lama
                    const hiddenInput = document.getElementById('hiddenContent');
                    this.quill.root.innerHTML = hiddenInput.value;

                    // Sinkronisasi saat ngetik
                    this.quill.on('text-change', () => {
                        let html = this.quill.root.innerHTML;
                        if (html === '<p><br></p>') html = '';
                        hiddenInput.value = html;
                    });

                    // Paste Gambar
                    this.quill.root.addEventListener('paste', (e) => {
                        if (e.clipboardData && e.clipboardData.items) {
                            const items = e.clipboardData.items;
                            for (let i = 0; i < items.length; i++) {
                                if (items[i].type.indexOf('image') !== -1) {
                                    e.preventDefault();
                                    this.uploadToServer(items[i].getAsFile());
                                }
                            }
                        }
                    });
                },

                imageHandler() {
                    const input = document.createElement('input');
                    input.setAttribute('type', 'file'); input.setAttribute('accept', 'image/*'); input.click();
                    input.onchange = async () => { if (input.files[0]) this.uploadToServer(input.files[0]); };
                },

                async uploadToServer(file) {
                    const formData = new FormData();
                    formData.append('image', file); // Sesuaikan parameter upload gambar Anda
                    const range = this.quill.getSelection(true);
                    this.quill.insertText(range.index, ' ⏳ Mengunggah gambar...', 'user');

                    try {
                        // Sesuaikan URL ini dengan endpoint upload image Anda!
                        const response = await axios.post('{{ route("admin.soal.upload-image") }}', formData, { headers: { 'Content-Type': 'multipart/form-data' } });
                        this.quill.deleteText(range.index, 23);
                        this.quill.insertEmbed(range.index, 'image', response.data.url);
                        this.quill.setSelection(range.index + 1);
                    } catch (error) {
                        this.quill.deleteText(range.index, 23);
                        alert('Gagal mengunggah gambar.');
                    }
                }
            }));
        });
        // =========================================================
        // FITUR TEMPLATE LATEX (TeX)
        // =========================================================
        function openLatexPicker(quill) {
            const range = quill.getSelection(true);

            // Daftar kode LaTeX yang paling sering dipakai
            const templates = [
                { label: 'Pecahan', code: '$\\frac{a}{b}$' },
                { label: 'Akar Kuadrat', code: '$\\sqrt{x}$' },
                { label: 'Pangkat', code: '$x^{2}$' },
                { label: 'Subskrip (Bawah)', code: '$x_{2}$' },
                { label: 'Integral', code: '$\\int_{a}^{b}$' },
                { label: 'Limit', code: '$\\lim_{x \\to \\infty}$' },
                { label: 'Sigma (Jumlah)', code: '$\\sum_{i=1}^{n}$' },
                { label: 'Derajat', code: '$90^{\\circ}$' }
            ];

            let html = '<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:8px;margin-top:12px">';
            templates.forEach(t => {
                // Ubah garis miring tunggal menjadi ganda agar aman di dalam HTML
                const safeCode = t.code.replace(/\\/g, '\\\\');
                html += `<button class="latex-btn" data-val="${safeCode}"
                    style="padding:10px;background:#f8fafc;border:1px solid #e2e8f0;
                           border-radius:8px;font-size:14px;font-weight:700;cursor:pointer;text-align:left;transition:all 0.2s;">
                    <span style="display:block;font-size:10px;color:#64748b;margin-bottom:4px;text-transform:uppercase;letter-spacing:1px">${t.label}</span>
                    <span style="font-family:monospace;color:#4f46e5">${safeCode}</span>
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
                        // Efek Hover manual via JS
                        btn.addEventListener('mouseenter', () => { btn.style.borderColor = '#4f46e5'; btn.style.backgroundColor = '#eef2ff'; });
                        btn.addEventListener('mouseleave', () => { btn.style.borderColor = '#e2e8f0'; btn.style.backgroundColor = '#f8fafc'; });

                        btn.addEventListener('click', () => {
                            const cursor = range ? range.index : quill.getLength();
                            const val = btn.getAttribute('data-val');
                            quill.insertText(cursor, val);

                            // Letakkan kursor di dalam tanda kurung kurawal pertama (UX yang sangat membantu!)
                            const bracketIndex = val.indexOf('{');
                            if(bracketIndex !== -1) {
                                quill.setSelection(cursor + bracketIndex + 1, 1);
                            } else {
                                quill.setSelection(cursor + val.length);
                            }

                            Swal.close();
                        });
                    });
                }
            });
        }
    </script>
    @endpush
</x-app-layout>