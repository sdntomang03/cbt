<link href="https://fastly.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
<link href="https://fastly.jsdelivr.net/npm/quill-resize-module@2.1.2/dist/resize.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/katex.min.css">

<script src="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/katex.min.js"></script>
<script src="https://fastly.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script src="https://fastly.jsdelivr.net/npm/quill-resize-module@2.1.2/dist/resize.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<style>
    /* ===== Narasi Editor ===== */
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

    /* ===== Mini Option Editor ===== */
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

    /* ===== Efek Resize Gambar & Iframe ===== */
    .ql-editor .active {
        outline: 2px solid rgba(79, 70, 229, 0.5);
        /* Indigo 600 */
    }

    .ql-editor .selected {
        opacity: 0.5;
    }

    .ql-resize-toolbar .btn-alt {
        color: #f59e0b;
        /* Amber 500 */
        font-weight: bold;
    }

    /* ===== Tombol & UI Custom (Simbol Ω & Edit HTML) ===== */
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

    /* ===== Kotak Source Code HTML (Disembunyikan Bawaan) ===== */
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
</style>

<script>
    // ── 1. Daftarkan Modul Resize secara Global ──
    if (window.Quill && window.QuillResize) {
        Quill.register('modules/resize', QuillResize.default);
    }

    document.addEventListener('alpine:init', () => {
    Alpine.data('questionEditor', (config) => {

        let myEditor = null;
        let optionEditors = {}; // object untuk menyimpan Quill instance Opsi

        // ── 2. Konfigurasi Resize Modul ──
        function getResizeConfig() {
            return {
                embedTags: ['VIDEO', 'IFRAME'],
                tools: [
                    'left', 'center', 'right', 'full', 'edit',
                    {
                        text: 'Alt',
                        attrs: { title: 'Set image alt', class: 'btn-alt' },
                        verify(activeEle) { return activeEle && activeEle.tagName === 'IMG'; },
                        handler(evt, button, activeEle) {
                            let alt = activeEle.alt || '';
                            alt = window.prompt('Masukkan teks Alt untuk gambar:', alt);
                            if (alt != null) activeEle.setAttribute('alt', alt);
                        },
                    },
                ],
            };
        }

        // ── 3. Fitur Custom Simbol (Ω) ──
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
                customClass: { popup: 'rounded-[2rem]' },
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

        // ── 4. Fitur Mode HTML Code (</>) ──
        function toggleHtmlEdit(quill) {
            const container = quill.container;
            const wrapper = container.parentNode;

            let txtArea = wrapper.querySelector('.html-source-editor');
            if (!txtArea) {
                txtArea = document.createElement('textarea');
                txtArea.className = 'html-source-editor custom-scrollbar';
                wrapper.insertBefore(txtArea, container.nextSibling);

                // Update state saat diketik di mode HTML
                txtArea.addEventListener('input', function() {
                    quill.root.innerHTML = this.value;
                    quill.emitter.emit('text-change');
                });
            }

            const qlEditor = container.querySelector('.ql-editor');
            const toolbarButton = quill.getModule('toolbar').container.querySelector('.ql-editHtml');

            if (txtArea.style.display === 'block') {
                // Kembali ke Mode Rich Text
                quill.clipboard.dangerouslyPasteHTML(txtArea.value);
                txtArea.style.display = 'none';
                qlEditor.style.display = 'block';
                toolbarButton.classList.remove('ql-active');
            } else {
                // Pindah ke Mode Edit HTML
                txtArea.value = quill.root.innerHTML;
                txtArea.style.display = 'block';
                qlEditor.style.display = 'none';
                toolbarButton.classList.add('ql-active');
            }
        }

        // ── 5. Setup Toolbar Mini (Untuk Opsi Jawaban) ──
        function miniToolbar() {
            return {
                container: [
                    ['bold', 'italic', 'underline'],
                    [{ script: 'sub' }, { script: 'super' }],
                    ['image', 'formula', 'customSymbol', 'editHtml'],
                ],
                handlers: {
                    customSymbol() { openSymbolPicker(this.quill); },
                    editHtml() { toggleHtmlEdit(this.quill); }
                }
            };
        }

        // ── 6. Fungsi Inject Quill ke Div Target ──
        function mountQuill(key, initialHtml, onChangeCb) {
            if (optionEditors[key]) return;

            const wrapper = document.querySelector(`[data-opt-id="${key}"]`);
            if (!wrapper) return;

            const oldToolbar = wrapper.querySelector('.ql-toolbar');
            if (oldToolbar) oldToolbar.remove();

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
                    resize: getResizeConfig()
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

        // ── STATE & LOGIKA UTAMA ALPINE ──
        return {
            form: {
                type: 'single_choice',
                content: '',
                subject_id: '',
                level_id: '',
                options: []
            },
            subjects: config.subjects || [],
            levels: config.levels || [],
            isSaving: false,
            types: [
                { id: 'single_choice',  label: 'Pilgan',        icon: 'fa-dot-circle'    },
                { id: 'complex_choice', label: 'PG Kompleks',   icon: 'fa-check-square'  },
                { id: 'true_false',     label: 'Benar/Salah',   icon: 'fa-list-ol'       },
                { id: 'matching',       label: 'Menjodohkan',   icon: 'fa-exchange-alt'  },
                { id: 'essay',          label: 'Isian Singkat', icon: 'fa-keyboard'      },
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
                    if (['essay','true_false'].includes(q.type) && !opts.length) {
                        opts = [{ option_text: '', is_correct: 1 }];
                    }
                }
                this.form = {
                    type: q.type,
                    content: q.content,
                    subject_id: q.subject_id || '',
                    level_id: q.level_id || '',
                    options: opts
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
                            resize: getResizeConfig(),
                            toolbar: {
                                container: [
                                    [{ 'size': [] }],
                                    ['bold','italic','underline','strike'],
                                    [{ script: 'sub' }, { script: 'super' }],
                                    [{ list: 'ordered' }, { list: 'bullet' }],
                                    [{ align: [] }],
                                    ['blockquote'],
                                    ['link','image','video','formula','customSymbol', 'editHtml'],
                                    ['clean'],
                                ],
                                handlers: {
                                    customSymbol() { openSymbolPicker(this.quill); },
                                    editHtml() { toggleHtmlEdit(this.quill); }
                                }
                            }
                        },
                        placeholder: 'Ketik narasi pertanyaan di sini...'
                    });

                    if (this.form.content) myEditor.clipboard.dangerouslyPasteHTML(this.form.content);

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
                        this.form.options.forEach((opt, i) => this.mountOptionAt(i));
                    }, 50);
                });
            },

            mountOptionAt(i) {
                if (this.form.type === 'matching') {
                    mountQuill(
                        `opt-${i}-premise`,
                        this.form.options[i].premise_text,
                        html => { this.form.options[i].premise_text = html; }
                    );
                    mountQuill(
                        `opt-${i}-target`,
                        this.form.options[i].target_text,
                        html => { this.form.options[i].target_text = html; }
                    );
                } else {
                    mountQuill(
                        `opt-${i}`,
                        this.form.options[i].option_text,
                        html => { this.form.options[i].option_text = html; }
                    );
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
                    for (let i = 0; i < 3; i++) this.form.options.push({ option_text: '', is_correct: 1 });
                } else if (this.form.type === 'matching') {
                    for (let i = 0; i < 3; i++) this.form.options.push({ premise_text: '', target_text: '' });
                } else {
                    for (let i = 0; i < 4; i++) this.form.options.push({ option_text: '', is_correct: i === 0 ? 1 : 0 });
                }

                this.$nextTick(() => setTimeout(() => this.initAllOptionEditors(), 100));
            },

            addOption() {
                if (this.form.type === 'matching') {
                    this.form.options.push({ premise_text: '', target_text: '' });
                } else if (['essay','true_false'].includes(this.form.type)) {
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

                this.$nextTick(() => setTimeout(() => {
                    this.destroyOptionEditors();
                    this.initAllOptionEditors();
                }, 50));
            },

            toggleCorrect(index) {
                if (this.form.type === 'complex_choice') {
                    this.form.options[index].is_correct = !this.form.options[index].is_correct;
                } else if (this.form.type !== 'true_false') {
                    this.form.options.forEach((o, i) => o.is_correct = i === index ? 1 : 0);
                }
            },

            saveQuestion() {
                if (!this.form.content.trim() || this.form.content === '<p><br></p>') {
                    return Swal.fire({ icon: 'warning', title: 'Oops!', text: 'Isi narasi pertanyaan terlebih dahulu!' });
                }
                this.isSaving = true;

                let payload = JSON.parse(JSON.stringify(this.form));

                // Encode Base64
                payload.content = btoa(unescape(encodeURIComponent(payload.content)));

                payload.options = payload.options.map(opt => {
                    let newOpt = { ...opt };
                    if (newOpt.option_text) newOpt.option_text = btoa(unescape(encodeURIComponent(newOpt.option_text)));
                    if (newOpt.premise_text) newOpt.premise_text = btoa(unescape(encodeURIComponent(newOpt.premise_text)));
                    if (newOpt.target_text) newOpt.target_text = btoa(unescape(encodeURIComponent(newOpt.target_text)));
                    return newOpt;
                });

                const method = config.isEdit ? 'put' : 'post';
                axios[method](config.submitUrl, payload)
                    .then(() => {
                        Swal.fire({ icon: 'success', title: 'Tersimpan!', timer: 1500, showConfirmButton: false })
                        .then(() => window.location.href = config.redirectUrl);
                    })
                    .catch(err => {
                        let errorMsg = err.response?.data?.debug_error || err.response?.data?.message || 'Terjadi kesalahan sistem';
                        Swal.fire({ icon: 'error', title: 'Gagal', text: errorMsg });
                        this.isSaving = false;
                    });
            }
        };
    });
});
</script>