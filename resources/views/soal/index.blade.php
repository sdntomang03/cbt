<x-app-layout>
    {{-- KUNCI 1: Pastikan memuat varian wight 300 (Light), 400 (Regular), 700 (Bold), 900 (Black) --}}
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

        /* KUNCI: Override bawaan Tailwind agar teks benar-benar tipis (Light 300) */
        .prose-custom p {
            font-weight: 300 !important;
            /* Diubah dari 400 menjadi 300 */
            color: #334155 !important;
            line-height: 1.7;
        }

        .prose-custom strong,
        .prose-custom b {
            font-weight: 800 !important;
            /* Bold tetap ekstra tebal agar kontras */
            color: #0f172a !important;
        }

        .prose-custom .katex {
            font-size: 1.1em;
        }
    </style>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 py-2">
            <div class="flex items-center gap-5">
                <a href="{{ route('admin.exams.index') }}"
                    class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-slate-500 hover:text-indigo-600 shadow-sm border border-slate-100 transition-all hover:-translate-x-1"><i
                        class="fas fa-arrow-left"></i></a>
                <div
                    class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-600 flex items-center justify-center shadow-lg shadow-indigo-200 rotate-3">
                    <i class="fas fa-layer-group text-white text-2xl"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1"><span
                            class="px-3 py-1 rounded-full bg-indigo-100 text-indigo-600 text-[10px] font-black uppercase tracking-wider">Manajemen
                            Bank Soal</span></div>
                    <h2 class="font-black text-2xl md:text-3xl text-slate-800 tracking-tight">{{ $exam->title }}</h2>
                </div>
            </div>
            <div
                class="flex items-center gap-2 md:gap-3 bg-white/80 backdrop-blur p-2 pl-6 rounded-full shadow-sm border border-white">

                {{-- 1. Counter Total Soal --}}
                <div class="flex flex-col items-end pr-2 md:pr-4">
                    <span class="text-[10px] uppercase font-bold text-slate-400 leading-none mb-1">Total Soal</span>
                    <span class="text-2xl font-black text-indigo-600 leading-none">{{ $questions->count() }}</span>
                </div>

                {{-- Garis Pemisah (Garis Vertikal) --}}
                <div class="w-px h-8 bg-slate-200 hidden sm:block"></div>

                {{-- ========================================== --}}
                {{-- 2. FORM IMPORT EXCEL (TERSEMBUNYI) --}}
                {{-- ========================================== --}}
                <form action="{{ route('admin.exams.soal.import', $exam->id) }}" method="POST"
                    enctype="multipart/form-data" id="formImportExcel" class="hidden">
                    @csrf
                    <input type="file" name="file_excel" id="fileExcel" accept=".xlsx, .xls, .csv"
                        onchange="document.getElementById('formImportExcel').submit()">
                </form>

                {{-- 3. TOMBOL DOWNLOAD TEMPLATE --}}
                <a href="{{ route('admin.soal.template') }}" target="_blank"
                    class="bg-white hover:bg-slate-50 text-slate-600 border border-slate-200 px-4 md:px-5 py-3.5 rounded-full font-bold transition-all flex items-center gap-2 text-sm shadow-sm bounce-active"
                    title="Download Template Excel">
                    <i class="fas fa-file-download text-slate-400"></i>
                    <span class="hidden lg:inline">Template</span>
                </a>

                {{-- 4. TOMBOL IMPORT --}}
                <button type="button" onclick="document.getElementById('fileExcel').click()"
                    class="bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-100 px-4 md:px-5 py-3.5 rounded-full font-bold transition-all flex items-center gap-2 text-sm shadow-sm bounce-active"
                    title="Import dari Excel">
                    <i class="fas fa-file-excel text-emerald-500"></i>
                    <span class="hidden lg:inline">Import</span>
                </button>

                {{-- 5. TOMBOL MANUAL (Bawaan Anda) --}}
                <a href="{{ route('admin.exams.soal.create', $exam->id) }}"
                    class="bg-slate-900 hover:bg-black text-white px-6 md:px-8 py-3.5 rounded-full shadow-xl shadow-slate-300 transition-all bounce-active font-bold flex items-center gap-2 md:gap-3 ml-1 text-sm md:text-base">
                    <i class="fas fa-plus-circle text-indigo-400"></i>
                    <span class="hidden sm:inline">Buat Manual</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10 min-h-screen" x-data="questionIndex({ questions: {{ $questions->toJson() }} })"
        x-init="renderMath()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid gap-6">
                <template x-for="(q, index) in questions" :key="q.id">
                    <div
                        class="bg-white rounded-[2.5rem] p-2 pr-8 shadow-sm border border-white hover:border-indigo-100 transition-all group relative">
                        <div class="flex flex-col md:flex-row gap-6">
                            {{-- Nomor Soal --}}
                            <div
                                class="w-full md:w-20 bg-slate-50 rounded-[2rem] flex flex-row md:flex-col items-center py-4 md:py-6 px-6 md:px-0 gap-4 md:gap-2 shrink-0 self-stretch justify-between md:justify-center">
                                <span class="text-xl md:text-3xl font-black text-slate-300"
                                    x-text="'#' + (index + 1)"></span>
                                <div class="w-12 md:w-8 h-1.5 rounded-full" :class="getTypeColor(q.type)"></div>
                            </div>

                            {{-- Konten Soal --}}
                            <div class="flex-1 py-4 md:py-8 px-4 md:px-0">
                                <div class="flex justify-between items-start mb-6">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span
                                            class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wide border-2"
                                            :class="getTypeBadge(q.type)">
                                            <i class="fas mr-1.5" :class="getTypeIcon(q.type)"></i> <span
                                                x-text="formatType(q.type)"></span>
                                        </span>
                                        <template x-if="q.subject">
                                            <span
                                                class="px-3 py-1.5 rounded-full bg-slate-50 border border-slate-100 text-slate-500 text-[10px] font-bold uppercase"><i
                                                    class="fas fa-book mr-1"></i> <span
                                                    x-text="q.subject.name"></span></span>
                                        </template>
                                        <template x-if="q.level">
                                            <span
                                                class="px-3 py-1.5 rounded-full bg-slate-50 border border-slate-100 text-slate-500 text-[10px] font-bold uppercase"><i
                                                    class="fas fa-layer-group mr-1"></i> <span
                                                    x-text="q.level.name"></span></span>
                                        </template>
                                    </div>

                                    {{-- Tombol Aksi --}}
                                    <div
                                        class="flex gap-2 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-all duration-300">
                                        <a :href="`/admin/exams/{{ $exam->id }}/soal/${q.id}/edit`"
                                            class="w-10 h-10 rounded-full bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white transition flex items-center justify-center bounce-active shadow-sm"><i
                                                class="fas fa-pencil-alt"></i></a>
                                        <button @click="deleteQuestion(q.id)"
                                            class="w-10 h-10 rounded-full bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition flex items-center justify-center bounce-active shadow-sm"><i
                                                class="fas fa-trash-alt"></i></button>
                                    </div>
                                </div>

                                {{-- Narasi --}}
                                {{-- KUNCI 3: Hapus 'font-bold' dari class utama dan ganti 'prose' dengan 'prose-custom'
                                buatan kita --}}
                                <div class="prose-custom max-w-none text-slate-700 mb-6 __se__katex_container"
                                    x-html="q.content"></div>

                                {{-- Preview Jawaban Tersimpan --}}
                                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                                    <h5 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">
                                        Kunci Jawaban:</h5>

                                    {{-- Preview PG / PG Kompleks --}}
                                    <template x-if="['single_choice', 'complex_choice'].includes(q.type)">
                                        <div class="space-y-2">
                                            <template x-for="(opt, i) in q.options" :key="i">
                                                <div class="flex items-start gap-2"
                                                    :class="(opt.is_correct == 1 || opt.is_correct === true) ? 'text-emerald-600' : 'text-slate-400 opacity-50'">
                                                    <i class="fas mt-1 text-sm"
                                                        :class="(opt.is_correct == 1 || opt.is_correct === true) ? 'fa-check-circle' : 'fa-times'"></i>
                                                    {{-- Gunakan prose-custom di sini juga --}}
                                                    <div class="text-sm prose-custom prose-sm prose-p:my-0"
                                                        x-html="opt.option_text"></div>
                                                </div>
                                            </template>
                                        </div>
                                    </template>

                                    {{-- Preview Benar Salah (MULTI PERNYATAAN) --}}
                                    <template x-if="q.type === 'true_false'">
                                        <div class="space-y-2 bg-white p-3 rounded-xl border border-slate-200">
                                            <template x-for="(opt, i) in q.options" :key="i">
                                                <div
                                                    class="flex items-start justify-between gap-4 py-2 border-b border-slate-100 last:border-0 last:pb-0">
                                                    <div class="text-sm prose-custom prose-sm prose-p:my-0 flex-1"
                                                        x-html="opt.option_text || '- Pernyataan Kosong -'"></div>
                                                    <div class="shrink-0">
                                                        <span
                                                            class="px-3 py-1 rounded-md text-[10px] font-black tracking-widest uppercase shadow-sm"
                                                            :class="opt.is_correct == 1 ? 'bg-emerald-500 text-white' : 'bg-rose-500 text-white'"
                                                            x-text="opt.is_correct == 1 ? 'BENAR' : 'SALAH'"></span>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </template>

                                    {{-- Preview Matching (Menjodohkan) --}}
                                    <template x-if="q.type === 'matching' && q.matches">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                            <template x-for="(m, i) in q.matches" :key="i">
                                                <div
                                                    class="bg-white p-2 rounded-lg border border-slate-200 flex items-center justify-between text-xs shadow-sm">
                                                    <span class="prose-custom truncate w-[45%]"
                                                        x-html="m.premise_text ? m.premise_text.replace(/<[^>]*>?/gm, '') : ''"></span>
                                                    <i class="fas fa-arrow-right text-indigo-300"></i>
                                                    <span class="prose-custom truncate w-[45%] text-right"
                                                        x-html="m.target_text ? m.target_text.replace(/<[^>]*>?/gm, '') : ''"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </template>

                                    {{-- Preview Essay --}}
                                    <template x-if="q.type === 'essay' && q.options && q.options.length > 0">
                                        <div class="flex flex-wrap gap-2">
                                            <template x-for="(opt, i) in q.options" :key="i">
                                                <div
                                                    class="text-sm font-bold text-indigo-700 bg-indigo-50 px-3 py-1.5 rounded-lg border border-indigo-100 flex items-center gap-2">
                                                    <i class="fas fa-check text-indigo-400 text-[10px]"></i>
                                                    {{-- Note: Essay biarkan tebal (font-bold bawaan div di atas) karena
                                                    ini kata kunci singkat --}}
                                                    <span class="prose-custom prose-p:my-0"
                                                        x-html="opt.option_text || '-'"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>

                            </div>
                        </div>
                    </div>
                </template>

                {{-- Kosong --}}
                <div x-show="questions.length === 0"
                    class="bg-white rounded-[2.5rem] p-16 text-center border border-dashed border-slate-300">
                    <div
                        class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center text-4xl text-slate-300 mx-auto mb-6">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <h3 class="text-2xl font-black text-slate-800 mb-2">Belum Ada Soal</h3>
                    <p class="text-slate-500 font-medium mb-8">Ujian ini masih kosong. Silakan klik tombol di pojok
                        kanan atas untuk mulai menyusun pertanyaan.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/katex.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/contrib/auto-render.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('questionIndex', (data) => ({
                questions: data.questions,
                types: [
                    { id: 'single_choice', label: 'Pilgan', icon: 'fa-dot-circle' },
                    { id: 'complex_choice', label: 'PG Kompleks', icon: 'fa-check-square' },
                    { id: 'true_false', label: 'Benar/Salah', icon: 'fa-list-ol' },
                    { id: 'matching', label: 'Menjodohkan', icon: 'fa-exchange-alt' },
                    { id: 'essay', label: 'Isian Singkat', icon: 'fa-keyboard' }
                ],
                renderMath() {
                    if (typeof renderMathInElement !== 'undefined') {
                        renderMathInElement(document.body, { throwOnError: false });
                    }
                },
                deleteQuestion(id) {
                    Swal.fire({ title: 'Hapus Pertanyaan?', text: "Data tidak bisa dikembalikan!", icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444', confirmButtonText: 'Ya, Hapus' })
                        .then(r => {
                            if(r.isConfirmed) {
                                axios.delete(`/admin/exams/{{ $exam->id }}/soal/${id}`)
                                    .then(() => window.location.reload());
                            }
                        });
                },
                formatType(t) { return this.types.find(x => x.id === t)?.label || t; },
                getTypeColor(t) { return { single_choice:'bg-violet-400', complex_choice:'bg-fuchsia-400', matching:'bg-amber-400', true_false:'bg-emerald-400', essay:'bg-blue-400' }[t] || 'bg-slate-300'; },
                getTypeIcon(t) { return this.types.find(x => x.id === t)?.icon || 'fa-question'; },
                getTypeBadge(t) { return { single_choice:'bg-violet-50 text-violet-600 border-violet-100', complex_choice:'bg-fuchsia-50 text-fuchsia-600 border-fuchsia-100', matching:'bg-amber-50 text-amber-600 border-amber-100', true_false:'bg-emerald-50 text-emerald-600 border-emerald-100', essay:'bg-blue-50 text-blue-600 border-blue-100' }[t] || 'bg-slate-50 text-slate-400'; }
            }));
        });
    </script>
</x-app-layout>