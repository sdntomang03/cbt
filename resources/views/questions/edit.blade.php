<x-app-layout>
    <link href="https://cdn.jsdelivr.net/npm/suneditor@latest/dist/css/suneditor.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/suneditor@latest/dist/suneditor.all.min.js"></script>

    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.exams.questions.index', $exam->id) }}"
                class="text-slate-400 hover:text-indigo-600 transition"><i class="fas fa-arrow-left text-xl"></i></a>
            <div>
                <h2 class="font-black text-2xl text-slate-800">Edit Soal</h2>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Editor Konten Ujian</p>
            </div>
        </div>
    </x-slot>

    <div class="py-10 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <form action="{{ route('admin.exams.questions.store', $exam->id) }}" method="POST" id="questionForm">
            @csrf
            <div x-data="optionManager('single_choice')" class="space-y-8">

                {{-- Bagian Tipe Soal --}}
                <div class="bg-white p-8 rounded-[2.5rem] border-4 border-white shadow-sm">
                    <label class="px-2 text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-4">Tipe
                        Pertanyaan</label>
                    <div class="flex gap-3 overflow-x-auto pb-2">
                        <template x-for="t in [
                            {id:'single_choice', label:'Pilgan', icon:'fa-dot-circle'},
                            {id:'complex_choice', label:'PG Kompleks', icon:'fa-check-square'},
                            {id:'true_false', label:'Benar/Salah', icon:'fa-list-ol'},
                            {id:'matching', label:'Menjodohkan', icon:'fa-exchange-alt'},
                            {id:'essay', label:'Isian Singkat', icon:'fa-keyboard'}
                        ]">
                            <button type="button" @click="type = t.id; resetOptions()"
                                :class="type === t.id ? 'bg-indigo-600 text-white border-indigo-600 shadow-lg' : 'bg-slate-50 text-slate-500 border-slate-100 hover:bg-white'"
                                class="flex items-center gap-3 px-6 py-4 rounded-2xl text-sm font-bold transition-all border shrink-0 bounce-active">
                                <i class="fas" :class="t.icon"></i> <span x-text="t.label"></span>
                            </button>
                        </template>
                        <input type="hidden" name="type" :value="type">
                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
                    {{-- Kiri: Editor --}}
                    <div
                        class="bg-white p-1 rounded-[2.5rem] shadow-sm border border-slate-200 flex flex-col min-h-[500px]">
                        <div
                            class="bg-slate-50/50 px-8 py-4 rounded-t-[2.5rem] border-b border-slate-100 flex items-center justify-between">
                            <span class="text-xs font-black text-indigo-500 uppercase tracking-widest"><i
                                    class="fas fa-pen-nib mr-2"></i> Narasi Pertanyaan</span>
                        </div>
                        <div class="p-4 flex-1">
                            <textarea id="editor_narasi" name="content"></textarea>
                        </div>
                    </div>

                    {{-- Kanan: Kunci Jawaban --}}
                    <div
                        class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-200 min-h-[500px] flex flex-col">
                        <div class="flex justify-between items-center mb-8">
                            <div>
                                <h4 class="font-black text-lg text-slate-800">Kunci Jawaban</h4>
                                <p class="text-xs font-bold text-slate-400 uppercase mt-1">Pengaturan opsi & jawaban
                                    benar</p>
                            </div>
                            <button type="button" @click="addOption()"
                                class="bg-indigo-50 hover:bg-indigo-600 text-indigo-600 hover:text-white px-6 py-3 rounded-2xl text-xs font-black transition-all bounce-active flex items-center gap-2">
                                <i class="fas fa-plus"></i> Tambah Baris
                            </button>
                        </div>

                        <div class="flex-1 overflow-y-auto space-y-4 custom-scroll pr-2">
                            <template x-for="(opt, index) in options" :key="index">
                                <div class="flex items-start gap-4 p-4 bg-slate-50 border transition-all rounded-[1.5rem]"
                                    :class="opt.is_correct == 1 ? 'border-emerald-400 bg-emerald-50/30' : 'border-slate-200'">
                                    <div class="pt-2">
                                        <input :type="type === 'complex_choice' ? 'checkbox' : 'radio'"
                                            :name="type === 'single_choice' || type === 'true_false' ? 'radio_correct' : 'options['+index+'][is_correct]'"
                                            :value="type === 'single_choice' || type === 'true_false' ? index : 1"
                                            :checked="opt.is_correct == 1" @change="updateCorrect(index)"
                                            class="w-6 h-6 text-emerald-500 border-slate-300 focus:ring-emerald-500 cursor-pointer shadow-sm">
                                        <input type="hidden" :name="'options['+index+'][is_correct]'"
                                            :value="opt.is_correct">
                                    </div>
                                    <div class="flex-1">
                                        <template x-if="type === 'matching'">
                                            <div class="flex gap-2">
                                                <input type="text" x-model="opt.premise_text"
                                                    :name="'options['+index+'][premise_text]'"
                                                    class="w-full text-sm border-slate-200 rounded-xl"
                                                    placeholder="Premis...">
                                                <input type="text" x-model="opt.target_text"
                                                    :name="'options['+index+'][target_text]'"
                                                    class="w-full text-sm border-slate-200 rounded-xl"
                                                    placeholder="Target...">
                                            </div>
                                        </template>
                                        <template x-if="type !== 'matching'">
                                            <textarea x-model="opt.option_text"
                                                :name="'options['+index+'][option_text]'"
                                                class="w-full text-sm border-slate-200 rounded-xl focus:ring-indigo-500"
                                                rows="2" placeholder="Teks jawaban..."></textarea>
                                        </template>
                                    </div>
                                    <button type="button" @click="removeOption(index)"
                                        class="text-rose-300 hover:text-rose-500 p-2"><i
                                            class="fas fa-trash"></i></button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-[2.5rem] border border-slate-100 flex justify-end gap-4 shadow-sm">
                    <a href="{{ route('admin.exams.questions.index', $exam->id) }}"
                        class="px-10 py-4 bg-slate-100 text-slate-500 font-black rounded-2xl hover:bg-slate-200 transition">Batal</a>
                    <button type="submit"
                        class="px-12 py-4 bg-indigo-600 text-white font-black rounded-2xl shadow-xl shadow-indigo-200 hover:bg-indigo-700 transition bounce-active">Simpan
                        Pertanyaan</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const editor = SUNEDITOR.create('editor_narasi', {
                width: '100%', height: 'auto', minHeight: '300px',
                buttonList: [['undo', 'redo'], ['font', 'fontSize', 'formatBlock'], ['bold', 'underline', 'italic'], ['fontColor', 'hiliteColor', 'align', 'list', 'table'], ['image', 'math'], ['fullScreen', 'codeView']]
            });
            document.getElementById('questionForm').onsubmit = () => { document.getElementById('editor_narasi').value = editor.getContents(); };
        });

        document.addEventListener('alpine:init', () => {
            Alpine.data('optionManager', (initType) => ({
                type: initType, options: [],
                init() { this.resetOptions(); },
                resetOptions() {
                    this.options = [];
                    if(this.type === 'essay' || this.type === 'true_false') this.options.push({option_text:'', is_correct:1});
                    else if(this.type === 'matching') for(let i=0; i<3; i++) this.options.push({premise_text:'', target_text:''});
                    else for(let i=0; i<4; i++) this.options.push({option_text:'', is_correct: i===0?1:0});
                },
                addOption() {
                    if(this.type === 'matching') this.options.push({premise_text:'', target_text:''});
                    else this.options.push({option_text:'', is_correct:0});
                },
                removeOption(i) { if(this.options.length > 1) this.options.splice(i, 1); },
                updateCorrect(index) {
                    if(this.type === 'complex_choice') this.options[index].is_correct = this.options[index].is_correct == 1 ? 0 : 1;
                    else this.options.forEach((o, i) => o.is_correct = (i === index ? 1 : 0));
                }
            }));
        });
    </script>
</x-app-layout>