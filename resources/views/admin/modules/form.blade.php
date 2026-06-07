@php
$isEdit = isset($module);
$titlePage = $isEdit ? 'Ubah Modul Belajar' : 'Tambah Modul Belajar Baru';
$actionUrl = $isEdit ? route('admin.modules.update', $module) : route('admin.modules.store');
@endphp

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
            min-height: 400px;
            padding: 1.75rem 2rem;
            line-height: 1.7;
            color: #334155;
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
            min-height: 400px;
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

        /* Penyesuaian Modal Formula Quill */
        .ql-snow .ql-tooltip {
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
            padding: 12px 16px;
            left: 50% !important;
            transform: translateX(-50%);
        }

        .ql-snow .ql-tooltip input[type=text] {
            width: 300px;
            font-family: monospace;
            padding: 8px;
        }
    </style>
    @endpush

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

                    {{-- FIELD 4: LONG CONTENT (RICH TEXT AREA DENGAN QUILL, GAMBAR RESIZE & KATEX) --}}
                    <div x-data="moduleEditor()">
                        <label class="block text-sm font-bold text-slate-700 mb-2">
                            Isi Materi Pembelajaran Lengkap
                        </label>
                        <div
                            class="border border-slate-200 rounded-2xl bg-white shadow-sm flex flex-col relative focus-within:ring-2 focus-within:ring-indigo-200 transition-all">
                            {{-- x-ignore mencegah Alpine merusak DOM Toolbar Quill saat update state --}}
                            <div x-ignore id="editor-wrapper">
                                <div id="editorArtikel"></div>
                            </div>
                        </div>
                        <input type="hidden" name="content" id="hiddenContent"
                            value="{{ old('content', $module->content ?? '') }}">

                        <div class="mt-3 flex flex-col sm:flex-row items-start gap-4 text-xs font-bold text-slate-500">
                            <span class="flex items-center gap-1"><i class="fas fa-magic text-indigo-400"></i> Mendukung
                                Paste Gambar.</span>
                            <span class="flex items-center gap-1"><i class="fas fa-expand text-emerald-400"></i> Gambar
                                bisa di-resize.</span>
                            <span class="flex items-center gap-1"><kbd
                                    class="bg-slate-100 border border-slate-200 px-1.5 rounded">&sum;</kbd> (Formula)
                                mendukung LaTeX.</span>
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
                                <span class="block text-xs text-slate-400">Jika aktif, hanya siswa paket premium yang
                                    bisa mengakses konten ini.</span>
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

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/katex.min.js"></script>
    <script src="https://fastly.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <script src="https://fastly.jsdelivr.net/npm/quill-resize-module@2.1.2/dist/resize.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <script>
        window.katex = window.katex || katex;
    if (window.Quill && window.QuillResize) {
        Quill.register('modules/resize', QuillResize.default);
    }

    document.addEventListener('alpine:init', () => {
        Alpine.data('moduleEditor', () => {
            let myEditor = null;

            // FUNGSI UPLOAD GAMBAR KE CONTROLLER MODULE
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

                // Mengarah ke route ModuleController upload image
                axios.post('{{ route("admin.modules.upload-image") }}', formData, {
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
                                return axios.post('{{ route("admin.modules.upload-image") }}', formData);
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
                    html += `<button class="symbol-btn" data-val="${s}" style="padding:10px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;font-size:18px;font-weight:900;cursor:pointer">${s}</button>`;
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
                    html += `<button class="latex-btn" data-val="${safe}" style="padding:10px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer;text-align:left;transition:all 0.2s;">
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
                            btn.addEventListener('click', () => {
                                const cursor = range ? range.index : quill.getLength();
                                const val    = btn.getAttribute('data-val');
                                quill.insertText(cursor, val);
                                const bi = val.indexOf('{');
                                quill.setSelection(bi !== -1 ? cursor + bi + 1 : cursor + val.length, bi !== -1 ? 1 : 0);
                                Swal.close();
                            });
                        });
                    }
                });
            }

            function toggleHtmlEdit(quill) {
                const container = quill.container;
                const wrapper = container.parentNode;
                let txtArea = wrapper.querySelector('.html-source-editor');
                const hiddenInput = document.getElementById('hiddenContent');

                if (!txtArea) {
                    txtArea = document.createElement('textarea');
                    txtArea.className = 'html-source-editor';
                    wrapper.insertBefore(txtArea, container.nextSibling);

                    txtArea.addEventListener('input', function () {
                        hiddenInput.value = this.value;
                    });
                }

                const qlEditor = container.querySelector('.ql-editor');
                const toolbarBtn = quill.getModule('toolbar').container.querySelector('.ql-editHtml');
                const isHtmlMode = txtArea.style.display === 'block';

                if (isHtmlMode) {
                    quill.root.innerHTML = txtArea.value;
                    txtArea.style.display = 'none';
                    qlEditor.style.display = 'block';
                    toolbarBtn.classList.remove('ql-active');
                    hiddenInput.value = quill.root.innerHTML;
                } else {
                    txtArea.value = hiddenInput.value || quill.root.innerHTML;
                    txtArea.style.display = 'block';
                    qlEditor.style.display = 'none';
                    toolbarBtn.classList.add('ql-active');
                }
            }

            function mainToolbar() {
                return {
                    container: [
                        [{ size: [] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ script: 'sub' }, { script: 'super' }],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        [{ align: [] }],
                        ['blockquote', 'code-block'],
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

            return {
                init() {
                    // Beri sedikit jeda agar DOM Alpine siap
                    setTimeout(() => {
                        const el = document.getElementById('editorArtikel');
                        const hiddenInput = document.getElementById('hiddenContent');
                        if (!el || !hiddenInput) return;

                        // Sisipkan nilai awal ke dalam DOM sebelum Quill terpasang
                        el.innerHTML = hiddenInput.value || '';

                        myEditor = new Quill(el, {
                            theme: 'snow',
                            placeholder: 'Ketik materi pembelajaran, panduan, dan rumus di sini...',
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

                        // Update hidden input saat terjadi perubahan
                        myEditor.on('text-change', () => {
                            const txtArea = document.querySelector('.html-source-editor');
                            if (!txtArea || txtArea.style.display !== 'block') {
                                const html = myEditor.root.innerHTML;
                                hiddenInput.value = (html === '<p><br></p>') ? '' : html;
                            }
                        });

                        setTimeout(() => scanAndUploadImages(myEditor), 500);

                    }, 150);

                    // Pastikan submit form mengirim data text area manual jika sedang mode HTML
                    const form = document.getElementById('moduleForm');
                    if (form) {
                        form.addEventListener('submit', () => {
                            const txtArea = document.querySelector('.html-source-editor');
                            const hiddenInput = document.getElementById('hiddenContent');
                            if (txtArea && txtArea.style.display === 'block') {
                                hiddenInput.value = txtArea.value;
                            }
                        });
                    }
                }
            }
        });
    });
    </script>
    @endpush
</x-app-layout>