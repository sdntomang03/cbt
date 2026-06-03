<x-app-layout>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>

    <div class="min-h-screen bg-slate-50/50 py-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">

            {{-- ================= HEADER ================= --}}
            <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div
                        class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-indigo-200 rotate-3 transition-transform hover:rotate-0">
                        <i class="fas fa-calculator text-2xl -rotate-3 hover:rotate-0 transition-transform"></i>
                    </div>
                    <div>
                        <h2 class="text-3xl font-black text-slate-800 tracking-tight">Generate Ujian</h2>
                        <p class="text-slate-500 font-bold text-sm mt-1">Buat komposisi soal dinamis untuk banyak siswa
                            sekaligus.</p>
                    </div>
                </div>
            </div>

            @if(session('success'))
            <div
                class="bg-emerald-50 border border-emerald-200 text-emerald-700 p-4 rounded-2xl mb-8 font-bold shadow-sm flex items-center gap-3 animate-pulse">
                <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
                    <i class="fas fa-check text-emerald-600"></i>
                </div>
                {{ session('success') }}
            </div>
            @endif

            <form action="{{ route('admin.math.store') }}" method="POST" x-data="mathExamForm()">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 xl:gap-8">

                    {{-- ================= KOLOM KIRI (PILIH PESERTA) ================= --}}
                    <div class="lg:col-span-4 flex flex-col h-[750px]">
                        <div
                            class="bg-white p-5 rounded-[2rem] shadow-sm border border-slate-100 flex-1 flex flex-col overflow-hidden">
                            <h3 class="font-black text-lg text-slate-800 mb-4 flex items-center gap-2">
                                <i class="fas fa-users text-indigo-500"></i> Daftar Peserta
                            </h3>

                            {{-- Filter Form (Sekolah, Kelas, Cari) --}}
                            <div class="space-y-3 mb-4">
                                <div class="relative">
                                    <i class="fas fa-school absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                                    <select x-model="selectedSchool"
                                        class="w-full pl-9 pr-8 py-2.5 rounded-xl border-slate-200 text-xs font-bold text-slate-600 bg-slate-50 focus:ring-2 focus:ring-indigo-500 cursor-pointer appearance-none transition-all">
                                        <option value="">-- Semua Sekolah --</option>
                                        @foreach($schools as $school)
                                        <option value="{{ $school->id }}">{{ $school->name }}</option>
                                        @endforeach
                                    </select>
                                    <i
                                        class="fas fa-chevron-down absolute right-3.5 top-3.5 text-slate-400 text-[10px] pointer-events-none"></i>
                                </div>

                                <div class="relative">
                                    <i class="fas fa-door-open absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                                    <select x-model="selectedClass" :disabled="availableClasses.length === 0"
                                        class="w-full pl-9 pr-8 py-2.5 rounded-xl border-slate-200 text-xs font-bold text-slate-600 bg-slate-50 focus:ring-2 focus:ring-indigo-500 cursor-pointer appearance-none disabled:opacity-50 transition-all">
                                        <option value="">-- Semua Kelas --</option>
                                        <template x-for="kelas in availableClasses" :key="kelas">
                                            <option :value="kelas" x-text="kelas"></option>
                                        </template>
                                    </select>
                                    <i
                                        class="fas fa-chevron-down absolute right-3.5 top-3.5 text-slate-400 text-[10px] pointer-events-none"></i>
                                </div>

                                <div class="relative">
                                    <i class="fas fa-search absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                                    <input type="text" x-model="search" placeholder="Cari nama siswa..."
                                        class="w-full pl-9 pr-4 py-2.5 rounded-xl border-slate-200 bg-slate-50 text-xs font-bold focus:ring-2 focus:ring-indigo-500 placeholder-slate-400 text-slate-700 transition-all">
                                </div>
                            </div>

                            <div class="flex justify-between items-center mb-3 px-1 border-t border-slate-100 pt-3">
                                <div
                                    class="flex items-center gap-2 bg-indigo-50 px-3 py-1.5 rounded-lg border border-indigo-100">
                                    <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"
                                        x-show="selectedStudents.length > 0" x-cloak></span>
                                    <span class="text-[10px] font-black text-indigo-700">
                                        <span x-text="selectedStudents.length" class="text-indigo-900 text-xs"></span>
                                        TERPILIH
                                    </span>
                                </div>
                                <button type="button" @click="toggleSelectAll()"
                                    class="text-xs font-black transition flex items-center gap-1.5 px-3 py-1.5 rounded-lg hover:bg-slate-100"
                                    :class="isAllSelected ? 'text-indigo-600' : 'text-slate-500 hover:text-indigo-600'">
                                    <i class="fas" :class="isAllSelected ? 'fa-check-square' : 'fa-square'"></i>
                                    <span x-text="isAllSelected ? 'Batal Semua' : 'Pilih Semua'"></span>
                                </button>
                            </div>

                            {{-- Daftar Siswa --}}
                            <div
                                class="flex-1 overflow-y-auto custom-scrollbar bg-slate-50/80 rounded-xl p-2 space-y-3 border border-slate-200/60 shadow-inner">
                                <template x-for="(studentsInClass, className) in groupedStudents" :key="className">
                                    <div class="space-y-1.5 bg-white p-2 rounded-xl border border-slate-100 shadow-sm">

                                        <div
                                            class="flex items-center justify-between px-2 py-2 bg-slate-50 rounded-lg sticky top-0 z-10">
                                            <span
                                                class="text-[11px] font-black text-slate-700 flex items-center gap-2 uppercase tracking-wide">
                                                <i class="fas fa-layer-group text-indigo-400"></i>
                                                <span x-text="className"></span>
                                            </span>
                                            <button type="button" @click="toggleClass(className)"
                                                class="text-[10px] font-black transition flex items-center gap-1 px-2 py-1 rounded-md bg-white border border-slate-200 hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-200 shadow-sm text-slate-500">
                                                <i class="fas"
                                                    :class="isClassSelected(className) ? 'fa-check text-indigo-500' : 'fa-check text-transparent'"></i>
                                                <span x-text="isClassSelected(className) ? 'Batal' : 'Pilih'"></span>
                                            </button>
                                        </div>

                                        <div class="space-y-1">
                                            <template x-for="student in studentsInClass" :key="student.id">
                                                <label
                                                    class="flex items-center px-3 py-2.5 bg-white rounded-lg cursor-pointer border transition-all hover:bg-slate-50"
                                                    :class="selectedStudents.includes(String(student.id)) ? 'border-indigo-400 bg-indigo-50/30' : 'border-transparent'">
                                                    <div
                                                        class="relative flex items-center justify-center w-4 h-4 mr-3 shrink-0">
                                                        <input type="checkbox" name="student_ids[]" :value="student.id"
                                                            x-model="selectedStudents"
                                                            class="peer w-4 h-4 opacity-0 absolute cursor-pointer">
                                                        <div
                                                            class="w-4 h-4 rounded border-2 border-slate-300 flex items-center justify-center peer-checked:bg-indigo-500 peer-checked:border-indigo-500 transition-colors bg-white">
                                                            <i
                                                                class="fas fa-check text-white text-[8px] opacity-0 peer-checked:opacity-100"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex flex-col min-w-0">
                                                        <span class="font-black text-slate-700 text-xs truncate"
                                                            x-text="student.name"></span>
                                                        <div
                                                            class="flex items-center gap-1 text-[9px] font-bold text-slate-400 mt-0.5">
                                                            <i class="fas fa-school text-slate-300"></i>
                                                            <span class="truncate"
                                                                x-text="student.school ? student.school.name : 'Pusat'"></span>
                                                        </div>
                                                    </div>
                                                </label>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                <div x-show="Object.keys(groupedStudents).length === 0"
                                    class="text-center py-10 flex flex-col items-center justify-center" x-cloak>
                                    <div
                                        class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mb-3">
                                        <i class="fas fa-search text-slate-300 text-lg"></i>
                                    </div>
                                    <span class="text-slate-400 text-xs font-bold">Peserta tidak ditemukan.</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ================= KOLOM KANAN (SETTING UJIAN & BUILDER SOAL) ================= --}}
                    <div class="lg:col-span-8 flex flex-col gap-6">

                        {{-- Judul Ujian --}}
                        <div
                            class="bg-white p-6 sm:p-8 rounded-[2rem] shadow-sm border border-slate-100 relative overflow-hidden group">
                            <div
                                class="absolute -right-6 -top-6 w-32 h-32 bg-indigo-50 rounded-full -z-10 group-hover:scale-150 transition-transform duration-700 ease-out">
                            </div>

                            <label class="block font-black text-sm text-indigo-600 mb-1 uppercase tracking-widest">Tahap
                                1</label>
                            <h3 class="font-black text-2xl text-slate-800 mb-1">Identitas Ujian</h3>
                            <p class="text-sm font-bold text-slate-400 mb-5">Berikan nama agar mudah dikenali oleh
                                siswa.</p>

                            <div class="relative">
                                <i class="fas fa-heading absolute left-5 top-4 text-slate-400"></i>
                                <input type="text" name="title" required
                                    placeholder="Misal: Ulangan Harian 1 Matematika..."
                                    class="w-full text-base font-black text-slate-700 bg-slate-50 border-slate-200 rounded-2xl focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/20 py-3.5 pl-12 pr-5 transition-all placeholder-slate-300">
                            </div>
                        </div>

                        {{-- Builder Soal --}}
                        <div
                            class="bg-white p-6 sm:p-8 rounded-[2rem] shadow-sm border border-slate-100 flex-1 flex flex-col relative overflow-hidden">

                            <div
                                class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-6 gap-4 border-b border-slate-100 pb-5">
                                <div>
                                    <label
                                        class="block font-black text-sm text-indigo-600 mb-1 uppercase tracking-widest">Tahap
                                        2</label>
                                    <h3 class="font-black text-2xl text-slate-800">Aturan & Komposisi Soal</h3>
                                    <p class="text-sm font-bold text-slate-400 mt-1">Tambahkan blok operasi hitung
                                        sesuai kebutuhan.</p>
                                </div>
                                <div
                                    class="bg-indigo-600 text-white px-5 py-3 rounded-2xl font-black text-sm shadow-lg shadow-indigo-200 flex flex-col items-center justify-center min-w-[100px]">
                                    <span x-text="totalQuestions" class="text-3xl leading-none mb-1"></span>
                                    <span class="text-[10px] tracking-widest opacity-80">TOTAL SOAL</span>
                                </div>
                            </div>

                            {{-- Daftar Aturan (Rules) --}}
                            <div class="space-y-6 mb-6 flex-1">
                                <template x-for="(rule, index) in rules" :key="rule.id">
                                    <div
                                        class="bg-white border-2 border-slate-200 rounded-3xl overflow-hidden transition-all hover:border-indigo-300 hover:shadow-md relative group">

                                        {{-- Header Rule --}}
                                        <div
                                            class="bg-slate-50/80 px-6 py-4 flex justify-between items-center border-b border-slate-200 group-hover:bg-indigo-50/50 transition-colors">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-xl bg-white shadow-sm border border-slate-200 text-slate-600 flex items-center justify-center font-black text-sm"
                                                    x-text="index + 1"></div>
                                                <span class="font-black text-slate-700 text-sm tracking-wide">Blok Soal
                                                    <span x-text="index + 1"></span></span>
                                            </div>

                                            <button type="button" @click="removeRule(index)" x-show="rules.length > 1"
                                                class="w-8 h-8 rounded-full bg-white border border-slate-200 text-slate-400 hover:text-rose-600 hover:border-rose-200 hover:bg-rose-50 flex items-center justify-center transition-all shadow-sm"
                                                title="Hapus Blok Ini">
                                                <i class="fas fa-trash-alt text-xs"></i>
                                            </button>
                                        </div>

                                        {{-- Body Rule --}}
                                        <div class="p-6">
                                            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">

                                                {{-- Pilih Operasi --}}
                                                <div class="md:col-span-4">
                                                    <label
                                                        class="flex items-center gap-2 text-xs font-black text-slate-500 uppercase tracking-wider mb-2">
                                                        <i class="fas fa-square-root-alt text-indigo-400"></i> Jenis
                                                        Operasi
                                                    </label>
                                                    <div class="relative">
                                                        <select x-model="rule.type" :name="`rules[${index}][type]`"
                                                            class="w-full text-sm font-black text-slate-700 rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 py-3 pl-4 pr-10 transition-all cursor-pointer appearance-none">
                                                            <option value="addition">Penjumlahan (+)</option>
                                                            <option value="subtraction">Pengurangan (-)</option>
                                                            <option value="multiplication">Perkalian (x)</option>
                                                            <option value="division">Pembagian (:)</option>
                                                        </select>
                                                        <i
                                                            class="fas fa-chevron-down absolute right-4 top-4 text-slate-400 text-[10px] pointer-events-none"></i>
                                                    </div>
                                                </div>

                                                {{-- Digit Kiri & Kanan --}}
                                                <div
                                                    class="md:col-span-5 bg-slate-50 p-4 rounded-2xl border border-slate-200">
                                                    <label
                                                        class="flex items-center gap-2 text-xs font-black text-slate-500 uppercase tracking-wider mb-3">
                                                        <i class="fas fa-ruler-horizontal text-indigo-400"></i>
                                                        Kesulitan Angka
                                                    </label>
                                                    <div class="flex items-center gap-3">
                                                        <select x-model="rule.num1" :name="`rules[${index}][num1]`"
                                                            class="w-full text-xs font-black text-slate-700 rounded-lg border-slate-200 bg-white focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 py-2.5 transition-all cursor-pointer">
                                                            <option value="1">1 Digit</option>
                                                            <option value="2">2 Digit</option>
                                                            <option value="2_max">Maks 2 Digit</option>
                                                            <option value="3">3 Digit</option>
                                                            <option value="3_max">Maks 3 Digit</option>
                                                        </select>
                                                        <span class="text-slate-400 font-black text-lg"
                                                            x-text="rule.type === 'addition' ? '+' : (rule.type === 'subtraction' ? '-' : (rule.type === 'multiplication' ? 'x' : ':'))"></span>
                                                        <select x-model="rule.num2" :name="`rules[${index}][num2]`"
                                                            class="w-full text-xs font-black text-slate-700 rounded-lg border-slate-200 bg-white focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 py-2.5 transition-all cursor-pointer">
                                                            <option value="1">1 Digit</option>
                                                            <option value="2">2 Digit</option>
                                                            <option value="2_max">Maks 2 Digit</option>
                                                            <option value="3">3 Digit</option>
                                                            <option value="3_max">Maks 3 Digit</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                {{-- Jumlah Soal --}}
                                                <div class="md:col-span-3">
                                                    <label
                                                        class="flex items-center gap-2 text-xs font-black text-slate-500 uppercase tracking-wider mb-2">
                                                        <i class="fas fa-cubes text-indigo-400"></i> Jml Soal
                                                    </label>
                                                    <div class="relative">
                                                        <input type="number" x-model.number="rule.count"
                                                            :name="`rules[${index}][count]`" min="1" max="100" required
                                                            class="w-full text-lg font-black text-indigo-700 rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 py-2.5 pl-5 pr-10 transition-all text-center">
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Toggle Acak Posisi --}}
                                            <div class="mt-6 flex justify-end"
                                                x-show="rule.type === 'addition' || rule.type === 'multiplication'"
                                                x-transition>
                                                <input type="hidden" :name="`rules[${index}][random_pos]`"
                                                    value="false">
                                                <label
                                                    class="relative inline-flex items-center cursor-pointer bg-slate-50 px-4 py-2 rounded-xl border border-slate-200 hover:border-indigo-300 transition-colors">
                                                    <input type="checkbox" :name="`rules[${index}][random_pos]`"
                                                        value="true" x-model="rule.random_pos" class="sr-only peer">

                                                    <span class="mr-3 text-xs font-bold text-slate-600 text-right">
                                                        Acak Posisi Kiri & Kanan <br>
                                                        <span class="text-slate-400 font-medium text-[10px]">(Misal: 2+3
                                                            bisa menjadi 3+2)</span>
                                                    </span>

                                                    <div
                                                        class="w-10 h-5 bg-slate-300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[10px] after:right-[22px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-500 shrink-0">
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <button type="button" @click="addRule()"
                                class="w-full py-4 mb-8 border-2 border-dashed border-indigo-200 bg-indigo-50/50 text-indigo-600 font-black rounded-2xl hover:bg-indigo-100 hover:border-indigo-400 transition-all flex items-center justify-center gap-2 group">
                                <i class="fas fa-plus-circle text-lg group-hover:scale-110 transition-transform"></i>
                                Tambah Blok Soal Baru
                            </button>

                            {{-- Durasi & Submit --}}
                            <div class="border-t border-slate-100 pt-8 mt-auto">
                                <label
                                    class="block font-black text-sm text-indigo-600 mb-1 uppercase tracking-widest">Tahap
                                    Akhir</label>
                                <h3 class="font-black text-2xl text-slate-800 mb-6">Waktu & Eksekusi</h3>

                                <div class="flex flex-col md:flex-row gap-6">
                                    <div
                                        class="w-full md:w-48 bg-amber-50 p-4 rounded-2xl border border-amber-200 shadow-sm shrink-0 flex flex-col justify-center">
                                        <label
                                            class="block text-xs font-black text-amber-700 uppercase tracking-wider mb-2 flex items-center gap-2">
                                            <i class="fas fa-hourglass-half"></i> Durasi (Menit)
                                        </label>
                                        <input type="number" name="duration_minutes" value="30" min="1" required
                                            class="w-full rounded-xl border-amber-300 bg-white font-black text-amber-700 focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 py-3 text-center text-xl shadow-inner">
                                    </div>

                                    <input type="hidden" name="total_questions" :value="totalQuestions">

                                    <button type="submit"
                                        :disabled="selectedStudents.length === 0 || totalQuestions === 0"
                                        class="flex-1 py-4 rounded-2xl font-black text-xl transition-all duration-300 flex justify-center items-center gap-3 relative overflow-hidden group"
                                        :class="(selectedStudents.length === 0 || totalQuestions === 0) ? 'bg-slate-200 text-slate-400 cursor-not-allowed border border-slate-300' : 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-xl shadow-indigo-200 hover:-translate-y-1'">
                                        <div x-show="selectedStudents.length > 0 && totalQuestions > 0"
                                            class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/20 to-transparent group-hover:animate-[shimmer_1.5s_infinite]">
                                        </div>
                                        <i class="fas fa-paper-plane relative z-10"
                                            :class="selectedStudents.length > 0 && totalQuestions > 0 ? 'group-hover:animate-bounce' : ''"></i>
                                        <span class="relative z-10" x-text="getSubmitText()"></span>
                                    </button>
                                </div>
                            </div>
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
                    if (this.selectedStudents.length === 0) return 'Pilih Siswa Terlebih Dahulu';
                    if (this.totalQuestions === 0) return 'Masukkan Minimal 1 Soal';
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

        @keyframes shimmer {
            100% {
                transform: translateX(100%);
            }
        }
    </style>
</x-app-layout>