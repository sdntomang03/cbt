<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/katex.min.css">

<script src="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/katex.min.js"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
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
        /* <-- Naikkan dari 60px menjadi 120px (atau sesuai selera) */
        font-size: 0.875rem;
        padding: 12px;
        /* <-- Beri ruang ketik lebih lega */
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

    .option-editor-wrap .ql-editor {
        min-height: 60px !important;
        font-size: 0.875rem;
        padding: 8px 12px;
    }

    /* ===== Simbol Button ===== */
    .ql-customSymbol {
        width: 28px !important;
    }

    .ql-customSymbol::after {
        content: "Ω";
        font-family: 'Nunito', sans-serif;
        font-weight: 900;
        font-size: 16px;
        color: #475569;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        transition: color 0.2s;
    }

    .ql-customSymbol:hover::after {
        color: #4f46e5;
    }
</style>

<script>
    document.addEventListener('alpine:init', () => {
    Alpine.data('questionEditor', (config) => {

        let myEditor = null;
        let optionEditors = {}; // key => Quill instance

        // ── Buka popup simbol dan sisipkan ke quill yg diberikan ──
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

        // ── Config toolbar mini untuk opsi ──
        function miniToolbar() {
            return {
                container: [
                    ['bold', 'italic', 'underline'],
                    [{ script: 'sub' }, { script: 'super' }],
                    ['formula', 'customSymbol'],
                ],
                handlers: {
                    customSymbol() { openSymbolPicker(this.quill); }
                }
            };
        }

        // ── Mount satu Quill mini pada wrapper dengan data-opt-id = key ──
        // ── Mount satu Quill mini pada wrapper dengan data-opt-id = key ──
        function mountQuill(key, initialHtml, onChangeCb) {
            if (optionEditors[key]) return; // sudah ada

            const wrapper = document.querySelector(`[data-opt-id="${key}"]`);
            if (!wrapper) return;

            // --- KUNCI PERBAIKAN DUPLIKASI TOOLBAR ---
            // 1. Hapus toolbar lama jika tersisa akibat daur ulang DOM Alpine
            const oldToolbar = wrapper.querySelector('.ql-toolbar');
            if (oldToolbar) oldToolbar.remove();

            // 2. Ambil container editornya (Quill biasanya merubahnya jadi .ql-container)
            let el = wrapper.querySelector('.quill-option-target') || wrapper.querySelector('.ql-container');
            if (!el) return;

            // 3. Reset elemen menjadi div bersih agar Quill tidak bingung
            el.outerHTML = '<div class="quill-option-target"></div>';
            el = wrapper.querySelector('.quill-option-target');
            // -----------------------------------------

            window.katex = katex;
            const q = new Quill(el, {
                theme: 'snow',
                modules: { formula: true, toolbar: miniToolbar() }
            });

            if (initialHtml) q.root.innerHTML = initialHtml;

            q.on('text-change', () => {
                let html = q.root.innerHTML;
                if (html === '<p><br></p>') html = '';
                onChangeCb(html);
            });

            optionEditors[key] = q;
        }

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

            // ────────────────────────────────────────────
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

            // ── Init editor narasi utama ──
            initNarasiEditor() {
                setTimeout(() => {
                    const el = document.getElementById('editorNarasi');
                    if (!el) return;
                    window.katex = katex;

                    myEditor = new Quill(el, {
                        theme: 'snow',
                        modules: {
                            formula: true,
                            toolbar: {
                                container: [
                                    ['bold','italic','underline','strike'],
                                    [{ script: 'sub' }, { script: 'super' }],
                                    [{ list: 'ordered' }, { list: 'bullet' }],
                                    [{ align: [] }],
                                    ['blockquote'],
                                    ['link','image','video','formula','customSymbol'],
                                    ['clean'],
                                ],
                                handlers: {
                                    customSymbol() { openSymbolPicker(this.quill); }
                                }
                            }
                        },
                        placeholder: 'Ketik narasi pertanyaan di sini...'
                    });

                    if (this.form.content) myEditor.root.innerHTML = this.form.content;

                    myEditor.on('text-change', () => {
                        let html = myEditor.root.innerHTML;
                        if (html === '<p><br></p>') html = '';
                        this.form.content = html;
                    });

                    // Init semua opsi setelah narasi siap
                    this.initAllOptionEditors();
                }, 100);
            },

            // ── Mount Quill untuk semua opsi yang ada ──
            initAllOptionEditors() {
                this.$nextTick(() => {
                    setTimeout(() => {
                        this.form.options.forEach((opt, i) => this.mountOptionAt(i));
                    }, 50);
                });
            },

            // ── Mount Quill untuk satu opsi di index i ──
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

            // ── Hancurkan semua instance Quill opsi ──
            destroyOptionEditors() {
                optionEditors = {};
            },

            // ────────────────────────────────────────────
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
                // Hapus instance terkait
                if (this.form.type === 'matching') {
                    delete optionEditors[`opt-${index}-premise`];
                    delete optionEditors[`opt-${index}-target`];
                } else {
                    delete optionEditors[`opt-${index}`];
                }
                this.form.options.splice(index, 1);

                // Re-index semua editor dari nol
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
                if (!this.form.content.trim()) {
                    return Swal.fire({ icon: 'warning', title: 'Oops!', text: 'Isi narasi pertanyaan terlebih dahulu!' });
                }
                this.isSaving = true;
                const method = config.isEdit ? 'put' : 'post';
                axios[method](config.submitUrl, this.form)
                    .then(() => {
                        Swal.fire({ icon: 'success', title: 'Tersimpan!', timer: 1500, showConfirmButton: false })
                            .then(() => window.location.href = config.redirectUrl);
                    })
                    .catch(err => {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: err.response?.data?.message || 'Terjadi kesalahan sistem' });
                        this.isSaving = false;
                    });
            }
        };
    });
});
</script>