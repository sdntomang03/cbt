<link href="https://fastly.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
<link href="https://fastly.jsdelivr.net/npm/quill-resize-module@2.1.2/dist/resize.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/katex.min.css">

<script src="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/katex.min.js"></script>
<script src="https://fastly.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script src="https://fastly.jsdelivr.net/npm/quill-resize-module@2.1.2/dist/resize.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<style>
    .ql-toolbar.ql-snow {
        border: none !important;
        border-bottom: 1px solid #f1f5f9 !important;
        background-color: #f8fafc;
        border-radius: 2rem 2rem 0 0;
        padding: 12px 20px;
    }

    .ql-container.ql-snow {
        border: none !important;
        font-family: 'Nunito', sans-serif;
        font-size: 1rem;
    }

    .ql-editor {
        min-height: 300px;
    }

    .option-editor-wrap .ql-editor {
        min-height: 120px !important;
        font-size: 0.875rem;
        padding: 12px;
    }

    .option-editor-wrap .ql-toolbar.ql-snow {
        border-radius: 0 !important;
        padding: 4px 8px !important;
        background: #f8fafc;
        border-bottom: 1px solid #f1f5f9 !important;
    }

    .option-editor-wrap .ql-container.ql-snow {
        border: none !important;
    }

    .ql-editor .active {
        outline: 2px solid rgba(79, 70, 229, 0.5);
    }

    .ql-editor .selected {
        opacity: 0.5;
    }

    .ql-resize-toolbar .btn-alt {
        color: #f59e0b;
        font-weight: bold;
    }

    .ql-customSymbol,
    .ql-editHtml {
        width: 28px !important;
    }

    .ql-customSymbol::after,
    .ql-editHtml::after {
        font-family: 'Nunito', sans-serif;
        font-weight: 900;
        font-size: 15px;
        color: #475569;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        transition: color 0.2s;
    }

    .ql-customSymbol::after {
        content: "Ω";
    }

    .ql-editHtml::after {
        content: "</>";
        font-size: 13px;
    }

    .ql-customSymbol:hover::after,
    .ql-editHtml:hover::after,
    .ql-editHtml.ql-active::after {
        color: #4f46e5;
    }

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
        border-radius: 0 0 2rem 2rem;
    }

    .option-editor-wrap .html-source-editor {
        min-height: 120px;
        border-radius: 0;
    }

    /* Loading overlay saat upload */
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
</style>

<script>
    // ── Setup CSRF Axios (HARUS paling atas, sebelum apapun) ──
axios.defaults.headers.common['X-CSRF-TOKEN'] =
    document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

// ── Daftarkan Modul Resize secara Global ──
if (window.Quill && window.QuillResize) {
    Quill.register('modules/resize', QuillResize.default);
}

