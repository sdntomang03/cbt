<x-app-layout>
    {{-- CSS & JS SunEditor --}}
    <link href="https://cdn.jsdelivr.net/npm/suneditor@latest/dist/css/suneditor.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/suneditor@latest/dist/suneditor.all.min.js"></script>

    <x-slot name="header">
        <h2 class="font-black text-2xl text-slate-800">Buat Soal Baru</h2>
    </x-slot>

    <div class="py-10 max-w-7xl mx-auto sm:px-6 lg:px-8">
        {{-- Form Standar Laravel --}}
        <form action="{{ route('admin.exams.questions.store', $exam->id) }}" method="POST" id="questionForm">
            @csrf

            {{-- AlpineJS untuk mengelola Opsi Dinamis --}}
            <div x-data="optionManager('single_choice')" class="grid grid-cols-1 lg:grid-cols-12 gap-8">

                {{-- KIRI: Editor Teks --}}
                <div class="lg:col-span-7 space-y-6">
                    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Pilih Tipe Soal</label>
                        <select name="type" x-model="type" @change="resetOptions()"
                            class="w-full border-slate-300 rounded-xl mb-6">
                            <option value="single_choice">Pilihan Ganda</option>
                            <option value="complex_choice">PG Kompleks</option>
                            <option value="true_false">Benar/Salah</option>
                            <option value="matching">Menjodohkan</option>
                            <option value="essay">Isian / Essay</option>
                        </select>

                        <label class="block text-sm font-bold text-slate-700 mb-2">Narasi Soal</label>
                        <div wire:ignore>
                            <textarea id="editor_content" name="content"></textarea>
                        </div>
                    </div>
                </div>

                {{-- KANAN: Form Opsi Jawaban --}}
                <div class="lg:col-span-5">
                    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="font-bold text-lg">Pengaturan Jawaban</h3>
                            <button type="button" @click="addOption()"
                                class="text-indigo-600 bg-indigo-50 px-3 py-1 rounded-lg text-sm font-bold">+
                                Tambah</button>
                        </div>

                        <div class="space-y-4">
                            <template x-for="(opt, index) in options" :key="index">
                                <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl flex gap-3 items-start">

                                    {{-- Kolom Kunci Jawaban (Radio/Checkbox) --}}
                                    <div x-show="type !== 'matching'">
                                        <input type="hidden" :name="'options['+index+'][is_correct]'" value="0">
                                        <input
                                            :type="type === 'single_choice' || type === 'true_false' ? 'radio' : 'checkbox'"
                                            :name="type === 'single_choice' ? 'options[radio][is_correct]' : 'options['+index+'][is_correct]'"
                                            value="1" :checked="opt.is_correct == 1"
                                            @change="if(type === 'single_choice') { options.forEach((o, i) => o.is_correct = (i === index ? 1 : 0)); } else { opt.is_correct = $event.target.checked ? 1 : 0 }"
                                            class="w-5 h-5 text-emerald-500 border-slate-300 mt-2">
                                    </div>

                                    {{-- Kolom Teks Opsi --}}
                                    <div class="flex-1 space-y-2">
                                        <template x-if="type === 'matching'">
                                            <div class="flex items-center gap-2">
                                                <input type="text" x-model="opt.premise_text"
                                                    :name="'options['+index+'][premise_text]'"
                                                    class="w-full text-sm border-slate-200 rounded-lg"
                                                    placeholder="Soal/Premis...">
                                                <i class="fas fa-arrow-right text-slate-400"></i>
                                                <input type="text" x-model="opt.target_text"
                                                    :name="'options['+index+'][target_text]'"
                                                    class="w-full text-sm border-slate-200 rounded-lg"
                                                    placeholder="Jawaban...">
                                            </div>
                                        </template>
                                        <template x-if="type !== 'matching'">
                                            <textarea x-model="opt.option_text"
                                                :name="'options['+index+'][option_text]'"
                                                class="w-full text-sm border-slate-200 rounded-lg" rows="2"
                                                placeholder="Ketik pilihan jawaban..."></textarea>
                                        </template>
                                    </div>

                                    <button type="button" @click="removeOption(index)"
                                        class="text-rose-400 hover:text-rose-600 mt-2"><i
                                            class="fas fa-trash"></i></button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <a href="{{ route('admin.exams.questions.index', $exam->id) }}"
                            class="px-6 py-3 bg-slate-200 text-slate-700 font-bold rounded-xl">Batal</a>
                        <button type="submit"
                            class="px-8 py-3 bg-indigo-600 text-white font-bold rounded-xl shadow-lg hover:bg-indigo-700">Simpan
                            Soal</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        // Init SunEditor Standar
        document.addEventListener('DOMContentLoaded', function() {
            const editor = SUNEDITOR.create('editor_content', {
                width: '100%', height: '300px',
                buttonList: [
                    ['undo', 'redo'], ['bold', 'underline', 'italic'],
                    ['align', 'list', 'table'], ['image', 'math'], ['fullScreen']
                ]
            });

            // Saat form disubmit, ambil konten dari editor ke textarea
            document.getElementById('questionForm').addEventListener('submit', function() {
                document.getElementById('editor_content').value = editor.getContents();
            });
        });

        // AlpineJS Form Logic
        document.addEventListener('alpine:init', () => {
            Alpine.data('optionManager', (initialType) => ({
                type: initialType,
                options: [],

                init() { this.resetOptions(); },

                resetOptions() {
                    this.options = [];
                    if(this.type === 'essay') { this.options.push({ option_text: '', is_correct: 1 }); }
                    else if (this.type === 'true_false') { this.options.push({ option_text: '', is_correct: 1 }, { option_text: '', is_correct: 1 }); }
                    else if (this.type === 'matching') { for(let i=0; i<3; i++) this.options.push({ premise_text: '', target_text: '' }); }
                    else { for(let i=0; i<4; i++) this.options.push({ option_text: '', is_correct: (i===0 ? 1 : 0) }); }
                },

                addOption() {
                    if (this.type === 'matching') this.options.push({ premise_text: '', target_text: '' });
                    else if (['essay', 'true_false'].includes(this.type)) this.options.push({ option_text: '', is_correct: 1 });
                    else this.options.push({ option_text: '', is_correct: 0 });
                },

                removeOption(index) { this.options.splice(index, 1); }
            }));
        });
    </script>
</x-app-layout>