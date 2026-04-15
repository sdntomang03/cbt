<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-5 py-2">
            <a href="{{ route('admin.exams.soal.index', $exam->id) }}"
                class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-slate-500 hover:text-indigo-600 shadow-sm border border-slate-100 transition-all hover:-translate-x-1">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="font-black text-2xl md:text-3xl text-slate-800 tracking-tight">Edit Soal</h2>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $exam->title }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-10 max-w-7xl mx-auto sm:px-6 lg:px-8" x-data="questionEditor({
            submitUrl: '{{ route('admin.exams.soal.update', [$exam->id, $soal->id]) }}',
            redirectUrl: '{{ route('admin.exams.soal.index', $exam->id) }}',
            subjects: {{ $subjects->toJson() }},
            levels: {{ $levels->toJson() }},
            initialData: {{ $soal->toJson() }},
            isEdit: true
        })">

        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 p-8">

            {{-- Tipe & Meta --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">
                <div class="lg:col-span-8 space-y-3">
                    <label class="px-2 text-[10px] font-black text-slate-400 uppercase tracking-widest">Tipe
                        Pertanyaan</label>
                    <div class="flex gap-2 overflow-x-auto pb-2 custom-scroll">
                        <template x-for="t in types" :key="t.id">
                            <button @click="form.type = t.id; resetOptions()"
                                class="flex items-center gap-2 px-5 py-3 rounded-xl text-xs font-bold transition-all border shrink-0"
                                :class="form.type === t.id ? 'bg-indigo-600 text-white border-indigo-600 shadow-md' : 'bg-white text-slate-500 border-slate-200 hover:bg-indigo-50'">
                                <i class="fas text-sm" :class="t.icon"></i>
                                <span x-text="t.label"></span>
                            </button>
                        </template>
                    </div>
                </div>
                <div class="lg:col-span-4 flex gap-4">
                    {{-- Dropdown Mapel --}}
                    <div class="flex-1">
                        <label
                            class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Mapel</label>
                        {{-- x-model tetap ada agar Alpine tahu nilainya --}}
                        <select x-model="form.subject_id"
                            class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-bold text-slate-700 py-3 px-4 focus:ring-indigo-500 cursor-pointer">
                            <option value="">Umum</option>
                            {{-- KUNCI: Gunakan Blade @foreach agar opsi sudah siap sebelum Alpine berjalan --}}
                            @foreach($subjects as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Dropdown Level --}}
                    <div class="flex-1">
                        <label
                            class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Level</label>
                        <select x-model="form.level_id"
                            class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-bold text-slate-700 py-3 px-4 focus:ring-indigo-500 cursor-pointer">
                            <option value="">Umum</option>
                            {{-- KUNCI: Gunakan Blade @foreach --}}
                            @foreach($levels as $l)
                            <option value="{{ $l->id }}">{{ $l->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">

                {{-- Editor Narasi --}}
                <div class="bg-white p-1 rounded-[2rem] shadow-sm border border-slate-200 flex flex-col h-[600px]">
                    <div
                        class="bg-slate-50/50 px-6 py-4 rounded-t-[2rem] border-b border-slate-100 flex items-center justify-between">
                        <span class="text-xs font-black text-indigo-500 uppercase tracking-widest">
                            <i class="fas fa-pen-nib mr-2"></i> Narasi Pertanyaan
                        </span>
                    </div>
                    <div class="flex-1 flex flex-col" x-ignore>
                        <div id="editorNarasi" class="flex-1 bg-white" style="min-height: 300px;"></div>
                    </div>
                </div>

                {{-- Kunci Jawaban Dinamis --}}
                <div
                    class="bg-white p-6 md:p-8 rounded-[2rem] shadow-sm border border-slate-200 h-[600px] flex flex-col">
                    <div class="flex justify-between items-center mb-6 shrink-0">
                        <h4 class="font-black text-lg text-slate-800">Kunci Jawaban</h4>
                        <button @click="addOption()"
                            class="bg-indigo-50 hover:bg-indigo-600 text-indigo-600 hover:text-white px-4 py-2 rounded-xl text-xs font-black transition-all flex items-center gap-2">
                            <i class="fas fa-plus"></i>
                            <span x-text="form.type === 'essay' ? 'Tambah Variasi' : 'Tambah Baris'"></span>
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto pr-2 custom-scroll">

                        {{-- PG & PG Kompleks --}}
                        <template x-if="['single_choice', 'complex_choice'].includes(form.type)">
                            <div class="space-y-3">
                                <template x-for="(opt, index) in form.options" :key="index">
                                    <div class="flex items-start gap-3 p-3 bg-slate-50 border rounded-xl transition-all"
                                        :class="opt.is_correct ? 'border-emerald-400 bg-emerald-50/30' : 'border-slate-200'">
                                        <div class="pt-2">
                                            <input :type="form.type === 'single_choice' ? 'radio' : 'checkbox'"
                                                :checked="opt.is_correct" @change="toggleCorrect(index)"
                                                name="correct_ans"
                                                class="w-5 h-5 text-emerald-500 border-slate-300 focus:ring-emerald-500 cursor-pointer">
                                        </div>
                                        <div class="option-editor-wrap flex-1" :data-opt-id="'opt-' + index">
                                            <div x-ignore>
                                                <div class="quill-option-target"></div>
                                            </div>
                                        </div>
                                        <button @click="removeOption(index)"
                                            class="text-rose-400 hover:text-rose-600 p-2 transition">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </template>

                        {{-- Menjodohkan --}}
                        <template x-if="form.type === 'matching'">
                            <div class="space-y-3">
                                <template x-for="(opt, index) in form.options" :key="index">
                                    <div
                                        class="flex items-start gap-3 p-3 bg-slate-50 border border-slate-200 rounded-xl transition-all hover:border-indigo-300">
                                        <div class="option-editor-wrap flex-1"
                                            :data-opt-id="'opt-' + index + '-premise'">
                                            <div x-ignore>
                                                <div class="quill-option-target"></div>
                                            </div>
                                        </div>
                                        <div class="pt-3"><i class="fas fa-arrow-right text-indigo-300"></i></div>
                                        <div class="option-editor-wrap flex-1"
                                            :data-opt-id="'opt-' + index + '-target'">
                                            <div x-ignore>
                                                <div class="quill-option-target"></div>
                                            </div>
                                        </div>
                                        <button @click="removeOption(index)"
                                            class="text-rose-400 hover:text-rose-600 p-2 transition pt-3">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </template>

                        {{-- Benar Salah --}}
                        <template x-if="form.type === 'true_false'">
                            <div class="space-y-3">
                                <template x-for="(opt, index) in form.options" :key="index">
                                    <div
                                        class="flex items-start gap-3 p-3 bg-slate-50 border border-slate-200 rounded-xl transition-all hover:border-indigo-300">
                                        <div class="option-editor-wrap flex-1" :data-opt-id="'opt-' + index">
                                            <div x-ignore>
                                                <div class="quill-option-target"></div>
                                            </div>
                                        </div>
                                        <div class="flex flex-col gap-2 pt-1">
                                            <button @click="opt.is_correct = 1"
                                                :class="opt.is_correct == 1 ? 'bg-emerald-500 text-white border-emerald-500' : 'bg-white text-slate-400 border-slate-200 hover:border-emerald-300'"
                                                class="px-3 py-1.5 text-[10px] font-black tracking-widest rounded-lg border transition shadow-sm">BENAR</button>
                                            <button @click="opt.is_correct = 0"
                                                :class="opt.is_correct == 0 ? 'bg-rose-500 text-white border-rose-500' : 'bg-white text-slate-400 border-slate-200 hover:border-rose-300'"
                                                class="px-3 py-1.5 text-[10px] font-black tracking-widest rounded-lg border transition shadow-sm">SALAH</button>
                                        </div>
                                        <button @click="removeOption(index)"
                                            class="text-rose-400 hover:text-rose-600 p-2 transition pt-2">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </template>

                        {{-- Essay / Isian Singkat --}}
                        <template x-if="form.type === 'essay'">
                            <div class="space-y-3">
                                <template x-for="(opt, index) in form.options" :key="index">
                                    <div
                                        class="flex items-start gap-3 p-3 bg-slate-50 border border-slate-200 rounded-xl transition-all hover:border-indigo-300">
                                        <div class="option-editor-wrap flex-1" :data-opt-id="'opt-' + index">
                                            <div x-ignore>
                                                <div class="quill-option-target"></div>
                                            </div>
                                        </div>
                                        <button @click="removeOption(index)"
                                            class="text-rose-400 hover:text-rose-600 p-2 transition pt-2">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </template>

                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <button @click="saveQuestion()" :disabled="isSaving"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-10 py-3.5 rounded-xl font-black shadow-lg shadow-indigo-200 transition-all flex items-center gap-3">
                    <span x-show="isSaving"
                        class="w-5 h-5 border-4 border-white border-t-transparent rounded-full animate-spin"></span>
                    <span x-text="isSaving ? 'Menyimpan...' : 'Perbarui Pertanyaan'"></span>
                </button>
            </div>
        </div>
    </div>

    @include('soal.partials.scripts')
</x-app-layout>