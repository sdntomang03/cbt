<x-app-layout>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>

    <div class="min-h-screen bg-slate-50/50 py-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">

            {{-- ================= HEADER ================= --}}
            <div class="mb-8">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-indigo-200 rotate-3">
                        <i class="fas fa-calculator text-xl -rotate-3"></i>
                    </div>
                    <div>
                        <h2 class="text-3xl font-black text-slate-800 tracking-tight">Generate Ujian Matematika</h2>
                        <p class="text-slate-500 font-bold text-sm mt-1">Atur kesulitan spesifik dan tugas ke banyak
                            siswa sekaligus.</p>
                    </div>
                </div>
            </div>

            @if(session('success'))
            <div
                class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded-r-xl mb-8 font-bold shadow-sm flex items-center gap-3 animate-pulse">
                <i class="fas fa-check-circle text-xl"></i> {{ session('success') }}
            </div>
            @endif

            <form action="{{ route('admin.math.store') }}" method="POST" x-data="mathExamForm()">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 xl:gap-8">

                    {{-- ================= KOLOM KIRI (PILIH PESERTA) ================= --}}

                    <div
                        class="flex-1 overflow-y-auto custom-scrollbar bg-slate-50/50 rounded-xl p-1.5 space-y-3 border border-slate-100/50">

                        {{-- Looping Berdasarkan Grup Kelas --}}
                        <template x-for="(studentsInClass, className) in groupedStudents" :key="className">
                            <div class="space-y-1.5">

                                {{-- Header Grup Kelas & Tombol Pilih 1 Kelas --}}
                                <div
                                    class="flex items-center justify-between px-3 py-2 bg-slate-200/60 rounded-lg sticky top-0 z-10 backdrop-blur-sm">
                                    <span class="text-xs font-black text-slate-700 flex items-center gap-1.5">
                                        <i class="fas fa-layer-group text-slate-400"></i>
                                        <span x-text="className"></span>
                                    </span>
                                    <button type="button" @click="toggleClass(className)"
                                        class="text-[10px] font-black transition flex items-center gap-1 px-2 py-1 rounded hover:bg-slate-300/50"
                                        :class="isClassSelected(className) ? 'text-indigo-600' : 'text-slate-500 hover:text-indigo-600'">
                                        <i class="fas"
                                            :class="isClassSelected(className) ? 'fa-check-square' : 'fa-square'"></i>
                                        <span
                                            x-text="isClassSelected(className) ? 'Batal 1 Kelas' : 'Pilih 1 Kelas'"></span>
                                    </button>
                                </div>

                                {{-- Daftar Siswa di dalam Kelas tersebut --}}
                                <div class="space-y-1 pl-1">
                                    <template x-for="student in studentsInClass" :key="student.id">
                                        <label
                                            class="flex items-center px-3 py-2 bg-white rounded-lg cursor-pointer border-2 transition-all hover:shadow-sm"
                                            :class="selectedStudents.includes(String(student.id)) ? 'border-indigo-500 shadow-indigo-50' : 'border-transparent hover:border-indigo-100'">
                                            <div
                                                class="relative flex items-center justify-center w-4 h-4 mr-3 shrink-0">
                                                <input type="checkbox" name="student_ids[]" :value="student.id"
                                                    x-model="selectedStudents"
                                                    class="peer w-4 h-4 opacity-0 absolute cursor-pointer">
                                                <div
                                                    class="w-4 h-4 rounded border-2 border-slate-300 flex items-center justify-center peer-checked:bg-indigo-500 peer-checked:border-indigo-500 transition-colors">
                                                    <i
                                                        class="fas fa-check text-white text-[8px] opacity-0 peer-checked:opacity-100"></i>
                                                </div>
                                            </div>
                                            <div class="flex flex-col min-w-0">
                                                <span class="font-black text-slate-700 text-xs truncate"
                                                    x-text="student.name"></span>
                                                <div
                                                    class="flex items-center gap-1 text-[9px] font-bold text-slate-400 mt-0.5">
                                                    <span class="truncate"
                                                        x-text="student.school ? student.school.name : 'Pusat'"></span>
                                                </div>
                                            </div>
                                        </label>
                                    </template>
                                </div>

                            </div>
                        </template>

                        {{-- Pesan Kosong --}}
                        <div x-show="Object.keys(groupedStudents).length === 0"
                            class="text-center py-6 text-slate-400 text-xs font-bold" x-cloak>
                            Tidak ada siswa yang sesuai filter.
                        </div>
                    </div>

                    {{-- ================= KOLOM KANAN (SETTING UJIAN & BUILDER SOAL) ================= --}}
                    <div class="lg:col-span-8 flex flex-col gap-6 xl:gap-8">

                        {{-- Judul Ujian --}}
                        <div
                            class="bg-white p-6 sm:p-8 rounded-[2rem] shadow-sm border border-slate-100 relative overflow-hidden">
                            <div class="absolute right-0 top-0 w-32 h-32 bg-indigo-50 rounded-bl-full -z-10"></div>
                            <label class="block font-black text-xl text-slate-800 mb-2">Nama / Kelompok Ujian</label>
                            <p class="text-sm font-bold text-slate-400 mb-4">Misal: Latihan 1, Ulangan Harian, atau
                                Tugas Rumah.</p>
                            <input type="text" name="title" required placeholder="Ketik judul ujian di sini..."
                                class="w-full text-lg font-bold text-slate-700 bg-slate-50 border-slate-200 rounded-xl focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 py-4 px-5 shadow-inner placeholder-slate-300">
                        </div>

                        {{-- ================= BUILDER SOAL DINAMIS ================= --}}
                        <div
                            class="bg-white p-6 sm:p-8 rounded-[2rem] shadow-sm border border-slate-100 flex-1 flex flex-col">
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-6 gap-4">
                                <div>
                                    <h3 class="font-black text-lg text-slate-800 flex items-center gap-2">
                                        <i class="fas fa-cogs text-indigo-500"></i> Aturan & Komposisi Soal
                                    </h3>
                                    <p class="text-xs font-bold text-slate-400 mt-1">Tambahkan kombinasi soal sesuai
                                        kebutuhan.</p>
                                </div>
                                <div
                                    class="bg-indigo-50 text-indigo-600 px-5 py-2.5 rounded-xl font-black text-sm border border-indigo-100 shrink-0 shadow-sm">
                                    Total: <span x-text="totalQuestions" class="text-lg"></span> Soal
                                </div>
                            </div>

                            {{-- Daftar Aturan (Rules) - DESAIN BARU YANG LEBIH PROFESIONAL --}}
                            <div class="space-y-5 mb-6 flex-1">
                                <template x-for="(rule, index) in rules" :key="rule.id">
                                    <div
                                        class="bg-white border-2 border-slate-100 rounded-2xl shadow-sm overflow-hidden transition-all hover:border-indigo-200">

                                        {{-- HEADER RULE --}}
                                        <div
                                            class="bg-slate-50 border-b border-slate-100 px-5 py-3 flex justify-between items-center">
                                            <div class="flex items-center gap-3">
                                                <div class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-black text-[10px]"
                                                    x-text="index + 1"></div>
                                                <span
                                                    class="font-black text-slate-600 text-sm tracking-wide uppercase">Kombinasi
                                                    Soal</span>
                                            </div>

                                            {{-- Tombol Hapus Rule --}}
                                            <button type="button" @click="removeRule(index)" x-show="rules.length > 1"
                                                class="text-slate-400 hover:text-rose-600 hover:bg-rose-50 px-3 py-1.5 rounded-lg transition-colors flex items-center gap-2 text-xs font-bold"
                                                title="Hapus Kombinasi Ini">
                                                <i class="fas fa-trash-alt"></i> <span
                                                    class="hidden sm:inline">Hapus</span>
                                            </button>
                                        </div>

                                        {{-- BODY RULE --}}
                                        <div class="p-5">
                                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                                                {{-- Pilih Operasi --}}
                                                <div>
                                                    <label
                                                        class="block text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2">Operasi</label>
                                                    <select x-model="rule.type" :name="`rules[${index}][type]`"
                                                        class="w-full text-sm font-bold text-slate-700 rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 py-2.5 transition-all cursor-pointer">
                                                        <option value="addition">Penjumlahan (+)</option>
                                                        <option value="subtraction">Pengurangan (-)</option>
                                                        <option value="multiplication">Perkalian (x)</option>
                                                        <option value="division">Pembagian (:)</option>
                                                    </select>
                                                </div>

                                                {{-- Digit 1 --}}
                                                <div>
                                                    <label
                                                        class="block text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2">Angka
                                                        Kiri</label>
                                                    <select x-model="rule.num1" :name="`rules[${index}][num1]`"
                                                        class="w-full text-sm font-bold text-slate-700 rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 py-2.5 transition-all cursor-pointer">
                                                        <option value="1">Pasti 1 Digit</option>
                                                        <option value="2">Pasti 2 Digit</option>
                                                        <option value="2_max">Maks 2 Digit</option>
                                                        <option value="3">Pasti 3 Digit</option>
                                                        <option value="3_max">Maks 3 Digit</option>
                                                        <option value="4">Pasti 4 Digit</option>
                                                    </select>
                                                </div>

                                                {{-- Digit 2 --}}
                                                <div>
                                                    <label
                                                        class="block text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2">Angka
                                                        Kanan</label>
                                                    <select x-model="rule.num2" :name="`rules[${index}][num2]`"
                                                        class="w-full text-sm font-bold text-slate-700 rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 py-2.5 transition-all cursor-pointer">
                                                        <option value="1">Pasti 1 Digit</option>
                                                        <option value="2">Pasti 2 Digit</option>
                                                        <option value="2_max">Maks 2 Digit</option>
                                                        <option value="3">Pasti 3 Digit</option>
                                                        <option value="3_max">Maks 3 Digit</option>
                                                        <option value="4">Pasti 4 Digit</option>
                                                    </select>
                                                </div>

                                                {{-- Jumlah Soal --}}
                                                <div>
                                                    <label
                                                        class="block text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2">Jml
                                                        Soal</label>
                                                    <div class="relative">
                                                        <input type="number" x-model.number="rule.count"
                                                            :name="`rules[${index}][count]`" min="1" max="100" required
                                                            class="w-full text-sm font-bold text-slate-700 rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 py-2.5 pl-4 pr-10 transition-all">
                                                        <span class="absolute right-3 top-2.5 text-slate-300"><i
                                                                class="fas fa-list-ol"></i></span>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Fitur Acak Posisi (Modern Toggle Switch) --}}
                                            <div class="mt-5 pt-4 border-t border-slate-100 flex items-start sm:items-center"
                                                x-show="rule.type === 'addition' || rule.type === 'multiplication'"
                                                x-transition>

                                                <input type="hidden" :name="`rules[${index}][random_pos]`"
                                                    value="false">
                                                <label
                                                    class="relative inline-flex items-start sm:items-center cursor-pointer group mt-0.5 sm:mt-0">
                                                    <input type="checkbox" :name="`rules[${index}][random_pos]`"
                                                        value="true" x-model="rule.random_pos" class="sr-only peer">

                                                    {{-- Switch --}}
                                                    <div
                                                        class="w-8 h-4 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3 after:w-4 after:transition-all peer-checked:bg-indigo-500 shrink-0">
                                                    </div>

                                                    {{-- Teks Label --}}
                                                    <span
                                                        class="ml-2 text-xs font-bold text-slate-600 group-hover:text-indigo-600 transition-colors leading-tight">
                                                        Acak Posisi Kiri & Kanan
                                                        <span
                                                            class="text-slate-400 font-medium text-[10px] block sm:inline mt-1 sm:mt-0 sm:ml-1">
                                                            (Misal: 2+3 bisa menjadi 3+2)
                                                        </span>
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <button type="button" @click="addRule()"
                                class="w-full py-4 mb-8 border-2 border-dashed border-indigo-200 text-indigo-500 font-black rounded-2xl hover:bg-indigo-50 hover:border-indigo-400 transition-all flex items-center justify-center gap-2 group">
                                <i class="fas fa-plus-circle text-lg group-hover:scale-110 transition-transform"></i>
                                Tambah Kombinasi Soal Baru
                            </button>

                            {{-- Input Durasi --}}
                            <div
                                class="bg-amber-50 p-5 rounded-2xl border border-amber-100 flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
                                <div>
                                    <h4 class="font-black text-amber-800 flex items-center gap-2"><i
                                            class="fas fa-hourglass-half"></i> Waktu Pengerjaan</h4>
                                    <p class="text-xs font-bold text-amber-600 mt-1">Tentukan durasi ujian (dalam menit)
                                    </p>
                                </div>
                                <div class="w-full sm:w-40 relative">
                                    <input type="number" name="duration_minutes" value="30" min="1" required
                                        class="w-full rounded-xl border-amber-200 bg-white font-black text-amber-700 focus:ring-2 focus:ring-amber-500 py-3 pl-4 pr-14 shadow-sm text-lg">
                                    <span
                                        class="absolute right-4 top-3.5 text-xs font-black text-amber-400">MENIT</span>
                                </div>
                            </div>

                            {{-- Input Hidden Total Soal --}}
                            <input type="hidden" name="total_questions" :value="totalQuestions">

                            <button type="submit" :disabled="selectedStudents.length === 0 || totalQuestions === 0"
                                class="w-full py-5 rounded-2xl font-black text-lg transition-all duration-300 flex justify-center items-center gap-3 relative overflow-hidden group"
                                :class="(selectedStudents.length === 0 || totalQuestions === 0) ? 'bg-slate-100 text-slate-400 cursor-not-allowed' : 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-xl hover:-translate-y-1'">
                                <div x-show="selectedStudents.length > 0 && totalQuestions > 0"
                                    class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/20 to-transparent group-hover:animate-[shimmer_1.5s_infinite]">
                                </div>
                                <i class="fas fa-rocket relative z-10"
                                    :class="selectedStudents.length > 0 && totalQuestions > 0 ? 'animate-bounce' : ''"></i>
                                <span class="relative z-10" x-text="getSubmitText()"></span>
                            </button>
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function mathExamForm() {
            return {
                students: @json($students),
                selectedSchool: '',
                selectedClass: '',
                search: '',
                selectedStudents: [],

                // STATE UNTUK BUILDER SOAL
                ruleCounter: 1,
                rules: [
                    { id: 1, type: 'addition', num1: '2', num2: '2', count: 5, random_pos: false }
                ],

                init() {
                    this.$watch('selectedSchool', () => {
                        this.selectedClass = '';
                    });

                    this.$watch('availableClasses', (newClasses) => {
                        if (this.selectedClass !== '' && !newClasses.includes(this.selectedClass)) {
                            this.selectedClass = '';
                        }
                    });
                },

                get availableClasses() {
                    let filtered = this.students;

                    if (this.selectedSchool !== '') {
                        filtered = filtered.filter(s => {
                            const sSchool = s.school_id ? String(s.school_id) : '';
                            return sSchool === String(this.selectedSchool);
                        });
                    }

                    const classesArray = filtered.map(s => s.kelas).filter(k => k && String(k).trim() !== '');
                    return [...new Set(classesArray)].sort();
                },

                get filteredStudents() {
                    return this.students.filter(s => {
                        const sSchool = s.school_id ? String(s.school_id) : '';
                        const sClass = s.kelas ? String(s.kelas) : '';

                        const matchSchool = this.selectedSchool === '' || sSchool === String(this.selectedSchool);
                        const matchClass = this.selectedClass === '' || sClass === String(this.selectedClass);
                        const matchName = s.name.toLowerCase().includes(this.search.toLowerCase());

                        return matchSchool && matchClass && matchName;
                    });
                },

                // FITUR BARU: Mengelompokkan Siswa Berdasarkan Kelas
                get groupedStudents() {
                    const groups = {};
                    this.filteredStudents.forEach(student => {
                        // Jika tidak ada nama kelas, masukkan ke "Tanpa Kelas"
                        const className = student.kelas && String(student.kelas).trim() !== '' ? String(student.kelas) : 'Tanpa Kelas';

                        if (!groups[className]) {
                            groups[className] = [];
                        }
                        groups[className].push(student);
                    });

                    // Mengurutkan Object Key agar tampil rapi berdasarkan abjad kelas
                    return Object.keys(groups).sort().reduce((obj, key) => {
                        obj[key] = groups[key];
                        return obj;
                    }, {});
                },

                // Cek apakah seluruh siswa di kelas tertentu sudah tercentang
                isClassSelected(className) {
                    const studentsInClass = this.groupedStudents[className] || [];
                    if (studentsInClass.length === 0) return false;

                    const classStudentIds = studentsInClass.map(s => String(s.id));
                    return classStudentIds.every(id => this.selectedStudents.includes(id));
                },

                // Centang atau hapus centang seluruh siswa di kelas tertentu
                toggleClass(className) {
                    const studentsInClass = this.groupedStudents[className] || [];
                    const classStudentIds = studentsInClass.map(s => String(s.id));

                    if (this.isClassSelected(className)) {
                        // Hapus semua id siswa dari kelas ini di dalam array selectedStudents
                        this.selectedStudents = this.selectedStudents.filter(id => !classStudentIds.includes(id));
                    } else {
                        // Tambahkan id siswa yang belum ada ke dalam selectedStudents
                        const newSelections = classStudentIds.filter(id => !this.selectedStudents.includes(id));
                        this.selectedStudents.push(...newSelections);
                    }
                },

                // Cek Semua Siswa (Global)
                get isAllSelected() {
                    if (this.filteredStudents.length === 0) return false;
                    const visibleIds = this.filteredStudents.map(s => String(s.id));
                    return visibleIds.every(id => this.selectedStudents.includes(id));
                },

                // Centang Semua Siswa (Global)
                toggleSelectAll() {
                    const visibleIds = this.filteredStudents.map(s => String(s.id));
                    if (this.isAllSelected) {
                        this.selectedStudents = this.selectedStudents.filter(id => !visibleIds.includes(id));
                    } else {
                        const newSelections = visibleIds.filter(id => !this.selectedStudents.includes(id));
                        this.selectedStudents.push(...newSelections);
                    }
                },

                // METODE BUILDER SOAL
                addRule() {
                    this.ruleCounter++;
                    this.rules.push({
                        id: this.ruleCounter,
                        type: 'addition',
                        num1: '2',
                        num2: '2',
                        count: 5,
                        random_pos: false
                    });
                },

                removeRule(index) {
                    if (this.rules.length > 1) {
                        this.rules.splice(index, 1);
                    }
                },

                get totalQuestions() {
                    return this.rules.reduce((total, rule) => total + (parseInt(rule.count) || 0), 0);
                },

                getSubmitText() {
                    if (this.selectedStudents.length === 0) return 'Pilih Siswa Terlebih Dahulu';
                    if (this.totalQuestions === 0) return 'Masukkan Minimal 1 Soal';
                    return `Buat ${this.totalQuestions} Soal untuk ${this.selectedStudents.length} Siswa`;
                }
            }
        }
    </script>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #cbd5e1;
        }

        @keyframes shimmer {
            100% {
                transform: translateX(100%);
            }
        }
    </style>
</x-app-layout>