document.addEventListener('alpine:init', () => {
    Alpine.data('questionEditor', (config) => {

        let myEditor      = null;
        let optionEditors = {};

        // =========================================================
        // UPLOAD GAMBAR KE SERVER
        // =========================================================
        function uploadImageToServer(file, quill) {
            // Simpan posisi cursor SEBELUM dialog file terbuka (fokus bisa hilang)
            const savedRange = quill.getSelection() || { index: quill.getLength() };

            // Validasi tipe & ukuran file di sisi klien
            const allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowed.includes(file.type)) {
                Swal.fire('Format Tidak Didukung', 'Hanya JPG, PNG, GIF, atau WEBP yang diizinkan.', 'warning');
                return;
            }
            if (file.size > 2 * 1024 * 1024) {
                Swal.fire('File Terlalu Besar', 'Ukuran gambar maksimal 2MB.', 'warning');
                return;
            }

            // Tampilkan loading di dalam editor
            const container = quill.container;
            const loader    = document.createElement('div');
            loader.className = 'ql-upload-loading';
            loader.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation:spin 1s linear infinite"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Mengupload...';
            container.style.position = 'relative';
            container.appendChild(loader);

            const formData = new FormData();
            formData.append('image', file);

            axios.post('{{ route("admin.image.upload") }}', formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            })
            .then(response => {
                const url = response.data.url;
                if (!url) throw new Error('URL tidak ditemukan dalam response server.');

                // Fokus kembali ke editor lalu insert gambar
                quill.focus();
                quill.insertEmbed(savedRange.index, 'image', url);
                quill.setSelection(savedRange.index + 1);
            })
            .catch(error => {
                const detail = error.response?.data?.detail
                    || error.response?.data?.error
                    || error.response?.data?.message
                    || error.message
                    || 'Terjadi kesalahan tidak diketahui.';
                Swal.fire('Gagal Upload', detail, 'error');
                console.error('Upload error:', error.response?.data ?? error);
            })
            .finally(() => {
                loader.remove();
            });
        }

        // =========================================================
        // HANDLER TOMBOL IMAGE (dipakai di narasi & opsi)
        // =========================================================
        function imageHandler(quillInstance) {
            const input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', 'image/jpeg,image/png,image/gif,image/webp');
            input.click();
            input.onchange = () => {
                const file = input.files[0];
                if (file) uploadImageToServer(file, quillInstance);
            };
        }

        // =========================================================
        // KONFIGURASI RESIZE MODUL
        // =========================================================
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
        // FITUR SIMBOL KHUSUS (Ω)
        // =========================================================
        function openSymbolPicker(quill) {
            const range   = quill.getSelection(true);
            const symbols = ['±','×','÷','≈','≠','≤','≥','∞','∴','°','π','α','β','θ','µ','Ω','∑','∫','√','½','¼','¾'];

            let html = '<div style="display:grid;grid-template-columns:repeat(6,1fr);gap:8px;margin-top:12px">';
            symbols.forEach(s => {
                html += `<button class="symbol-btn" data-val="${s}"
                    style="padding:10px;background:#f8fafc;border:1px solid #e2e8f0;
                           border-radius:8px;font-size:18px;font-weight:900;cursor:pointer">${s}</button>`;
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

        // =========================================================
        // FITUR TOGGLE MODE HTML (</>)
        // =========================================================
        function toggleHtmlEdit(quill) {
            const container = quill.container;
            const wrapper   = container.parentNode;

            let txtArea = wrapper.querySelector('.html-source-editor');
            if (!txtArea) {
                txtArea           = document.createElement('textarea');
                txtArea.className = 'html-source-editor custom-scrollbar';
                wrapper.insertBefore(txtArea, container.nextSibling);
                txtArea.addEventListener('input', function () {
                    quill.root.innerHTML = this.value;
                    quill.emitter.emit('text-change');
                });
            }

            const qlEditor     = container.querySelector('.ql-editor');
            const toolbarBtn   = quill.getModule('toolbar').container.querySelector('.ql-editHtml');
            const isHtmlMode   = txtArea.style.display === 'block';

            if (isHtmlMode) {
                quill.clipboard.dangerouslyPasteHTML(txtArea.value);
                txtArea.style.display  = 'none';
                qlEditor.style.display = 'block';
                toolbarBtn.classList.remove('ql-active');
            } else {
                txtArea.value          = quill.root.innerHTML;
                txtArea.style.display  = 'block';
                qlEditor.style.display = 'none';
                toolbarBtn.classList.add('ql-active');
            }
        }

        // =========================================================
        // TOOLBAR MINI (untuk opsi jawaban)
        // =========================================================
        function miniToolbar() {
            return {
                container: [
                    ['bold', 'italic', 'underline'],
                    [{ script: 'sub' }, { script: 'super' }],
                    ['image', 'formula', 'customSymbol', 'editHtml'],
                ],
                handlers: {
                    // FIX: tambahkan handler image agar tidak jadi base64
                    image()       { imageHandler(this.quill); },
                    customSymbol(){ openSymbolPicker(this.quill); },
                    editHtml()    { toggleHtmlEdit(this.quill); },
                }
            };
        }

        // =========================================================
        // TOOLBAR UTAMA (untuk narasi soal)
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
                    ['link', 'image', 'video', 'formula', 'customSymbol', 'editHtml'],
                    ['clean'],
                ],
                handlers: {
                    image()       { imageHandler(this.quill); },
                    customSymbol(){ openSymbolPicker(this.quill); },
                    editHtml()    { toggleHtmlEdit(this.quill); },
                }
            };
        }

        // =========================================================
        // MOUNT QUILL KE ELEMEN OPSI
        // =========================================================
        function mountQuill(key, initialHtml, onChangeCb) {
            if (optionEditors[key]) return;

            const wrapper = document.querySelector(`[data-opt-id="${key}"]`);
            if (!wrapper) return;

            // Bersihkan toolbar lama jika ada (cegah duplikat)
            wrapper.querySelector('.ql-toolbar')?.remove();

            let el = wrapper.querySelector('.quill-option-target') || wrapper.querySelector('.ql-container');
            if (!el) return;

            el.outerHTML = '<div class="quill-option-target"></div>';
            el = wrapper.querySelector('.quill-option-target');

            window.katex = katex;

            const q = new Quill(el, {
                theme: 'snow',
                modules: {
                    formula: true,
                    toolbar: miniToolbar(),
                    resize: getResizeConfig(),
                }
            });

            if (initialHtml) q.clipboard.dangerouslyPasteHTML(initialHtml);

            q.on('text-change', () => {
                let html = q.root.innerHTML;
                if (html === '<p><br></p>') html = '';
                onChangeCb(html);
            });

            optionEditors[key] = q;
        }

        // =========================================================
        // STATE & LOGIKA UTAMA ALPINE
        // =========================================================
        return {
            form: {
                type: 'single_choice',
                content: '',
                subject_id: '',
                level_id: '',
                options: [],
            },
            subjects:   config.subjects || [],
            levels:     config.levels   || [],
            isSaving:   false,
            types: [
                { id: 'single_choice',  label: 'Pilgan',        icon: 'fa-dot-circle'   },
                { id: 'complex_choice', label: 'PG Kompleks',   icon: 'fa-check-square' },
                { id: 'true_false',     label: 'Benar/Salah',   icon: 'fa-list-ol'      },
                { id: 'matching',       label: 'Menjodohkan',   icon: 'fa-exchange-alt' },
                { id: 'essay',          label: 'Isian Singkat', icon: 'fa-keyboard'     },
            ],

            init() {
                if (config.isEdit && config.initialData) {
                    this.setupEditData(config.initialData);
                } else {
                    this.resetOptions();
                }
                this.$nextTick(() => this.initNarasiEditor());
            },

            setupEditData(q) {
                let opts = [];
                if (q.type === 'matching') {
                    opts = q.matches?.length
                        ? q.matches.map(m => ({ premise_text: m.premise_text, target_text: m.target_text }))
                        : [{ premise_text: '', target_text: '' }];
                } else {
                    opts = q.options?.length
                        ? q.options.map(o => ({ option_text: o.option_text, is_correct: o.is_correct }))
                        : [];
                    if (['essay', 'true_false'].includes(q.type) && !opts.length) {
                        opts = [{ option_text: '', is_correct: 1 }];
                    }
                }
                this.form = {
                    type:       q.type,
                    content:    q.content,
                    subject_id: q.subject_id || '',
                    level_id:   q.level_id   || '',
                    options:    opts,
                };
            },

            initNarasiEditor() {
                setTimeout(() => {
                    const el = document.getElementById('editorNarasi');
                    if (!el) return;

                    window.katex = katex;

                    myEditor = new Quill(el, {
                        theme: 'snow',
                        modules: {
                            formula: true,
                            resize:  getResizeConfig(),
                            toolbar: mainToolbar(),
                        },
                        placeholder: 'Ketik narasi pertanyaan di sini...',
                    });

                    if (this.form.content) {
                        myEditor.clipboard.dangerouslyPasteHTML(this.form.content);
                    }

                    myEditor.on('text-change', () => {
                        let html = myEditor.root.innerHTML;
                        if (html === '<p><br></p>') html = '';
                        this.form.content = html;
                    });

                    this.initAllOptionEditors();
                }, 100);
            },

            initAllOptionEditors() {
                this.$nextTick(() => {
                    setTimeout(() => {
                        this.form.options.forEach((_, i) => this.mountOptionAt(i));
                    }, 50);
                });
            },

            mountOptionAt(i) {
                if (this.form.type === 'matching') {
                    mountQuill(`opt-${i}-premise`, this.form.options[i].premise_text,
                        html => { this.form.options[i].premise_text = html; });
                    mountQuill(`opt-${i}-target`,  this.form.options[i].target_text,
                        html => { this.form.options[i].target_text  = html; });
                } else {
                    mountQuill(`opt-${i}`, this.form.options[i].option_text,
                        html => { this.form.options[i].option_text = html; });
                }
            },

            destroyOptionEditors() {
                optionEditors = {};
            },

            resetOptions() {
                this.destroyOptionEditors();
                this.form.options = [];

                if (this.form.type === 'essay') {
                    this.form.options.push({ option_text: '', is_correct: 1 });
                } else if (this.form.type === 'true_false') {
                    for (let i = 0; i < 3; i++)
                        this.form.options.push({ option_text: '', is_correct: 1 });
                } else if (this.form.type === 'matching') {
                    for (let i = 0; i < 3; i++)
                        this.form.options.push({ premise_text: '', target_text: '' });
                } else {
                    for (let i = 0; i < 4; i++)
                        this.form.options.push({ option_text: '', is_correct: i === 0 ? 1 : 0 });
                }

                this.$nextTick(() => setTimeout(() => this.initAllOptionEditors(), 100));
            },

            addOption() {
                if (this.form.type === 'matching') {
                    this.form.options.push({ premise_text: '', target_text: '' });
                } else if (['essay', 'true_false'].includes(this.form.type)) {
                    this.form.options.push({ option_text: '', is_correct: 1 });
                } else {
                    this.form.options.push({ option_text: '', is_correct: 0 });
                }
                const newIndex = this.form.options.length - 1;
                this.$nextTick(() => setTimeout(() => this.mountOptionAt(newIndex), 50));
            },

            removeOption(index) {
                if (this.form.type === 'matching') {
                    delete optionEditors[`opt-${index}-premise`];
                    delete optionEditors[`opt-${index}-target`];
                } else {
                    delete optionEditors[`opt-${index}`];
                }
                this.form.options.splice(index, 1);

                // Re-mount semua editor agar indeks tidak kacau
                this.$nextTick(() => setTimeout(() => {
                    this.destroyOptionEditors();
                    this.initAllOptionEditors();
                }, 50));
            },

            toggleCorrect(index) {
                if (this.form.type === 'complex_choice') {
                    this.form.options[index].is_correct = !this.form.options[index].is_correct;
                } else if (this.form.type !== 'true_false') {
                    this.form.options.forEach((o, i) => { o.is_correct = i === index ? 1 : 0; });
                }
            },

            saveQuestion() {
                if (!this.form.content.trim() || this.form.content === '<p><br></p>') {
                    return Swal.fire({ icon: 'warning', title: 'Oops!', text: 'Isi narasi pertanyaan terlebih dahulu!' });
                }

                this.isSaving = true;

                // Deep copy agar state Alpine tidak termutasi
                let payload = JSON.parse(JSON.stringify(this.form));

                // Encode konten ke Base64 (aman untuk HTML + unicode)
                const toB64 = str => btoa(unescape(encodeURIComponent(str)));

                payload.content  = toB64(payload.content);
                payload.options  = payload.options.map(opt => {
                    const o = { ...opt };
                    if (o.option_text)  o.option_text  = toB64(o.option_text);
                    if (o.premise_text) o.premise_text = toB64(o.premise_text);
                    if (o.target_text)  o.target_text  = toB64(o.target_text);
                    return o;
                });

                const method = config.isEdit ? 'put' : 'post';
                axios[method](config.submitUrl, payload)
                    .then(() => {
                        Swal.fire({ icon: 'success', title: 'Tersimpan!', timer: 1500, showConfirmButton: false })
                            .then(() => { window.location.href = config.redirectUrl; });
                    })
                    .catch(err => {
                        const msg = err.response?.data?.debug_error
                            || err.response?.data?.message
                            || 'Terjadi kesalahan sistem.';
                        Swal.fire({ icon: 'error', title: 'Gagal Menyimpan', text: msg });
                        this.isSaving = false;
                    });
            },
        };
    });
});
</script>

{{-- Animasi spin untuk ikon loading upload --}}
<style>
    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }
</style>