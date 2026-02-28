<x-app-layout>
    <link href="https://cdn.jsdelivr.net/npm/suneditor@latest/dist/css/suneditor.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/katex.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        [x-cloak] {
            display: none !important;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8fafc;
        }

        /* SunEditor Customization */
        .sun-editor {
            border: none !important;
        }

        .sun-editor .se-toolbar {
            background: #f8fafc !important;
            outline: 1px solid #e2e8f0;
        }

        .mini-editor .sun-editor {
            border: 1px solid #e2e8f0 !important;
            border-radius: 1rem;
        }

        .custom-scroll::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .hide-scroll::-webkit-scrollbar {
            display: none;
        }

        .bounce-active:active {
            transform: scale(0.98);
        }

        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
    </style>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 py-2">
            <div class="flex items-center gap-5">
                <div
                    class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-600 flex items-center justify-center shadow-lg shadow-indigo-200 rotate-3 hover:rotate-6 transition-transform duration-300">
                    <i class="fas fa-layer-group text-white text-2xl"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span
                            class="px-3 py-1 rounded-full bg-indigo-100 text-indigo-600 text-[10px] font-black uppercase tracking-wider">Editor
                            Mode</span>
                    </div>
                    <h2 class="font-black text-3xl text-slate-800 tracking-tight">{{ $exam->title }}</h2>
                </div>
            </div>

            <div
                class="flex items-center gap-4 bg-white/80 backdrop-blur p-2 pl-6 rounded-full shadow-sm border border-white">
                <div class="flex flex-col items-end pr-2" x-data>
                    <span class="text-[10px] uppercase font-bold text-slate-400 leading-none mb-1">Total Soal</span>
                    <span class="text-2xl font-black text-indigo-600 leading-none"
                        x-text="$store.examData?.count || 0">0</span>
                </div>
                <button x-data @click="$dispatch('open-question-modal')"
                    class="bg-slate-900 hover:bg-black text-white px-8 py-3.5 rounded-full shadow-xl shadow-slate-300 transition-all bounce-active font-bold flex items-center gap-3">
                    <i class="fas fa-plus-circle text-indigo-400"></i> Buat Soal Baru
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-10 min-h-screen"
        x-data="questionManager({{ $exam->id }}, {{ json_encode($subjects) }}, {{ json_encode($levels) }})"
        @open-question-modal.window="openModal()">

        <script>
            window.globalUploadConfig = {
                imageUploadUrl: "{{ route('admin.image.upload') }}",
                imageUploadHeader: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                imageMultipleFile: false, imageResizing: true, imageWidth: '100%'
            };
        </script>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div x-show="isLoading" class="flex flex-col items-center justify-center py-32" x-transition>
                <div class="w-16 h-16 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
                <p class="text-slate-400 font-bold mt-6 tracking-wide text-sm">Menyiapkan Ruang Belajar...</p>
            </div>

            <div x-show="!isLoading" x-cloak class="grid gap-6">
                <template x-for="(q, index) in questions" :key="q.id">
                    <div
                        class="bg-white rounded-[2.5rem] p-2 pr-8 shadow-sm border border-white hover:border-indigo-100 transition-all group relative overflow-visible">
                        <div class="flex gap-6">
                            <div
                                class="w-20 bg-slate-50 rounded-[2rem] flex flex-col items-center py-6 gap-2 shrink-0 self-stretch justify-center">
                                <span class="text-3xl font-black text-slate-200" x-text="'#' + (index + 1)"></span>
                                <div class="w-8 h-1.5 rounded-full" :class="getTypeColor(q.type)"></div>
                            </div>

                            <div class="flex-1 py-8">
                                <div class="flex justify-between items-start mb-6">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span
                                            class="px-4 py-1.5 rounded-full text-[11px] font-black uppercase tracking-wide border-2"
                                            :class="getTypeBadge(q.type)">
                                            <i class="fas mr-2" :class="getTypeIcon(q.type)"></i> <span
                                                x-text="formatType(q.type)"></span>
                                        </span>
                                        <template x-if="q.subject">
                                            <span
                                                class="px-3 py-1.5 rounded-full bg-slate-50 border border-slate-100 text-slate-500 text-[11px] font-bold">
                                                <i class="fas fa-book mr-1 opacity-50"></i> <span
                                                    x-text="q.subject.name"></span>
                                            </span>
                                        </template>
                                    </div>
                                    <div
                                        class="flex gap-2 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                        <button @click="editQuestion(q)"
                                            class="w-10 h-10 rounded-full bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white transition flex items-center justify-center bounce-active shadow-sm"><i
                                                class="fas fa-pencil-alt"></i></button>
                                        <button @click="deleteQuestion(q.id)"
                                            class="w-10 h-10 rounded-full bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition flex items-center justify-center bounce-active shadow-sm"><i
                                                class="fas fa-trash-alt"></i></button>
                                    </div>
                                </div>

                                <div class="prose prose-indigo max-w-none font-bold text-slate-700 leading-relaxed mb-6"
                                    x-html="q.content"></div>

                                <template x-if="['single_choice', 'complex_choice', 'true_false'].includes(q.type)">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <template x-for="(opt, i) in q.options" :key="i">
                                            <div class="relative flex items-start gap-3 p-3 rounded-xl border transition-all"
                                                :class="opt.is_correct == 1 ? 'bg-emerald-50 border-emerald-200' : 'bg-slate-50 border-slate-100'">

                                                <div class="shrink-0 w-6 h-6 flex items-center justify-center rounded-lg text-[10px] font-black border"
                                                    :class="opt.is_correct == 1 ? 'bg-emerald-200 border-emerald-300 text-emerald-700' : 'bg-white border-slate-200 text-slate-400'"
                                                    x-text="q.type === 'true_false' ? (i===0?'B':'S') : String.fromCharCode(65 + i)">
                                                </div>

                                                <div class="text-xs font-bold text-slate-600 prose prose-sm max-w-none prose-p:my-0"
                                                    x-html="opt.option_text"></div>

                                                <template x-if="opt.is_correct == 1">
                                                    <div class="absolute top-2 right-2 text-emerald-500 text-xs">
                                                        <i class="fas fa-check-circle"></i>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </template>

                                <template x-if="q.type === 'matching' && q.matches">
                                    <div class="space-y-2 mt-4 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                                        <p class="text-[10px] font-black text-slate-400 uppercase mb-2">Kunci Jawaban
                                            Pasangan</p>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            <template x-for="m in q.matches">
                                                <div
                                                    class="flex items-center justify-between gap-3 bg-white p-2 px-3 rounded-lg border border-slate-200 text-xs shadow-sm">
                                                    <span class="font-bold text-slate-600 truncate w-1/2"
                                                        x-html="m.premise_text.replace(/<[^>]*>?/gm, '')"></span>
                                                    <i class="fas fa-arrow-right text-amber-400 text-[10px]"></i>
                                                    <span class="font-bold text-indigo-600 truncate w-1/2 text-right"
                                                        x-text="m.target_text"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="q.type === 'essay' && q.options && q.options.length > 0">
                                    <div
                                        class="mt-4 p-4 bg-indigo-50 rounded-2xl border border-indigo-100 flex items-start gap-3">
                                        <div
                                            class="shrink-0 w-6 h-6 bg-indigo-200 text-indigo-700 rounded-lg flex items-center justify-center text-xs">
                                            <i class="fas fa-key"></i>
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-black text-indigo-400 uppercase mb-1">Kunci
                                                Jawaban</p>
                                            <div class="text-xs font-bold text-indigo-900 prose prose-sm max-w-none"
                                                x-html="q.options[0].option_text"></div>
                                        </div>
                                    </div>
                                </template>

                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div x-show="isModalOpen" x-transition.opacity
            class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6" x-cloak>
            <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" @click="closeModal()"></div>

            <div
                class="bg-[#f8fafc] w-full max-w-7xl h-[95vh] flex flex-col rounded-[2.5rem] shadow-2xl relative z-10 border-[6px] border-white overflow-hidden">

                <div
                    class="px-8 py-5 bg-white border-b border-slate-100 flex justify-between items-center shrink-0 z-20">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-11 h-11 rounded-xl bg-slate-900 flex items-center justify-center text-white shadow-lg shadow-slate-200">
                            <i class="fas" :class="isEditMode ? 'fa-edit' : 'fa-magic'"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-xl text-slate-800 leading-tight"
                                x-text="isEditMode ? 'Edit Soal' : 'Buat Soal Baru'"></h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Editor Konten
                                Ujian</p>
                        </div>
                    </div>
                    <button @click="closeModal()"
                        class="w-10 h-10 rounded-full bg-slate-50 text-slate-400 hover:bg-rose-500 hover:text-white transition-all flex items-center justify-center bounce-active">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto custom-scroll">
                    <div class="p-6 sm:p-10 space-y-8">

                        <div class="space-y-3">
                            <label class="px-2 text-[10px] font-black text-slate-400 uppercase tracking-widest">Pilih
                                Tipe Pertanyaan</label>
                            <div class="flex gap-3 overflow-x-auto pb-2 hide-scroll">
                                <template x-for="t in types" :key="t.id">
                                    <button @click="form.type = t.id; resetOptions()"
                                        class="flex items-center gap-3 px-6 py-3.5 rounded-full text-xs font-bold transition-all border shrink-0 bounce-active"
                                        :class="form.type === t.id ? 'bg-indigo-600 text-white border-indigo-600 shadow-lg shadow-indigo-200 scale-105' : 'bg-white text-slate-500 border-slate-200 hover:border-indigo-300 hover:bg-indigo-50'">
                                        <i class="fas text-sm" :class="t.icon"></i>
                                        <span x-text="t.label"></span>
                                        <div x-show="form.type === t.id" class="w-1.5 h-1.5 rounded-full bg-white ml-1">
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">

                            <div class="xl:col-span-3 space-y-6">
                                <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm space-y-5">
                                    <h4 class="text-xs font-black text-slate-800 uppercase flex items-center gap-2"><i
                                            class="fas fa-sliders-h text-indigo-500"></i> Pengaturan</h4>

                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Mata
                                            Pelajaran</label>
                                        <div class="relative">
                                            <select x-model="form.subject_id"
                                                class="w-full appearance-none bg-slate-50 border-transparent rounded-xl text-sm font-bold text-slate-700 py-3 pl-4 pr-8 focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-colors">
                                                <option value="">-- Umum --</option>
                                                <template x-for="s in masterSubjects">
                                                    <option :value="s.id" x-text="s.name"></option>
                                                </template>
                                            </select>
                                            <i
                                                class="fas fa-chevron-down absolute right-4 top-4 text-xs text-slate-300 pointer-events-none"></i>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Level
                                            Kesulitan</label>
                                        <div class="relative">
                                            <select x-model="form.level_id"
                                                class="w-full appearance-none bg-slate-50 border-transparent rounded-xl text-sm font-bold text-slate-700 py-3 pl-4 pr-8 focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-colors">
                                                <option value="">-- Default --</option>
                                                <template x-for="l in masterLevels">
                                                    <option :value="l.id" x-text="l.name"></option>
                                                </template>
                                            </select>
                                            <i
                                                class="fas fa-chevron-down absolute right-4 top-4 text-xs text-slate-300 pointer-events-none"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="xl:col-span-9 space-y-8">

                                <div class="bg-white p-1 rounded-[2.5rem] shadow-sm border border-slate-200">
                                    <div
                                        class="bg-slate-50/50 px-6 py-3 rounded-t-[2.5rem] border-b border-slate-100 flex items-center justify-between">
                                        <span
                                            class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">Narasi
                                            Soal</span>
                                        <div class="flex gap-1.5">
                                            <div class="w-2 h-2 rounded-full bg-rose-400"></div>
                                            <div class="w-2 h-2 rounded-full bg-amber-400"></div>
                                            <div class="w-2 h-2 rounded-full bg-emerald-400"></div>
                                        </div>
                                    </div>
                                    <div class="p-2">
                                        <x-editor id="main_editor" x-model="form.content" height="250px" />
                                    </div>
                                </div>

                                <div class="bg-slate-100/50 p-8 rounded-[3rem] border border-slate-200">
                                    <div class="flex justify-between items-center mb-8 px-2">
                                        <div>
                                            <h4 class="font-black text-xl text-slate-800">Jawaban</h4>
                                            <p class="text-xs font-bold text-slate-400 uppercase mt-1">Kelola pilihan
                                                dan kunci jawaban</p>
                                        </div>
                                        <button @click="addOption()"
                                            x-show="!['essay', 'true_false'].includes(form.type)"
                                            class="bg-white hover:bg-indigo-50 text-indigo-600 border border-indigo-100 px-6 py-2.5 rounded-xl text-xs font-black shadow-sm transition-all bounce-active flex items-center gap-2">
                                            <i class="fas fa-plus"></i> Tambah
                                        </button>
                                    </div>

                                    <template x-if="['single_choice', 'complex_choice'].includes(form.type)">
                                        <x-question-types.choice />
                                    </template>

                                    <template x-if="form.type === 'matching'">
                                        <x-question-types.matching />
                                    </template>

                                    <template x-if="form.type === 'true_false'">
                                        <x-question-types.true-false />
                                    </template>

                                    <template x-if="form.type === 'essay'">
                                        <x-question-types.essay />
                                    </template>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="px-8 py-6 bg-white border-t border-slate-100 flex justify-end gap-4 shrink-0 shadow-[0_-10px_40px_rgba(0,0,0,0.03)] z-20">
                    <button @click="saveQuestion()" :disabled="isSaving"
                        class="bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white px-10 py-4 rounded-xl font-black shadow-xl shadow-indigo-200 transition-all bounce-active flex items-center gap-3 disabled:opacity-70 disabled:cursor-not-allowed">
                        <span x-show="isSaving"
                            class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        <span x-text="isSaving ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                        <i x-show="!isSaving" class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/katex.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/suneditor@latest/dist/suneditor.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/suneditor@latest/src/lang/en.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @stack('scripts')

    <script>
        document.addEventListener('alpine:init', () => {
    Alpine.store('examData', { count: 0 });

    Alpine.data('questionManager', (examId, subjects, levels) => ({
        questions: [],
        masterSubjects: subjects,
        masterLevels: levels,
        isLoading: true,
        isSaving: false,
        isModalOpen: false,
        isEditMode: false,
        currentId: null,

        // Form default
        form: {
            type: 'single_choice',
            content: '',
            subject_id: '',
            level_id: '',
            options: []
        },

        types: [
            { id: 'single_choice', label: 'Pilihan Ganda', icon: 'fa-bullseye' },
            { id: 'complex_choice', label: 'PG Kompleks', icon: 'fa-check-double' },
            { id: 'matching', label: 'Menjodohkan', icon: 'fa-random' },
            { id: 'true_false', label: 'Benar / Salah', icon: 'fa-adjust' },
            { id: 'essay', label: 'Isian Singkat', icon: 'fa-pen-alt' }
        ],

        // Config Upload Gambar Global
        uploadConfig: {
            imageUploadUrl: "{{ route('admin.image.upload') }}",
            imageUploadHeader: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            imageMultipleFile: false,
            imageResizing: true,
            imageWidth: '100%'
        },

       init() { this.fetchQuestions(); },

        // 1. TAMBAHKAN FUNGSI PENYULAP RUMUS INI
    renderMath() {
            if (typeof window.katex === 'undefined') return;

            // 1. RENDER RUMUS DARI TOMBOL MATH SUNEDITOR
            document.querySelectorAll('.__se__katex').forEach(el => {
                let exp = el.getAttribute('data-exp');
                if (exp) {
                    try {
                        // Terkadang karakter '>' berubah menjadi '&gt;' di HTML, kita kembalikan
                        let txt = document.createElement("textarea");
                        txt.innerHTML = exp;
                        let decodedExp = txt.value;

                        window.katex.render(decodedExp, el, {
                            throwOnError: false,
                            displayMode: el.style.display === 'block'
                        });
                    } catch (e) {
                        console.error("Gagal render KaTeX span:", e);
                    }
                }
            });

            // 2. RENDER RUMUS DARI TEKS BIASA (COPY-PASTE)
            // Menggunakan ekstensi Auto-Render
            if (typeof renderMathInElement !== 'undefined') {
                renderMathInElement(document.body, {
                    delimiters: [
                        {left: "$$", right: "$$", display: true}, // Mode blok
                        {left: "\\[", right: "\\]", display: true}, // Mode blok alternatif
                        {left: "$", right: "$", display: false}, // Mode sebaris (inline)
                        {left: "\\(", right: "\\)", display: false} // Mode sebaris alternatif
                    ],
                    throwOnError: false
                });
            }
        },

        // 2. UBAH FUNGSI FETCH UNTUK MEMANGGIL PENYULAP TADI
        fetchQuestions() {
            this.isLoading = true;
            axios.get(`/admin/exams/${examId}/questions`)
                .then(res => {
                    this.questions = res.data.questions;
                    Alpine.store('examData').count = this.questions.length;

                    // PENTING: Tunggu Alpine selesai mencetak soal ke layar (DOM),
                    // baru jalankan fungsi penyulap KaTeX
                    this.$nextTick(() => {
                        this.renderMath();
                    });
                })
                .finally(() => this.isLoading = false);
        },
        // --- MODAL CONTROLS ---
        openModal() {
            this.isEditMode = false;
            // Reset form bersih
            this.form = { type: 'single_choice', content: '', subject_id: '', level_id: '', options: [] };
            this.resetOptions();
            this.isModalOpen = true;
        },

        closeModal() {
            this.isModalOpen = false;
        },

        // --- LOGIC OPSI ---
        resetOptions() {
            this.form.options = [];

            if(this.form.type === 'essay') {
                this.form.options.push({ option_text: '', is_correct: 1 });
            }
            else if (this.form.type === 'true_false') {
                this.form.options.push(
                    { option_text: 'Benar', is_correct: 1 },
                    { option_text: 'Salah', is_correct: 0 }
                );
            }
            else if (this.form.type === 'matching') {
                // Matching butuh 'premise_text' dan 'target_text'
                for(let i=0; i<3; i++) {
                    this.form.options.push({ premise_text: '', target_text: '' });
                }
            }
            else {
                // Default PG / Kompleks
                for(let i=0; i<4; i++) {
                    this.form.options.push({ option_text: '', is_correct: (i===0 ? 1 : 0) });
                }
            }
        },

        addOption() {
            if (this.form.type === 'matching') {
                this.form.options.push({ premise_text: '', target_text: '' });
            } else {
                this.form.options.push({ option_text: '', is_correct: 0 });
            }
        },

        removeOption(index) {
            this.form.options.splice(index, 1);
        },

        toggleCorrect(index) {
            if (this.form.type === 'complex_choice') {
                this.form.options[index].is_correct = !this.form.options[index].is_correct;
            } else {
                this.form.options.forEach((o, i) => o.is_correct = (i === index));
            }
        },

        // --- CRUD OPERATIONS ---
        // --- CRUD OPERATIONS ---
        saveQuestion() {
            // 1. Validasi Sederhana
            if (!this.form.content || this.form.content === '<p><br></p>') {
                return Swal.fire({ icon: 'warning', title: 'Oops!', text: 'Isi pertanyaan dulu!' });
            }

            if (this.form.type === 'matching') {
                let valid = this.form.options.some(o => o.premise_text && o.target_text);
                if(!valid) return Swal.fire({ icon: 'warning', title: 'Oops!', text: 'Isi minimal satu pasang soal menjodohkan!' });
            }

            this.isSaving = true;

            // 2. Fungsi Pembersih KaTeX
            // Membersihkan tag <math> di dalam span KaTeX agar payload aman dikirim
            const cleanKatexHtml = (htmlString) => {
                if (!htmlString || typeof htmlString !== 'string') return htmlString;

                let tempDiv = document.createElement('div');
                tempDiv.innerHTML = htmlString;

                tempDiv.querySelectorAll('.__se__katex').forEach(span => {
                    span.innerHTML = ''; // Kosongkan isinya, pertahankan atributnya
                });

                return tempDiv.innerHTML;
            };

            // 3. Gandakan data form (payload) dan bersihkan sebelum dikirim
            let payload = {
                type: this.form.type,
                subject_id: this.form.subject_id,
                level_id: this.form.level_id,
                content: cleanKatexHtml(this.form.content),
                options: []
            };

            // Bersihkan teks pada opsi jawaban (untuk pilihan ganda, mencocokkan, dll)
            if (this.form.options && this.form.options.length > 0) {
                payload.options = this.form.options.map(opt => {
                    let cleanOpt = { ...opt };
                    if (cleanOpt.option_text) cleanOpt.option_text = cleanKatexHtml(cleanOpt.option_text);
                    if (cleanOpt.premise_text) cleanOpt.premise_text = cleanKatexHtml(cleanOpt.premise_text);
                    if (cleanOpt.target_text) cleanOpt.target_text = cleanKatexHtml(cleanOpt.target_text);
                    return cleanOpt;
                });
            }

            // 4. Proses Pengiriman via Axios
            const url = this.isEditMode
                ? `/admin/questions/${this.currentId}`
                : `/admin/exams/${examId}/questions`;

            const method = this.isEditMode ? 'put' : 'post';

            // Kirim 'payload' yang sudah bersih, BUKAN 'this.form'
            axios[method](url, payload)
                .then(() => {
                    this.closeModal();
                    this.fetchQuestions();
                    Swal.fire({ icon: 'success', title: 'Berhasil', timer: 1500, showConfirmButton: false });
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire({ icon: 'error', title: 'Gagal', text: err.response?.data?.message || 'Terjadi kesalahan sistem' });
                })
                .finally(() => this.isSaving = false);
        },

        editQuestion(q) {
            this.isEditMode = true;
            this.currentId = q.id;

            // Mapping Data dari DB ke Format UI
            let loadedOptions = [];

            if (q.type === 'matching') {
                // Jika matching, ambil dari relasi matches, tapi petakan ke array options UI
                if (q.matches && q.matches.length > 0) {
                    loadedOptions = q.matches.map(m => ({
                        premise_text: m.premise_text,
                        target_text: m.target_text
                    }));
                } else {
                    // Fallback jika data kosong
                    loadedOptions = [{ premise_text: '', target_text: '' }];
                }
            } else {
                // Tipe lain ambil dari options
                if (q.options && q.options.length > 0) {
                    loadedOptions = q.options.map(o => ({
                        option_text: o.option_text,
                        is_correct: o.is_correct
                    }));
                }
            }

            this.form = {
                type: q.type,
                content: q.content,
                subject_id: q.subject_id || '',
                level_id: q.level_id || '',
                options: loadedOptions
            };

            this.isModalOpen = true;
        },

        deleteQuestion(id) {
            Swal.fire({ title: 'Hapus Soal?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus' })
                .then(r => {
                    if(r.isConfirmed) axios.delete(`/admin/questions/${id}`).then(() => this.fetchQuestions());
                });
        },

        // --- UTILS ---
        formatType(t) { return this.types.find(x => x.id === t)?.label || t; },
        getTypeColor(t) {
            const c = { single_choice:'bg-violet-400', complex_choice:'bg-fuchsia-400', matching:'bg-amber-400', true_false:'bg-emerald-400', essay:'bg-blue-400' };
            return c[t] || 'bg-slate-300';
        },
        getTypeIcon(t) { return this.types.find(x => x.id === t)?.icon || 'fa-question'; },
        getTypeBadge(t) {
            const b = { single_choice:'bg-violet-50 text-violet-600 border-violet-100', complex_choice:'bg-fuchsia-50 text-fuchsia-600 border-fuchsia-100', matching:'bg-amber-50 text-amber-600 border-amber-100', true_false:'bg-emerald-50 text-emerald-600 border-emerald-100', essay:'bg-blue-50 text-blue-600 border-blue-100' };
            return b[t] || 'bg-slate-50 text-slate-400';
        }
    }));
});
    </script>
</x-app-layout>