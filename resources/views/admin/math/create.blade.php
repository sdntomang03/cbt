<x-app-layout>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>

    <div class="min-h-screen bg-slate-50 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">

            {{-- ================= HEADER ================= --}}
            <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-md">
                        <i class="fas fa-calculator text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Generate Ujian Matematika</h2>
                        <p class="text-slate-500 text-sm mt-0.5">Buat komposisi soal dinamis dan tugaskan ke siswa.</p>
                    </div>
                </div>
            </div>

            @if(session('success'))
            <div
                class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center gap-3">
                <i class="fas fa-check-circle text-lg"></i>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            @endif

            <form action="{{ route('admin.math.store') }}" method="POST" x-data="mathExamForm()">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                    {{-- ================= KOLOM KIRI (PILIH PESERTA) ================= --}}
                    <div class="lg:col-span-4 flex flex-col h-[600px] lg:h-[800px]">
                        <div
                            class="bg-white rounded-2xl shadow-sm border border-slate-200 flex-1 flex flex-col overflow-hidden">

                            {{-- Header & Filter --}}
                            <div class="p-5 border-b border-slate-100 bg-slate-50/50">
                                <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                                    <i class="fas fa-users text-indigo-500"></i> Pilih Peserta
                                </h3>

                                <div class="space-y-3">
                                    <div class="relative">
                                        <select x-model="selectedSchool"
                                            class="w-full pl-3 pr-8 py-2 rounded-lg border-slate-200 text-sm text-slate-700 focus:ring-indigo-500 focus:border-indigo-500 appearance-none">
                                            <option value="">-- Semua Sekolah --</option>
                                            @foreach($schools as $school)
                                            <option value="{{ $school->id }}">{{ $school->name }}</option>
                                            @endforeach
                                        </select>
                                        <i
                                            class="fas fa-chevron-down absolute right-3 top-3 text-slate-400 text-[10px] pointer-events-none"></i>
                                    </div>

                                    <div class="relative">
                                        <select x-model="selectedClass" :disabled="availableClasses.length === 0"
                                            class="w-full pl-3 pr-8 py-2 rounded-lg border-slate-200 text-sm text-slate-700 focus:ring-indigo-500 focus:border-indigo-500 appearance-none disabled:bg-slate-100 disabled:text-slate-400">
                                            <option value="">-- Semua Kelas --</option>
                                            <template x-for="kelas in availableClasses" :key="kelas">
                                                <option :value="kelas" x-text="kelas"></option>
                                            </template>
                                        </select>
                                        <i
                                            class="fas fa-chevron-down absolute right-3 top-3 text-slate-400 text-[10px] pointer-events-none"></i>
                                    </div>

                                    <div class="relative">
                                        <i class="fas fa-search absolute left-3 top-2.5 text-slate-400 text-sm"></i>
                                        <input type="text" x-model="search" placeholder="Cari nama siswa..."
                                            class="w-full pl-9 pr-3 py-2 rounded-lg border-slate-200 text-sm text-slate-700 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                </div>
                            </div>

                            {{-- Aksi Pilih Semua --}}
                            <div class="px-5 py-3 flex justify-between items-center border-b border-slate-100 bg-white">
                                <div class="text-sm font-semibold text-slate-600">
                                    <span class="text-indigo-600" x-text="selectedStudents.length"></span> Terpilih
                                </div>
                                <button type="button" @click="toggleSelectAll()"
                                    class="text-xs font-semibold text-slate-500 hover:text-indigo-600 flex items-center gap-1.5 transition-colors">
                                    <i class="fas" :class="isAllSelected ? 'fa-check-square' : 'fa-square'"></i>
                                    <span x-text="isAllSelected ? 'Batal Semua' : 'Pilih Semua'"></span>
                                </button>
                            </div>

                            {{-- Daftar Siswa --}}
                            <div class="flex-1 overflow-y-auto custom-scrollbar p-3 space-y-3 bg-slate-50/50">
                                <template x-for="(studentsInClass, className) in groupedStudents" :key="className">
                                    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">

                                        {{-- Header Kelas --}}
                                        <div
                                            class="bg-slate-100 px-3 py-2 flex justify-between items-center sticky top-0 z-10 border-b border-slate-200">
                                            <span class="text-xs font-bold text-slate-700 flex items-center gap-2">
                                                <i class="fas fa-layer-group text-slate-400"></i>
                                                <span x-text="className"></span>
                                            </span>
                                            <button type="button" @click="toggleClass(className)"
                                                class="text-[10px] font-semibold text-slate-500 hover:text-indigo-600 flex items-center gap-1 bg-white px-2 py-1 rounded border border-slate-200 hover:border-indigo-200 transition-colors">
                                                <i class="fas"
                                                    :class="isClassSelected(className) ? 'fa-check text-indigo-500' : 'fa-check text-transparent'"></i>
                                                <span
                                                    x-text="isClassSelected(className) ? 'Batal 1 Kelas' : 'Pilih 1 Kelas'"></span>
                                            </button>
                                        </div>

                                        {{-- List Item Siswa --}}
                                        <div class="divide-y divide-slate-100">
                                            <template x-for="student in studentsInClass" :key="student.id">
                                                <label
                                                    class="flex items-center px-3 py-2.5 cursor-pointer hover:bg-slate-50 transition-colors"
                                                    :class="selectedStudents.includes(String(student.id)) ? 'bg-indigo-50/30' : ''">
                                                    <div
                                                        class="relative flex items-center justify-center w-4 h-4 mr-3 shrink-0">
                                                        <input type="checkbox" name="student_ids[]" :value="student.id"
                                                            x-model="selectedStudents"
                                                            class="peer w-4 h-4 opacity-0 absolute cursor-pointer">
                                                        <div
                                                            class="w-4 h-4 rounded border border-slate-300 flex items-center justify-center peer-checked:bg-indigo-500 peer-checked:border-indigo-500 bg-white transition-colors">
                                                            <i
                                                                class="fas fa-check text-white text-[9px] opacity-0 peer-checked:opacity-100"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex flex-col min-w-0">
                                                        <span class="font-semibold text-slate-700 text-sm truncate"
                                                            x-text="student.name"></span>
                                                        <span class="text-[10px] text-slate-400 truncate mt-0.5"
                                                            x-text="student.school ? student.school.name : 'Pusat'"></span>
                                                    </div>
                                                </label>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                <div x-show="Object.keys(groupedStudents).length === 0"
                                    class="text-center py-10 text-slate-400 text-sm font-medium" x-cloak>
                                    Tidak ada peserta ditemukan.
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ================= KOLOM KANAN (SETTING UJIAN & BUILDER) ================= --}}
                    <div class="lg:col-span-8 flex flex-col gap-6">

                        {{-- Panel 1: Info Dasar --}}
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                            <h3 class="font-bold text-lg text-slate-800 mb-4 border-b border-slate-100 pb-3">Informasi
                                Ujian</h3>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                                <div class="md:col-span-2">
                                    <label
                                        class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Nama
                                        / Judul Ujian</label>
                                    <input type="text" name="title" required placeholder="Cth: Ulangan Harian 1"
                                        class="w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Durasi
                                        (Menit)</label>
                                    <div class="relative">
                                        <input type="number" name="duration_minutes" value="30" min="1" required
                                            class="w-full text-sm rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 pl-4 pr-12">
                                        <span
                                            class="absolute right-4 top-2.5 text-xs font-bold text-slate-400 pointer-events-none">MNT</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Panel 2: Builder Soal --}}
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex-1 flex flex-col">

                            <div class="flex justify-between items-center border-b border-slate-100 pb-4 mb-5">
                                <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2">
                                    <i class="fas fa-cogs text-indigo-500"></i> Komposisi Soal
                                </h3>
                                <div
                                    class="bg-indigo-50 border border-indigo-100 text-indigo-700 px-4 py-1.5 rounded-lg text-sm font-bold">
                                    Total: <span x-text="totalQuestions" class="text-lg"></span> Soal
                                </div>
                            </div>

                            {{-- Daftar Aturan (Rules) --}}
                            <div class="space-y-4 mb-6 flex-1">
                                <template x-for="(rule, index) in rules" :key="rule.id">
                                    <div
                                        class="bg-white border border-slate-200 rounded-xl shadow-sm hover:border-indigo-300 transition-colors relative">

                                        {{-- Header Rule --}}
                                        <div
                                            class="bg-slate-50 px-4 py-3 border-b border-slate-200 flex justify-between items-center rounded-t-xl">
                                            <span class="font-bold text-sm text-slate-700 flex items-center gap-2">
                                                <span
                                                    class="w-6 h-6 bg-white border border-slate-200 rounded-md flex items-center justify-center text-xs text-indigo-600"
                                                    x-text="index + 1"></span>
                                                Blok Aturan Soal
                                            </span>

                                            <button type="button" @click="removeRule(index)" x-show="rules.length > 1"
                                                class="text-rose-500 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 w-7 h-7 rounded-md flex items-center justify-center transition-colors"
                                                title="Hapus Aturan">
                                                <i class="fas fa-trash-alt text-xs"></i>
                                            </button>
                                        </div>

                                        {{-- Body Rule (Grid 4 Kolom) --}}
                                        <div class="p-4">
                                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                                                {{-- Operasi --}}
                                                <div>
                                                    <label
                                                        class="block text-xs font-semibold text-slate-500 mb-1">Operasi
                                                        Hitung</label>
                                                    <select x-model="rule.type" :name="`rules[${index}][type]`"
                                                        class="w-full text-sm rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                                                        <option value="addition">Penjumlahan (+)</option>
                                                        <option value="subtraction">Pengurangan (-)</option>
                                                        <option value="multiplication">Perkalian (x)</option>
                                                        <option value="division">Pembagian (:)</option>
                                                    </select>
                                                </div>

                                                {{-- Angka Kiri --}}
                                                <div>
                                                    <label class="block text-xs font-semibold text-slate-500 mb-1">Angka
                                                        Kiri</label>
                                                    <select x-model="rule.num1" :name="`rules[${index}][num1]`"
                                                        class="w-full text-sm rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                                                        <option value="1">Pasti 1 Digit</option>
                                                        <option value="2">Pasti 2 Digit</option>
                                                        <option value="2_max">Maks 2 Digit</option>
                                                        <option value="3">Pasti 3 Digit</option>
                                                        <option value="3_max">Maks 3 Digit</option>
                                                    </select>
                                                </div>

                                                {{-- Angka Kanan --}}
                                                <div>
                                                    <label class="block text-xs font-semibold text-slate-500 mb-1">Angka
                                                        Kanan</label>
                                                    <select x-model="rule.num2" :name="`rules[${index}][num2]`"
                                                        class="w-full text-sm rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                                                        <option value="1">Pasti 1 Digit</option>
                                                        <option value="2">Pasti 2 Digit</option>
                                                        <option value="2_max">Maks 2 Digit</option>
                                                        <option value="3">Pasti 3 Digit</option>
                                                        <option value="3_max">Maks 3 Digit</option>
                                                    </select>
                                                </div>

                                                {{-- Jumlah Soal --}}
                                                <div>
                                                    <label
                                                        class="block text-xs font-semibold text-slate-500 mb-1">Jumlah
                                                        Soal</label>
                                                    <input type="number" x-model.number="rule.count"
                                                        :name="`rules[${index}][count]`" min="1" max="100" required
                                                        class="w-full text-sm rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                                                </div>
                                            </div>

                                            {{-- Fitur Acak Posisi (Checkbox Simple) --}}
                                            <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-end"
                                                x-show="rule.type === 'addition' || rule.type === 'multiplication'"
                                                x-transition>
                                                <input type="hidden" :name="`rules[${index}][random_pos]`"
                                                    value="false">
                                                <label class="flex items-center cursor-pointer group">
                                                    <input type="checkbox" :name="`rules[${index}][random_pos]`"
                                                        value="true" x-model="rule.random_pos"
                                                        class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 cursor-pointer">
                                                    <span
                                                        class="ml-2 text-xs font-medium text-slate-600 group-hover:text-indigo-600">
                                                        Acak posisi kiri/kanan (Misal: 2+3 menjadi 3+2)
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <button type="button" @click="addRule()"
                                class="w-full py-3 mb-6 bg-slate-50 border border-slate-200 text-indigo-600 font-semibold rounded-xl hover:bg-indigo-50 hover:border-indigo-200 transition-colors flex items-center justify-center gap-2">
                                <i class="fas fa-plus"></i> Tambah Aturan Soal Baru
                            </button>

                            {{-- Input Hidden Total Soal --}}
                            <input type="hidden" name="total_questions" :value="totalQuestions">

                            {{-- Tombol Submit Utama --}}
                            <button type="submit" :disabled="selectedStudents.length === 0 || totalQuestions === 0"
                                class="w-full py-4 rounded-xl font-bold text-white shadow-md transition-all flex justify-center items-center gap-2 mt-auto"
                                :class="(selectedStudents.length === 0 || totalQuestions === 0) ? 'bg-slate-300 cursor-not-allowed shadow-none' : 'bg-indigo-600 hover:bg-indigo-700'">
                                <i class="fas fa-paper-plane"></i>
                                <span x-text="getSubmitText()"></span>
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

                ruleCounter: 1,
                rules: [
                    { id: 1, type: 'addition', num1: '2', num2: '2', count: 10, random_pos: false }
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

                get groupedStudents() {
                    const groups = {};
                    this.filteredStudents.forEach(student => {
                        const className = student.kelas && String(student.kelas).trim() !== '' ? String(student.kelas) : 'Tanpa Kelas';
                        if (!groups[className]) groups[className] = [];
                        groups[className].push(student);
                    });

                    return Object.keys(groups).sort().reduce((obj, key) => {
                        obj[key] = groups[key];
                        return obj;
                    }, {});
                },

                isClassSelected(className) {
                    const studentsInClass = this.groupedStudents[className] || [];
                    if (studentsInClass.length === 0) return false;
                    const classStudentIds = studentsInClass.map(s => String(s.id));
                    return classStudentIds.every(id => this.selectedStudents.includes(id));
                },

                toggleClass(className) {
                    const studentsInClass = this.groupedStudents[className] || [];
                    const classStudentIds = studentsInClass.map(s => String(s.id));

                    if (this.isClassSelected(className)) {
                        this.selectedStudents = this.selectedStudents.filter(id => !classStudentIds.includes(id));
                    } else {
                        const newSelections = classStudentIds.filter(id => !this.selectedStudents.includes(id));
                        this.selectedStudents.push(...newSelections);
                    }
                },

                get isAllSelected() {
                    if (this.filteredStudents.length === 0) return false;
                    const visibleIds = this.filteredStudents.map(s => String(s.id));
                    return visibleIds.every(id => this.selectedStudents.includes(id));
                },

                toggleSelectAll() {
                    const visibleIds = this.filteredStudents.map(s => String(s.id));
                    if (this.isAllSelected) {
                        this.selectedStudents = this.selectedStudents.filter(id => !visibleIds.includes(id));
                    } else {
                        const newSelections = visibleIds.filter(id => !this.selectedStudents.includes(id));
                        this.selectedStudents.push(...newSelections);
                    }
                },

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
                    if (this.selectedStudents.length === 0) return 'Pilih Peserta Terlebih Dahulu';
                    if (this.totalQuestions === 0) return 'Atur Minimal 1 Soal';
                    return `Terbitkan ${this.totalQuestions} Soal Untuk ${this.selectedStudents.length} Siswa`;
                }
            }
        }
    </script>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</x-app-layout>