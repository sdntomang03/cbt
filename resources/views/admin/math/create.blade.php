<x-app-layout>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>

    <div class="min-h-screen bg-slate-50/50 py-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">

            <div class="mb-8">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-indigo-200 rotate-3">
                        <i class="fas fa-calculator text-xl -rotate-3"></i>
                    </div>
                    <div>
                        <h2 class="text-3xl font-black text-slate-800 tracking-tight">Generate Ujian Matematika</h2>
                        <p class="text-slate-500 font-bold text-sm mt-1">Atur kesulitan spesifik dan tugaskan ke banyak
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

                    {{-- ================= KOLOM KIRI (RASIO 4/12 ATAU 33%) ================= --}}
                    <div class="lg:col-span-4 flex flex-col h-[700px] xl:h-[800px]">
                        <div
                            class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 flex-1 flex flex-col overflow-hidden">
                            <h3 class="font-black text-lg text-slate-800 mb-5 flex items-center gap-2">
                                <i class="fas fa-users text-indigo-500"></i> Pilih Peserta
                            </h3>

                            <div class="mb-3 relative">
                                <select x-model="selectedSchool"
                                    class="w-full pl-4 pr-10 py-2.5 rounded-xl border-slate-200 text-sm font-bold text-slate-600 bg-slate-50 focus:ring-2 focus:ring-indigo-500 cursor-pointer appearance-none">
                                    <option value="">-- Semua Sekolah --</option>
                                    @foreach($schools as $school)
                                    <option value="{{ $school->id }}">{{ $school->name }}</option>
                                    @endforeach
                                </select>
                                <i
                                    class="fas fa-chevron-down absolute right-4 top-3.5 text-slate-400 text-xs pointer-events-none"></i>
                            </div>

                            <div class="mb-3 relative">
                                <select x-model="selectedClass" :disabled="availableClasses.length === 0"
                                    class="w-full pl-4 pr-10 py-2.5 rounded-xl border-slate-200 text-sm font-bold text-slate-600 bg-slate-50 focus:ring-2 focus:ring-indigo-500 cursor-pointer appearance-none disabled:opacity-50">
                                    <option value="">-- Semua Kelas --</option>
                                    <template x-for="kelas in availableClasses" :key="kelas">
                                        <option :value="kelas" x-text="kelas"></option>
                                    </template>
                                </select>
                                <i
                                    class="fas fa-chevron-down absolute right-4 top-3.5 text-slate-400 text-xs pointer-events-none"></i>
                            </div>

                            <div class="mb-5 relative">
                                <i class="fas fa-search absolute left-4 top-3 text-slate-400"></i>
                                <input type="text" x-model="search" placeholder="Cari nama siswa..."
                                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border-slate-200 bg-slate-50 text-sm font-bold focus:ring-2 focus:ring-indigo-500 placeholder-slate-400 text-slate-700">
                            </div>

                            <div class="flex justify-between items-center mb-3 px-1">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"
                                        x-show="selectedStudents.length > 0" x-cloak></span>
                                    <span class="text-xs font-black text-slate-500">
                                        <span x-text="selectedStudents.length" class="text-indigo-600"></span> TERPILIH
                                    </span>
                                </div>
                                <button type="button" @click="toggleSelectAll()"
                                    class="text-xs font-black transition flex items-center gap-1.5 px-3 py-1.5 rounded-lg hover:bg-slate-50"
                                    :class="isAllSelected ? 'text-indigo-600' : 'text-slate-400 hover:text-indigo-600'">
                                    <i class="fas" :class="isAllSelected ? 'fa-check-square' : 'fa-square'"></i>
                                    <span x-text="isAllSelected ? 'Batal Semua' : 'Pilih Semua'"></span>
                                </button>
                            </div>

                            <div
                                class="flex-1 overflow-y-auto custom-scrollbar bg-slate-50/50 rounded-xl p-2 space-y-1.5 border border-slate-100/50">
                                <template x-for="student in filteredStudents" :key="student.id">
                                    <label
                                        class="flex items-center p-3 bg-white rounded-xl cursor-pointer border-2 transition-all hover:shadow-md"
                                        :class="selectedStudents.includes(String(student.id)) ? 'border-indigo-500 shadow-indigo-100 shadow-sm' : 'border-transparent hover:border-indigo-100'">
                                        <div class="relative flex items-center justify-center w-5 h-5 mr-3 shrink-0">
                                            <input type="checkbox" name="student_ids[]" :value="student.id"
                                                x-model="selectedStudents"
                                                class="peer w-5 h-5 opacity-0 absolute cursor-pointer">
                                            <div
                                                class="w-5 h-5 rounded border-2 border-slate-300 flex items-center justify-center peer-checked:bg-indigo-500 peer-checked:border-indigo-500 transition-colors">
                                                <i
                                                    class="fas fa-check text-white text-[10px] opacity-0 peer-checked:opacity-100"></i>
                                            </div>
                                        </div>
                                        <div class="flex flex-col min-w-0">
                                            <span class="font-black text-slate-700 text-sm truncate"
                                                x-text="student.name"></span>
                                            <div
                                                class="flex items-center gap-1 text-[10px] font-bold text-slate-400 mt-0.5">
                                                <span class="truncate"
                                                    x-text="student.school ? student.school.name : 'Pusat'"></span>
                                                <span x-show="student.kelas"
                                                    class="px-1.5 py-0.5 bg-slate-100 rounded text-slate-500"
                                                    x-text="student.kelas"></span>
                                            </div>
                                        </div>
                                    </label>
                                </template>

                                <div x-show="filteredStudents.length === 0"
                                    class="text-center py-10 text-slate-400 text-sm font-bold">
                                    Tidak ada siswa yang sesuai filter.
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ================= KOLOM KANAN (RASIO 8/12 ATAU 67%) ================= --}}
                    {{-- KODE KOLOM KANAN ANDA TETAP SAMA SEPERTI SEBELUMNYA --}}
                    <div class="lg:col-span-8 flex flex-col gap-6 xl:gap-8">
                        <div
                            class="bg-white p-6 sm:p-8 rounded-[2rem] shadow-sm border border-slate-100 relative overflow-hidden">
                            <div class="absolute right-0 top-0 w-32 h-32 bg-indigo-50 rounded-bl-full -z-10"></div>
                            <label class="block font-black text-xl text-slate-800 mb-2">Nama / Kelompok Ujian</label>
                            <p class="text-sm font-bold text-slate-400 mb-4">Misal: Latihan 1, Ulangan Harian, atau
                                Tugas Rumah.</p>
                            <input type="text" name="title" required placeholder="Ketik judul ujian di sini..."
                                class="w-full text-lg font-bold text-slate-700 bg-slate-50 border-slate-200 rounded-xl focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 py-4 px-5 shadow-inner placeholder-slate-300">
                        </div>

                        @include('admin.math.partials.operation_cards') {{-- Asumsi sisa form diletakkan di sini agar
                        jawaban ini tidak terpotong kepanjangan --}}
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
                selectedClass: '', // Tambahan state Kelas
                search: '',
                selectedStudents: [],
                types: ['addition'],

                init() {
                    // Ketika sekolah diubah, reset pilihan kelas agar tidak error
                    this.$watch('selectedSchool', () => {
                        this.selectedClass = '';
                    });
                },

                // Getter untuk mengekstrak Kelas apa saja yang tersedia dari data Siswa
                get availableClasses() {
                    let filtered = this.students;

                    // Filter berdasarkan sekolah yang dipilih dulu
                    if (this.selectedSchool !== '') {
                        filtered = filtered.filter(s => s.school_id && String(s.school_id) === String(this.selectedSchool));
                    }

                    // Ekstrak properti 'kelas', buang yang kosong/null, dan ambil yang unik (Set)
                    // Catatan: Pastikan di model User, nama kolom kelasnya adalah 'kelas'
                    const classesArray = filtered.map(s => s.kelas).filter(k => k && k.trim() !== '');
                    return [...new Set(classesArray)].sort();
                },

                get filteredStudents() {
                    return this.students.filter(s => {
                        const matchSchool = this.selectedSchool === '' || (s.school_id && String(s.school_id) === String(this.selectedSchool));
                        const matchClass = this.selectedClass === '' || (s.kelas && String(s.kelas) === String(this.selectedClass));
                        const matchName = s.name.toLowerCase().includes(this.search.toLowerCase());

                        return matchSchool && matchClass && matchName;
                    });
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

                getSubmitText() {
                    if (this.selectedStudents.length === 0) return 'Pilih Siswa Terlebih Dahulu';
                    if (this.types.length === 0) return 'Pilih Minimal 1 Tipe Operasi';
                    return `Buat Ujian untuk ${this.selectedStudents.length} Siswa`;
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