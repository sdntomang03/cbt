<x-app-layout>
    {{-- ================= DEPENDENCIES (CSS) ================= --}}
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

        .sun-editor {
            border: none !important;
            font-family: 'Nunito', sans-serif !important;
        }

        .sun-editor .se-toolbar {
            background: #f8fafc !important;
            outline: 1px solid #e2e8f0;
            border-radius: 1.5rem 1.5rem 0 0;
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
    </style>

    {{-- ================= HEADER ================= --}}
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 py-2">
            <div class="flex items-center gap-5">
                <a href="{{ route('admin.exams.index') }}"
                    class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-slate-500 hover:text-indigo-600 shadow-sm border border-slate-100 transition-all hover:-translate-x-1">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div
                    class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-600 flex items-center justify-center shadow-lg shadow-indigo-200 rotate-3">
                    <i class="fas fa-layer-group text-white text-2xl"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span
                            class="px-3 py-1 rounded-full bg-indigo-100 text-indigo-600 text-[10px] font-black uppercase tracking-wider">Manajemen
                            Bank Soal</span>
                    </div>
                    <h2 class="font-black text-2xl md:text-3xl text-slate-800 tracking-tight">{{ $exam->title }}</h2>
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

    {{-- ================= MAIN CONTENT & ALPINE LOGIC ================= --}}
    <div class="py-10 min-h-screen"
        x-data="questionManager({{ $exam->id }}, {{ json_encode($subjects ?? []) }}, {{ json_encode($levels ?? []) }})"
        @open-question-modal.window="openModal()">

        {{-- Konfigurasi Upload Global --}}
        <script>
            window.globalUploadConfig = {
                imageUploadUrl: "{{ route('admin.image.upload') }}",
                imageUploadHeader: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
            };
        </script>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Loading State --}}
            <div x-show="isLoading" class="flex flex-col items-center justify-center py-32" x-transition>
                <div class="w-16 h-16 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
                <p class="text-slate-400 font-bold mt-6 tracking-wide text-sm">Menyelaraskan Database...</p>
            </div>

            {{-- Daftar Soal --}}
            <div x-show="!isLoading" x-cloak class="grid gap-6">
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
                                        <button @click="editQuestion(q)"
                                            class="w-10 h-10 rounded-full bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white transition flex items-center justify-center bounce-active shadow-sm"><i
                                                class="fas fa-pencil-alt"></i></button>
                                        <button @click="deleteQuestion(q.id)"
                                            class="w-10 h-10 rounded-full bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition flex items-center justify-center bounce-active shadow-sm"><i
                                                class="fas fa-trash-alt"></i></button>
                                    </div>
                                </div>

                                {{-- Narasi --}}
                                <div class="prose prose-indigo max-w-none font-bold text-slate-700 leading-relaxed mb-6 __se__katex_container"
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
                                                    :class="opt.is_correct ? 'text-emerald-600' : 'text-slate-400 opacity-50'">
                                                    <i class="fas mt-1 text-sm"
                                                        :class="opt.is_correct ? 'fa-check-circle' : 'fa-times'"></i>
                                                    <div class="text-sm font-bold prose prose-sm prose-p:my-0"
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
                                                    <div class="text-sm font-bold text-slate-600 prose prose-sm prose-p:my-0 flex-1"
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

                                    {{-- Preview Matching --}}
                                    <template x-if="q.type === 'matching' && q.matches">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                            <template x-for="(m, i) in q.matches" :key="i">
                                                <div
                                                    class="bg-white p-2 rounded-lg border border-slate-200 flex items-center justify-between text-xs shadow-sm">
                                                    <span class="font-bold text-slate-600 truncate w-[45%]"
                                                        x-html="m.premise_text.replace(/<[^>]*>?/gm, '')"></span>
                                                    <i class="fas fa-arrow-right text-indigo-300"></i>
                                                    <span class="font-bold text-emerald-600 truncate w-[45%] text-right"
                                                        x-text="m.target_text"></span>
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
                                                    <span x-text="opt.option_text || '-'"></span>
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
                    <p class="text-slate-500 font-medium mb-8">Ujian ini masih kosong. Silakan klik tombol "Buat Soal
                        Baru" di pojok kanan atas untuk mulai menyusun pertanyaan.</p>
                    <button @click="openModal()"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-indigo-200 transition-all bounce-active">Buat
                        Soal Pertama</button>
                </div>
            </div>
        </div>

        {{-- ================= MODAL EDITOR SOAL ================= --}}
        <div x-show="isModalOpen" x-transition.opacity
            class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6" x-cloak>
            <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" @click="closeModal()"></div>

            <div
                class="bg-[#f8fafc] w-full max-w-6xl h-[95vh] flex flex-col rounded-[2.5rem] shadow-2xl relative z-10 border-[6px] border-white overflow-hidden">

                {{-- Header Modal --}}
                <div
                    class="px-8 py-5 bg-white border-b border-slate-100 flex justify-between items-center shrink-0 z-20">
                    <div class="flex items-center gap-4">
                        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-white shadow-lg"
                            :class="isEditMode ? 'bg-amber-500 shadow-amber-200' : 'bg-slate-900 shadow-slate-200'">
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

                {{-- Body Modal --}}
                <div class="flex-1 overflow-y-auto custom-scroll p-6 sm:p-8">

                    {{-- Baris 1: Tipe Soal & Meta --}}
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">
                        {{-- Tipe Soal --}}
                        <div class="lg:col-span-8 space-y-3">
                            <label class="px-2 text-[10px] font-black text-slate-400 uppercase tracking-widest">Tipe
                                Pertanyaan</label>
                            <div class="flex gap-2 overflow-x-auto pb-2 hide-scroll">
                                <template x-for="t in types" :key="t.id">
                                    <button @click="form.type = t.id; resetOptions()"
                                        class="flex items-center gap-2 px-5 py-3 rounded-xl text-xs font-bold transition-all border shrink-0 bounce-active"
                                        :class="form.type === t.id ? 'bg-indigo-600 text-white border-indigo-600 shadow-md' : 'bg-white text-slate-500 border-slate-200 hover:bg-indigo-50'">
                                        <i class="fas text-sm" :class="t.icon"></i> <span x-text="t.label"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- Mapel & Level --}}
                        <div class="lg:col-span-4 flex gap-4">
                            <div class="flex-1">
                                <label
                                    class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Mapel</label>
                                <select x-model="form.subject_id"
                                    class="w-full bg-white border-slate-200 rounded-xl text-sm font-bold text-slate-700 py-3 shadow-sm focus:ring-indigo-500">
                                    <option value="">Umum</option>
                                    <template x-for="s in masterSubjects">
                                        <option :value="s.id" x-text="s.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="flex-1">
                                <label
                                    class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Level</label>
                                <select x-model="form.level_id"
                                    class="w-full bg-white border-slate-200 rounded-xl text-sm font-bold text-slate-700 py-3 shadow-sm focus:ring-indigo-500">
                                    <option value="">Umum</option>
                                    <template x-for="l in masterLevels">
                                        <option :value="l.id" x-text="l.name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Baris 2: Editor Editor --}}
                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">

                        {{-- Kiri: Editor Narasi --}}
                        <div
                            class="bg-white p-1 rounded-[2rem] shadow-sm border border-slate-200 flex flex-col h-[500px]">
                            <div
                                class="bg-slate-50/50 px-6 py-4 rounded-t-[2rem] border-b border-slate-100 flex items-center justify-between shrink-0">
                                <span class="text-xs font-black text-indigo-500 uppercase tracking-widest"><i
                                        class="fas fa-pen-nib mr-2"></i> Narasi Pertanyaan / Teks Bacaan</span>
                            </div>
                            <div class="flex-1 overflow-hidden" wire:ignore x-ignore>
                                <textarea id="main_editor_narasi" class="w-full h-full"></textarea>
                            </div>
                        </div>

                        {{-- Kanan: Form Opsi Dinamis --}}
                        <div
                            class="bg-white p-6 md:p-8 rounded-[2rem] shadow-sm border border-slate-200 h-[500px] flex flex-col">
                            <div class="flex justify-between items-center mb-6 shrink-0">
                                <div>
                                    <h4 class="font-black text-lg text-slate-800">Kunci Jawaban</h4>
                                    <p class="text-xs font-bold text-slate-400 uppercase mt-1">Pengaturan opsi & jawaban
                                        benar</p>
                                </div>
                                <button @click="addOption()"
                                    class="bg-indigo-50 hover:bg-indigo-600 text-indigo-600 hover:text-white px-4 py-2 rounded-xl text-xs font-black transition-all bounce-active flex items-center gap-2">
                                    <i class="fas fa-plus"></i> <span
                                        x-text="form.type === 'essay' ? 'Tambah Variasi' : (form.type === 'true_false' ? 'Tambah Pernyataan' : 'Tambah Baris')"></span>
                                </button>
                            </div>

                            <div class="flex-1 overflow-y-auto custom-scroll pr-2">

                                {{-- TEMPLATE 1: SINGLE & COMPLEX CHOICE --}}
                                <template x-if="['single_choice', 'complex_choice'].includes(form.type)">
                                    <div class="space-y-3">
                                        <template x-for="(opt, index) in form.options" :key="index">
                                            <div class="flex items-start gap-3 p-3 bg-slate-50 border transition-all rounded-xl relative group"
                                                :class="(opt.is_correct == 1 || opt.is_correct === true) ? 'border-emerald-400 shadow-[0_0_15px_rgba(16,185,129,0.15)] bg-emerald-50/30' : 'border-slate-200 hover:border-indigo-300'">

                                                <div class="pt-2">
                                                    <input :type="form.type === 'single_choice' ? 'radio' : 'checkbox'"
                                                        :name="'correct_ans'"
                                                        :checked="opt.is_correct == 1 || opt.is_correct === true"
                                                        @change="toggleCorrect(index)"
                                                        class="w-5 h-5 text-emerald-500 border-slate-300 focus:ring-emerald-500 cursor-pointer shadow-sm">
                                                </div>
                                                <div class="flex-1">
                                                    <textarea x-model="opt.option_text"
                                                        class="w-full text-sm border-slate-200 rounded-lg focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                                                        rows="2" placeholder="Ketik pilihan jawaban..."></textarea>
                                                </div>
                                                <button @click="removeOption(index)"
                                                    class="text-rose-300 hover:text-rose-500 p-2 transition-colors"><i
                                                        class="fas fa-trash"></i></button>
                                            </div>
                                        </template>
                                    </div>
                                </template>

                                {{-- TEMPLATE 2: MATCHING (MENJODOHKAN) --}}
                                <template x-if="form.type === 'matching'">
                                    <div class="space-y-3">
                                        <div
                                            class="flex text-[10px] font-black text-slate-400 uppercase tracking-widest px-2 mb-1">
                                            <div class="flex-1">Soal / Premis (Kiri)</div>
                                            <div class="flex-1 ml-9">Jawaban / Target (Kanan)</div>
                                        </div>
                                        <template x-for="(opt, index) in form.options" :key="index">
                                            <div
                                                class="flex items-center gap-3 p-3 bg-slate-50 border border-slate-200 rounded-xl relative">
                                                <input x-model="opt.premise_text" type="text"
                                                    class="flex-1 text-sm border-slate-200 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    placeholder="Ayam...">
                                                <i class="fas fa-arrow-right text-indigo-300"></i>
                                                <input x-model="opt.target_text" type="text"
                                                    class="flex-1 text-sm border-slate-200 rounded-lg shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                                    placeholder="Berkokok...">
                                                <button @click="removeOption(index)"
                                                    class="text-rose-300 hover:text-rose-500 p-2"><i
                                                        class="fas fa-trash"></i></button>
                                            </div>
                                        </template>
                                    </div>
                                </template>

                                {{-- TEMPLATE 3: TRUE / FALSE (MAJEMUK) --}}
                                <template x-if="form.type === 'true_false'">
                                    <div class="space-y-3">
                                        <div
                                            class="flex text-[10px] font-black text-slate-400 uppercase tracking-widest px-2 mb-1">
                                            <div class="flex-1">Pernyataan</div>
                                            <div class="w-32 text-center">Kunci Jawaban</div>
                                            <div class="w-8"></div>
                                        </div>
                                        <template x-for="(opt, index) in form.options" :key="index">
                                            <div
                                                class="flex items-stretch gap-3 p-3 bg-slate-50 border border-slate-200 rounded-xl relative transition-all hover:border-indigo-300">

                                                <div class="flex-1 flex items-center">
                                                    <textarea x-model="opt.option_text"
                                                        class="w-full text-sm border-slate-200 rounded-lg focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                                                        rows="3" placeholder="Ketik pernyataan di sini..."></textarea>
                                                </div>

                                                <div class="w-32 flex flex-col gap-2 justify-center shrink-0">
                                                    {{-- Tombol Benar --}}
                                                    <button @click="opt.is_correct = 1" type="button"
                                                        class="px-3 py-2 rounded-lg text-xs font-black tracking-widest transition-all shadow-sm flex items-center justify-center gap-1 border-2"
                                                        :class="opt.is_correct == 1 ? 'bg-emerald-500 text-white border-emerald-500' : 'bg-white text-slate-400 border-slate-200 hover:border-emerald-200 hover:text-emerald-500'">
                                                        <i class="fas fa-check"></i> BENAR
                                                    </button>
                                                    {{-- Tombol Salah --}}
                                                    <button @click="opt.is_correct = 0" type="button"
                                                        class="px-3 py-2 rounded-lg text-xs font-black tracking-widest transition-all shadow-sm flex items-center justify-center gap-1 border-2"
                                                        :class="opt.is_correct == 0 ? 'bg-rose-500 text-white border-rose-500' : 'bg-white text-slate-400 border-slate-200 hover:border-rose-200 hover:text-rose-500'">
                                                        <i class="fas fa-times"></i> SALAH
                                                    </button>
                                                </div>

                                                <div class="flex items-center justify-center">
                                                    <button @click="removeOption(index)"
                                                        class="text-rose-300 hover:text-rose-500 transition-colors"><i
                                                            class="fas fa-trash"></i></button>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>

                                {{-- TEMPLATE 4: ESSAY --}}
                                <template x-if="form.type === 'essay'">
                                    <div class="space-y-4">
                                        <div
                                            class="bg-indigo-50 border border-indigo-100 p-4 rounded-xl flex gap-3 text-indigo-700 mb-2">
                                            <i class="fas fa-info-circle mt-0.5"></i>
                                            <p class="text-xs font-bold leading-relaxed">
                                                Masukkan variasi jawaban yang dianggap benar. <br>Misal soal <b>"Ibukota
                                                    Indonesia?"</b>, Anda bisa mengisi: <i>Jakarta</i>, <i>DKI
                                                    Jakarta</i>, <i>Kota Jakarta</i>.
                                            </p>
                                        </div>
                                        <template x-for="(opt, index) in form.options" :key="index">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center font-black text-xs shrink-0"
                                                    x-text="index + 1"></div>
                                                <input x-model="opt.option_text" type="text"
                                                    class="flex-1 text-sm border-slate-200 rounded-lg focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                                                    placeholder="Ketik variasi jawaban benar...">
                                                <button @click="removeOption(index)" x-show="form.options.length > 1"
                                                    class="text-rose-300 hover:text-rose-500 p-2"><i
                                                        class="fas fa-times"></i></button>
                                            </div>
                                        </template>
                                    </div>
                                </template>

                            </div>
                        </div>

                    </div>
                </div>

                {{-- Footer Modal --}}
                <div class="px-8 py-5 bg-white border-t border-slate-100 flex justify-end shrink-0 z-20">
                    <button @click="saveQuestion()" :disabled="isSaving"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-10 py-3.5 rounded-xl font-black shadow-lg shadow-indigo-200 transition-all bounce-active flex items-center gap-3 disabled:opacity-70">
                        <span x-show="isSaving"
                            class="w-5 h-5 border-4 border-white border-t-transparent rounded-full animate-spin"></span>
                        <span x-text="isSaving ? 'Menyimpan ke Database...' : 'Simpan Pertanyaan'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= DEPENDENCIES (JS) ================= --}}
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/katex.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/contrib/auto-render.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/suneditor@latest/dist/suneditor.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('examData', { count: 0 });

            Alpine.data('questionManager', (examId, subjects, levels) => ({
                masterSubjects: subjects || [],
                masterLevels: levels || [],
                questions: [],
                isLoading: true,
                isSaving: false,
                isModalOpen: false,
                isEditMode: false,
                currentId: null,
                editorInstance: null,

                form: { type: 'single_choice', content: '', subject_id: '', level_id: '', options: [] },

                types: [
                    { id: 'single_choice', label: 'Pilgan', icon: 'fa-dot-circle' },
                    { id: 'complex_choice', label: 'PG Kompleks', icon: 'fa-check-square' },
                    { id: 'true_false', label: 'Benar/Salah', icon: 'fa-list-ol' }, // Icon diubah agar mencerminkan list pernyataan
                    { id: 'matching', label: 'Menjodohkan', icon: 'fa-exchange-alt' },
                    { id: 'essay', label: 'Isian Singkat', icon: 'fa-keyboard' }
                ],

                init() {
                    this.fetchQuestions();

                    this.$watch('isModalOpen', value => {
                        if (value) {
                            this.initEditor();
                        } else {
                            setTimeout(() => {
                                if (this.editorInstance) this.editorInstance.setContents('<p><br></p>');
                                this.form.content = '';
                            }, 300);
                        }
                    });
                },

                initEditor() {
                    let attempts = 0;
                    const tryInit = () => {
                        const editorEl = document.getElementById('main_editor_narasi');

                        if (editorEl && editorEl.offsetParent !== null) {
                            if (!this.editorInstance) {
                                this.editorInstance = SUNEDITOR.create(editorEl, {
                                    katex: window.katex,
                                    width: '100%',
                                    height: '100%',
                                    buttonList: [
                                        ['undo', 'redo', 'font', 'fontSize', 'formatBlock'],
                                        ['bold', 'underline', 'italic', 'strike', 'subscript', 'superscript'],
                                        ['fontColor', 'hiliteColor', 'align', 'list', 'table'],
                                        ['image', 'video', 'math'],
                                        ['fullScreen', 'codeView']
                                    ],
                                    imageUploadUrl: window.globalUploadConfig.imageUploadUrl,
                                    imageUploadHeader: window.globalUploadConfig.imageUploadHeader
                                });

                                this.editorInstance.onChange = (contents) => {
                                    this.form.content = contents;
                                };
                            }
                            this.editorInstance.setContents(this.form.content || '<p><br></p>');
                        } else {
                            attempts++;
                            if (attempts < 20 && this.isModalOpen) {
                                setTimeout(tryInit, 50);
                            }
                        }
                    };
                    tryInit();
                },

                renderMath() {
                    if (typeof renderMathInElement !== 'undefined') {
                        renderMathInElement(document.body, {
                            delimiters: [
                                {left: "$$", right: "$$", display: true},
                                {left: "\\[", right: "\\]", display: true},
                                {left: "$", right: "$", display: false},
                                {left: "\\(", right: "\\)", display: false}
                            ],
                            throwOnError: false
                        });
                    }
                },

                fetchQuestions() {
                    this.isLoading = true;
                    axios.get(`/admin/exams/${examId}/questions`)
                        .then(res => {
                            this.questions = res.data.questions;
                            Alpine.store('examData').count = this.questions.length;
                            this.$nextTick(() => this.renderMath());
                        })
                        .finally(() => this.isLoading = false);
                },

                openModal() {
                    this.isEditMode = false;
                    this.currentId = null;
                    this.form = { type: 'single_choice', content: '', subject_id: '', level_id: '', options: [] };
                    this.resetOptions();
                    if (this.editorInstance) this.editorInstance.setContents('<p><br></p>');
                    this.isModalOpen = true;
                },

                closeModal() {
                    this.isModalOpen = false;
                },

                resetOptions() {
                    this.form.options = [];
                    if(this.form.type === 'essay') {
                        this.form.options.push({ option_text: '', is_correct: 1 });
                    }
                    else if (this.form.type === 'true_false') {
                        // PERUBAHAN: Sediakan 3 baris pernyataan kosong default
                        for(let i=0; i<3; i++) {
                            this.form.options.push({ option_text: '', is_correct: 1 });
                        }
                    }
                    else if (this.form.type === 'matching') {
                        for(let i=0; i<3; i++) { this.form.options.push({ premise_text: '', target_text: '' }); }
                    }
                    else {
                        for(let i=0; i<4; i++) { this.form.options.push({ option_text: '', is_correct: (i===0 ? 1 : 0) }); }
                    }
                },

                addOption() {
                    if (this.form.type === 'matching') {
                        this.form.options.push({ premise_text: '', target_text: '' });
                    }
                    else if (['essay', 'true_false'].includes(this.form.type)) {
                        this.form.options.push({ option_text: '', is_correct: 1 });
                    }
                    else {
                        this.form.options.push({ option_text: '', is_correct: 0 });
                    }
                },

                removeOption(index) {
                    this.form.options.splice(index, 1);
                },

                toggleCorrect(index) {
                    if (this.form.type === 'complex_choice') {
                        this.form.options[index].is_correct = !this.form.options[index].is_correct;
                    } else if (this.form.type !== 'true_false') {
                        // Untuk single choice
                        this.form.options.forEach((o, i) => o.is_correct = (i === index ? 1 : 0));
                    }
                },

                saveQuestion() {
                    if(this.editorInstance) {
                        this.form.content = this.editorInstance.getContents();
                    } else {
                        const fallbackEl = document.getElementById('main_editor_narasi');
                        if (fallbackEl) this.form.content = fallbackEl.value;
                    }

                    let rawContent = this.form.content || '';
                    let tempText = rawContent.replace(/<p><br><\/p>/g, '').replace(/<p><\/p>/g, '').trim();

                    if (!tempText) {
                        return Swal.fire({ icon: 'warning', title: 'Oops!', text: 'Isi narasi pertanyaan terlebih dahulu!' });
                    }

                    if (this.form.type === 'matching') {
                        let valid = this.form.options.some(o => o.premise_text && o.target_text);
                        if(!valid) return Swal.fire({ icon: 'warning', title: 'Oops!', text: 'Isi minimal satu pasang soal menjodohkan!' });
                    }

                    if (this.form.type === 'essay') {
                        let valid = this.form.options.some(o => o.option_text.trim() !== '');
                        if(!valid) return Swal.fire({ icon: 'warning', title: 'Oops!', text: 'Isi minimal satu kunci jawaban yang benar!' });
                    }

                    // PERUBAHAN: Validasi minimal ada 1 pernyataan untuk benar/salah
                    if (this.form.type === 'true_false') {
                        let valid = this.form.options.some(o => o.option_text.trim() !== '');
                        if(!valid) return Swal.fire({ icon: 'warning', title: 'Oops!', text: 'Isi minimal satu pernyataan!' });
                    }

                    this.isSaving = true;

                    let payload = {
                        type: this.form.type,
                        content: rawContent,
                        subject_id: this.form.subject_id || null,
                        level_id: this.form.level_id || null,
                        options: this.form.options
                    };

                    const url = this.isEditMode ? `/admin/questions/${this.currentId}` : `/admin/exams/${examId}/questions`;
                    const method = this.isEditMode ? 'put' : 'post';

                    axios[method](url, payload)
                        .then(() => {
                            this.closeModal();
                            this.fetchQuestions();
                            Swal.fire({ icon: 'success', title: 'Tersimpan!', timer: 1500, showConfirmButton: false });
                        })
                        .catch(err => {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: err.response?.data?.message || 'Terjadi kesalahan sistem' });
                        })
                        .finally(() => this.isSaving = false);
                },

                editQuestion(q) {
                    this.isEditMode = true;
                    this.currentId = q.id;

                    let loadedOptions = [];
                    if (q.type === 'matching') {
                        loadedOptions = (q.matches && q.matches.length > 0)
                            ? q.matches.map(m => ({ premise_text: m.premise_text, target_text: m.target_text }))
                            : [{ premise_text: '', target_text: '' }];
                    } else {
                        loadedOptions = (q.options && q.options.length > 0)
                            ? q.options.map(o => ({ option_text: o.option_text, is_correct: o.is_correct }))
                            : [];

                        if (q.type === 'essay' && loadedOptions.length === 0) {
                            loadedOptions = [{ option_text: '', is_correct: 1 }];
                        }
                        // PERUBAHAN: Jika edit T/F tapi kosong (kasus jarang terjadi)
                        if (q.type === 'true_false' && loadedOptions.length === 0) {
                            loadedOptions = [{ option_text: '', is_correct: 1 }];
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
                    Swal.fire({ title: 'Hapus Pertanyaan?', text: "Data tidak bisa dikembalikan!", icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444', confirmButtonText: 'Ya, Hapus' })
                        .then(r => {
                            if(r.isConfirmed) axios.delete(`/admin/questions/${id}`).then(() => {
                                this.fetchQuestions();
                                Swal.fire({ icon: 'success', title: 'Terhapus!', timer: 1000, showConfirmButton: false });
                            });
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