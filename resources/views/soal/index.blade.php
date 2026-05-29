<x-app-layout>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/katex.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <style>
        [x-cloak] {
            display: none !important;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8fafc;
        }

        .bounce-active:active {
            transform: scale(0.98);
        }

        .prose-custom p {
            font-weight: 300 !important;
            color: #334155 !important;
            line-height: 1.7;
        }

        .prose-custom strong,
        .prose-custom b {
            font-weight: 800 !important;
            color: #0f172a !important;
        }

        .prose-custom .katex {
            font-size: 1.1em;
        }
    </style>

    <x-slot name="header">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 py-2">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.exams.index') }}"
                    class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-slate-400 hover:text-indigo-600 shadow-sm border border-slate-200 transition-all hover:-translate-x-0.5 shrink-0">
                    <i class="fas fa-arrow-left text-sm"></i>
                </a>
                <div class="flex items-center gap-3">
                    <div
                        class="w-11 h-11 rounded-xl bg-indigo-600 flex items-center justify-center shadow-md shadow-indigo-200 shrink-0">
                        <i class="fas fa-layer-group text-white text-base"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">Bank
                            Soal</p>
                        <h2
                            class="font-black text-lg text-slate-800 tracking-tight leading-tight truncate max-w-[260px] lg:max-w-[400px]">
                            {{ $exam->title }}
                        </h2>
                    </div>
                </div>
                <div
                    class="hidden sm:flex flex-col items-center justify-center bg-indigo-50 border border-indigo-100 rounded-xl px-4 py-2 ml-1">
                    <span class="text-2xl font-black text-indigo-600 leading-none">{{ $totalQuestions }}</span>
                    <span
                        class="text-[10px] font-bold text-indigo-400 uppercase tracking-wider leading-none mt-0.5">Soal</span>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <form action="{{ route('admin.exams.soal.import', $exam->id) }}" method="POST"
                    enctype="multipart/form-data" id="formImportExcel" class="hidden">
                    @csrf
                    <input type="file" name="file_excel" id="fileExcel" accept=".xlsx,.xls,.csv"
                        onchange="document.getElementById('formImportExcel').submit()">
                </form>
                <a href="{{ route('admin.soal.template') }}" target="_blank"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-600 hover:border-slate-300 hover:bg-slate-50 font-semibold text-sm transition-all shadow-sm">
                    <i class="fas fa-file-download text-slate-400 text-xs"></i>
                    <span class="hidden md:inline">Template</span>
                </a>
                <button type="button" onclick="document.getElementById('fileExcel').click()"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 hover:bg-emerald-100 font-semibold text-sm transition-all shadow-sm">
                    <i class="fas fa-file-excel text-emerald-500 text-xs"></i>
                    <span class="hidden md:inline">Excel</span>
                </button>
                <a href="{{ route('admin.soal.import_json_view', $exam) }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 hover:bg-amber-100 font-semibold text-sm transition-all shadow-sm">
                    <i class="fas fa-file-code text-amber-500 text-xs"></i>
                    <span class="hidden md:inline">JSON</span>
                </a>
                <div class="w-px h-8 bg-slate-200 hidden sm:block mx-1"></div>
                <a href="{{ route('admin.exams.soal.create', $exam) }}"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm transition-all shadow-md shadow-indigo-200">
                    <i class="fas fa-plus text-xs"></i>
                    <span>Buat Soal</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10 min-h-screen" x-data="questionIndex({
            fetchUrl: '{{ route('admin.exams.soal.index', $exam) }}',
            deleteUrlBase: '/admin/exams/{{ $exam->getRouteKey() }}/soal',
            initialQuestions: {{ $questions->toJson() }},
            currentPage: {{ $questions->currentPage() }},
            lastPage: {{ $questions->lastPage() }},
            total: {{ $totalQuestions }},
        })">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Info bar --}}
            <div class="flex items-center justify-between mb-6">
                <p class="text-sm text-slate-500">
                    Menampilkan <span class="font-bold text-slate-700" x-text="questions.length"></span>
                    dari <span class="font-bold text-slate-700" x-text="total"></span> soal
                </p>
                <div x-show="isLoading" class="flex items-center gap-2 text-indigo-500 text-sm font-semibold">
                    <svg class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5">
                        <path
                            d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
                    </svg>
                    Memuat...
                </div>
            </div>

            {{-- List soal --}}
            <div class="grid gap-5" id="question-list">
                <template x-for="(q, index) in questions" :key="q.id">
                    <div
                        class="bg-white rounded-[2rem] p-2 pr-6 shadow-sm border border-white hover:border-indigo-100 transition-all group relative">
                        <div class="flex flex-col md:flex-row gap-5">

                            {{-- Nomor & warna tipe --}}
                            <div
                                class="w-full md:w-16 bg-slate-50 rounded-[1.5rem] flex flex-row md:flex-col items-center py-4 md:py-6 px-5 md:px-0 gap-3 md:gap-2 shrink-0 self-stretch justify-between md:justify-center">
                                <span class="text-lg md:text-2xl font-black text-slate-300"
                                    x-text="'#' + (index + 1)"></span>
                                <div class="w-10 md:w-6 h-1.5 rounded-full" :class="getTypeColor(q.type)"></div>
                            </div>

                            {{-- Konten --}}
                            <div class="flex-1 py-4 md:py-6 px-3 md:px-0">

                                {{-- Badge & aksi --}}
                                <div class="flex justify-between items-start mb-4">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span
                                            class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wide border-2"
                                            :class="getTypeBadge(q.type)">
                                            <i class="fas mr-1" :class="getTypeIcon(q.type)"></i>
                                            <span x-text="formatType(q.type)"></span>
                                        </span>
                                        <template x-if="q.subject">
                                            <span
                                                class="px-2.5 py-1 rounded-full bg-slate-50 border border-slate-100 text-slate-400 text-[10px] font-bold uppercase">
                                                <i class="fas fa-book mr-1"></i><span x-text="q.subject.name"></span>
                                            </span>
                                        </template>
                                        <template x-if="q.level">
                                            <span
                                                class="px-2.5 py-1 rounded-full bg-slate-50 border border-slate-100 text-slate-400 text-[10px] font-bold uppercase">
                                                <i class="fas fa-layer-group mr-1"></i><span
                                                    x-text="q.level.name"></span>
                                            </span>
                                        </template>
                                    </div>
                                    <div
                                        class="flex gap-1.5 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-all duration-200">
                                        <a :href="`${deleteUrlBase}/${q.id}/edit`"
                                            class="w-9 h-9 rounded-xl bg-amber-50 text-amber-500 hover:bg-amber-500 hover:text-white transition flex items-center justify-center shadow-sm text-sm">
                                            <i class="fas fa-pencil-alt"></i>
                                        </a>
                                        <button @click="deleteQuestion(q.id)"
                                            class="w-9 h-9 rounded-xl bg-rose-50 text-rose-400 hover:bg-rose-500 hover:text-white transition flex items-center justify-center shadow-sm text-sm">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </div>

                                {{-- Narasi --}}
                                <div class="prose-custom max-w-none text-slate-700 mb-4 text-sm leading-relaxed"
                                    x-html="q.content"></div>

                                {{-- Kunci Jawaban --}}
                                <div class="bg-slate-50/80 p-3 rounded-xl border border-slate-100">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Kunci
                                        Jawaban</p>

                                    {{-- PG & PG Kompleks --}}
                                    <template x-if="['single_choice', 'complex_choice'].includes(q.type)">
                                        <div class="space-y-1.5">
                                            <template x-for="(opt, i) in q.options" :key="i">
                                                <div class="flex items-start gap-2 text-sm"
                                                    :class="(opt.is_correct == 1 || opt.is_correct === true) ? 'text-emerald-600' : 'text-slate-300'">
                                                    <i class="fas mt-0.5 text-xs flex-shrink-0"
                                                        :class="(opt.is_correct == 1 || opt.is_correct === true) ? 'fa-check-circle' : 'fa-times'"></i>
                                                    <div class="prose-custom prose-sm prose-p:my-0"
                                                        x-html="opt.option_text"></div>
                                                </div>
                                            </template>
                                        </div>
                                    </template>

                                    {{-- Benar/Salah --}}
                                    <template x-if="q.type === 'true_false'">
                                        <div class="space-y-1 bg-white p-2.5 rounded-lg border border-slate-100">
                                            <template x-for="(opt, i) in q.options" :key="i">
                                                <div
                                                    class="flex items-center justify-between gap-3 py-1.5 border-b border-slate-50 last:border-0">
                                                    <div class="text-xs prose-custom prose-p:my-0 flex-1"
                                                        x-html="opt.option_text || '-'"></div>
                                                    <span
                                                        class="px-2 py-0.5 rounded text-[9px] font-black tracking-widest uppercase"
                                                        :class="opt.is_correct == 1 ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-500'"
                                                        x-text="opt.is_correct == 1 ? 'BENAR' : 'SALAH'"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </template>

                                    {{-- Matching --}}
                                    <template x-if="q.type === 'matching' && q.matches">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                                            <template x-for="(m, i) in q.matches" :key="i">
                                                <div
                                                    class="bg-white px-2.5 py-1.5 rounded-lg border border-slate-100 flex items-center gap-2 text-xs">
                                                    <span class="font-semibold text-slate-600 truncate flex-1"
                                                        x-html="m.premise_text ? m.premise_text.replace(/<[^>]*>?/gm, '') : ''"></span>
                                                    <i
                                                        class="fas fa-arrow-right text-indigo-200 flex-shrink-0 text-[10px]"></i>
                                                    <span class="text-slate-400 truncate flex-1 text-right"
                                                        x-html="m.target_text ? m.target_text.replace(/<[^>]*>?/gm, '') : ''"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </template>

                                    {{-- Essay --}}
                                    <template x-if="q.type === 'essay' && q.options && q.options.length > 0">
                                        <div class="flex flex-wrap gap-1.5">
                                            <template x-for="(opt, i) in q.options" :key="i">
                                                <span
                                                    class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-lg border border-indigo-100 flex items-center gap-1.5">
                                                    <i class="fas fa-check text-indigo-300 text-[9px]"></i>
                                                    <span class="prose-custom prose-p:my-0"
                                                        x-html="opt.option_text || '-'"></span>
                                                </span>
                                            </template>
                                        </div>
                                    </template>
                                </div>

                                {{-- Penjelasan (jika ada) --}}
                                <template x-if="q.explanation">
                                    <div
                                        class="mt-3 bg-emerald-50/60 border border-emerald-100 rounded-xl p-3 flex gap-2">
                                        <i class="fas fa-lightbulb text-emerald-400 text-xs mt-0.5 flex-shrink-0"></i>
                                        <p class="text-xs text-emerald-700 font-medium leading-relaxed"
                                            x-text="q.explanation"></p>
                                    </div>
                                </template>

                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Load More --}}
            <div class="mt-8 text-center" x-show="currentPage < lastPage">
                <button @click="loadMore()" :disabled="isLoading"
                    class="inline-flex items-center gap-2 px-8 py-3 rounded-xl bg-white border border-slate-200 text-slate-600 font-bold hover:border-indigo-300 hover:text-indigo-600 transition-all shadow-sm text-sm disabled:opacity-40">
                    <template x-if="!isLoading">
                        <span>Muat Lebih Banyak</span>
                    </template>
                    <template x-if="isLoading">
                        <span class="flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5">
                                <path
                                    d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
                            </svg>
                            Memuat...
                        </span>
                    </template>
                </button>
            </div>

            {{-- All loaded info --}}
            <div class="mt-6 text-center" x-show="currentPage >= lastPage && questions.length > 0">
                <p class="text-xs text-slate-400 font-semibold">
                    Semua <span x-text="total"></span> soal sudah ditampilkan
                </p>
            </div>

            {{-- Empty state --}}
            <div x-show="questions.length === 0 && !isLoading" x-cloak
                class="bg-white rounded-[2rem] p-16 text-center border border-dashed border-slate-200">
                <div
                    class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center text-3xl text-slate-200 mx-auto mb-5">
                    <i class="fas fa-folder-open"></i>
                </div>
                <h3 class="text-xl font-black text-slate-700 mb-2">Belum Ada Soal</h3>
                <p class="text-slate-400 text-sm mb-6">Ujian ini masih kosong. Mulai buat soal pertama.</p>
                <a href="{{ route('admin.exams.soal.create', $exam) }}"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm hover:bg-indigo-700 transition-all">
                    <i class="fas fa-plus text-xs"></i> Buat Soal Pertama
                </a>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/katex.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/contrib/auto-render.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <script>
        document.addEventListener('alpine:init', () => {
        Alpine.data('questionIndex', ({ fetchUrl, deleteUrlBase, initialQuestions, currentPage, lastPage, total }) => ({

            questions:   initialQuestions,
            currentPage: currentPage,
            lastPage:    lastPage,
            total:       total,
            isLoading:   false,
            deleteUrlBase,
            fetchUrl,

            // Mapping data tipe soal
            types: [
                { id: 'single_choice',  label: 'Pilgan',        icon: 'fa-dot-circle'   },
                { id: 'complex_choice', label: 'PG Kompleks',   icon: 'fa-check-square' },
                { id: 'true_false',     label: 'Benar/Salah',   icon: 'fa-list-ol'      },
                { id: 'matching',       label: 'Menjodohkan',   icon: 'fa-exchange-alt' },
                { id: 'essay',          label: 'Isian Singkat', icon: 'fa-keyboard'     },
            ],

            init() {
                // Render KaTeX hanya pada area soal, bukan seluruh body
                this.$nextTick(() => this.renderMath());
            },

            renderMath() {
                if (typeof renderMathInElement === 'undefined') return;
                const area = document.getElementById('question-list');
                if (!area) return;
                renderMathInElement(area, {
                    delimiters: [
                        { left: '$$', right: '$$', display: true  },
                        { left: '$',  right: '$',  display: false },
                        { left: '\\(', right: '\\)', display: false },
                        { left: '\\[', right: '\\]', display: true  },
                    ],
                    throwOnError: false,
                });
            },

            async loadMore() {
                if (this.isLoading || this.currentPage >= this.lastPage) return;
                this.isLoading = true;

                try {
                    const res = await axios.get(this.fetchUrl, {
                        params:  { page: this.currentPage + 1 },
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    });

                    const data = res.data;
                    this.questions.push(...data.data);
                    this.currentPage = data.current_page;
                    this.lastPage    = data.last_page;

                    // Render KaTeX hanya untuk soal baru setelah dirender Alpine
                    this.$nextTick(() => this.renderMath());

                } catch (e) {
                    console.error('Gagal memuat soal:', e);
                    Swal.fire('Error', 'Gagal memuat soal. Coba lagi.', 'error');
                } finally {
                    this.isLoading = false;
                }
            },

            deleteQuestion(id) {
                Swal.fire({
                    title: 'Hapus Soal?',
                    text: 'Data tidak bisa dikembalikan!',
                    icon: 'warning',
                    showCancelButton:  true,
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText:  'Batal',
                }).then(result => {
                    if (!result.isConfirmed) return;

                    axios.delete(`${this.deleteUrlBase}/${id}`)
                        .then(() => {
                            // Hapus dari array lokal — tidak perlu reload halaman
                            this.questions = this.questions.filter(q => q.id !== id);
                            this.total--;
                        })
                        .catch(e => {
                            console.error('Gagal hapus:', e);
                            Swal.fire('Gagal', 'Soal tidak berhasil dihapus.', 'error');
                        });
                });
            },

            formatType(t)    { return this.types.find(x => x.id === t)?.label || t; },
            getTypeIcon(t)   { return this.types.find(x => x.id === t)?.icon  || 'fa-question'; },
            getTypeColor(t)  {
                return {
                    single_choice:  'bg-violet-400',
                    complex_choice: 'bg-fuchsia-400',
                    matching:       'bg-amber-400',
                    true_false:     'bg-emerald-400',
                    essay:          'bg-blue-400',
                }[t] || 'bg-slate-300';
            },
            getTypeBadge(t) {
                return {
                    single_choice:  'bg-violet-50 text-violet-600 border-violet-100',
                    complex_choice: 'bg-fuchsia-50 text-fuchsia-600 border-fuchsia-100',
                    matching:       'bg-amber-50 text-amber-600 border-amber-100',
                    true_false:     'bg-emerald-50 text-emerald-600 border-emerald-100',
                    essay:          'bg-blue-50 text-blue-600 border-blue-100',
                }[t] || 'bg-slate-50 text-slate-400 border-slate-100';
            },
        }));
    });
    </script>
</x-app-layout>
