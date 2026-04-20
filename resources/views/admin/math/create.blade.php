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

                            <div class="mb-4 relative">
                                <select x-model="selectedSchool"
                                    class="w-full pl-4 pr-10 py-3 rounded-xl border-slate-200 text-sm font-bold text-slate-600 bg-slate-50 focus:ring-2 focus:ring-indigo-500 cursor-pointer appearance-none">
                                    <option value="">-- Semua Sekolah --</option>
                                    @foreach($schools as $school)
                                    <option value="{{ $school->id }}">{{ $school->name }}</option>
                                    @endforeach
                                </select>
                                <i
                                    class="fas fa-chevron-down absolute right-4 top-4 text-slate-400 text-xs pointer-events-none"></i>
                            </div>

                            <div class="mb-5 relative">
                                <i class="fas fa-search absolute left-4 top-3.5 text-slate-400"></i>
                                <input type="text" x-model="search" placeholder="Cari nama siswa..."
                                    class="w-full pl-11 pr-4 py-3 rounded-xl border-slate-200 bg-slate-50 text-sm font-bold focus:ring-2 focus:ring-indigo-500 placeholder-slate-400 text-slate-700">
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
                                            <span class="text-[10px] font-bold text-slate-400 truncate"
                                                x-text="student.school ? student.school.name : 'Pusat'"></span>
                                        </div>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- ================= KOLOM KANAN (RASIO 8/12 ATAU 67%) ================= --}}
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

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                            {{-- 1. PENJUMLAHAN --}}
                            <div class="flex flex-col p-1 bg-white rounded-2xl border-2 transition-all"
                                :class="types.includes('addition') ? 'border-emerald-500 bg-emerald-50/10 shadow-lg shadow-emerald-100/50' : 'border-slate-100'">
                                <label class="relative flex items-center p-3 cursor-pointer group">
                                    <input type="checkbox" name="types[]" value="addition" x-model="types"
                                        class="hidden">
                                    <div class="w-12 h-12 rounded-xl flex items-center justify-center font-black text-xl mr-4 transition-colors shrink-0"
                                        :class="types.includes('addition') ? 'bg-emerald-500 text-white shadow-md shadow-emerald-200' : 'bg-slate-100 text-slate-400 group-hover:bg-emerald-100 group-hover:text-emerald-500'">
                                        <i class="fas fa-plus"></i>
                                    </div>
                                    <div class="flex-1"><span
                                            class="block font-black text-slate-800 text-lg">Penjumlahan</span></div>
                                    <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-colors shrink-0 ml-2"
                                        :class="types.includes('addition') ? 'border-emerald-500 bg-emerald-500' : 'border-slate-200'">
                                        <i class="fas fa-check text-white text-[10px]"
                                            x-show="types.includes('addition')"></i>
                                    </div>
                                </label>
                                <div x-show="types.includes('addition')" x-collapse>
                                    <div class="px-4 pb-4 pt-2">
                                        <div class="border-t border-emerald-100 pt-3 grid grid-cols-2 gap-3">
                                            <div>
                                                <label
                                                    class="block text-[10px] font-black text-emerald-600 mb-1 uppercase">Angka
                                                    1 (Kiri):</label>
                                                <select name="digits[addition][num1]"
                                                    class="w-full text-xs font-bold text-slate-700 rounded-lg border-emerald-200 bg-emerald-50 focus:ring-emerald-500 py-2">
                                                    <option value="1">Pasti 1 Digit</option>
                                                    <option value="2" selected>Pasti 2 Digit</option>
                                                    <option value="2_max">Maks 2 Digit (1-99)</option>
                                                    <option value="3">Pasti 3 Digit</option>
                                                    <option value="3_max">Maks 3 Digit (1-999)</option>
                                                    <option value="4">Pasti 4 Digit</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-[10px] font-black text-emerald-600 mb-1 uppercase">Angka
                                                    2 (Kanan):</label>
                                                <select name="digits[addition][num2]"
                                                    class="w-full text-xs font-bold text-slate-700 rounded-lg border-emerald-200 bg-emerald-50 focus:ring-emerald-500 py-2">
                                                    <option value="1" selected>Pasti 1 Digit</option>
                                                    <option value="2">Pasti 2 Digit</option>
                                                    <option value="2_max">Maks 2 Digit (1-99)</option>
                                                    <option value="3">Pasti 3 Digit</option>
                                                    <option value="3_max">Maks 3 Digit (1-999)</option>
                                                    <option value="4">Pasti 4 Digit</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- 2. PENGURANGAN --}}
                            <div class="flex flex-col p-1 bg-white rounded-2xl border-2 transition-all"
                                :class="types.includes('subtraction') ? 'border-rose-500 bg-rose-50/10 shadow-lg shadow-rose-100/50' : 'border-slate-100'">
                                <label class="relative flex items-center p-3 cursor-pointer group">
                                    <input type="checkbox" name="types[]" value="subtraction" x-model="types"
                                        class="hidden">
                                    <div class="w-12 h-12 rounded-xl flex items-center justify-center font-black text-xl mr-4 transition-colors shrink-0"
                                        :class="types.includes('subtraction') ? 'bg-rose-500 text-white shadow-md shadow-rose-200' : 'bg-slate-100 text-slate-400 group-hover:bg-rose-100 group-hover:text-rose-500'">
                                        <i class="fas fa-minus"></i>
                                    </div>
                                    <div class="flex-1"><span
                                            class="block font-black text-slate-800 text-lg">Pengurangan</span></div>
                                    <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-colors shrink-0 ml-2"
                                        :class="types.includes('subtraction') ? 'border-rose-500 bg-rose-500' : 'border-slate-200'">
                                        <i class="fas fa-check text-white text-[10px]"
                                            x-show="types.includes('subtraction')"></i>
                                    </div>
                                </label>
                                <div x-show="types.includes('subtraction')" x-collapse>
                                    <div class="px-4 pb-4 pt-2">
                                        <div class="border-t border-rose-100 pt-3 grid grid-cols-2 gap-3">
                                            <div>
                                                <label
                                                    class="block text-[10px] font-black text-rose-600 mb-1 uppercase">Angka
                                                    1 (Kiri):</label>
                                                <select name="digits[subtraction][num1]"
                                                    class="w-full text-xs font-bold text-slate-700 rounded-lg border-rose-200 bg-rose-50 focus:ring-rose-500 py-2">
                                                    <option value="1">Pasti 1 Digit</option>
                                                    <option value="2" selected>Pasti 2 Digit</option>
                                                    <option value="2_max">Maks 2 Digit</option>
                                                    <option value="3">Pasti 3 Digit</option>
                                                    <option value="3_max">Maks 3 Digit</option>
                                                    <option value="4">Pasti 4 Digit</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-[10px] font-black text-rose-600 mb-1 uppercase">Angka
                                                    2 (Kanan):</label>
                                                <select name="digits[subtraction][num2]"
                                                    class="w-full text-xs font-bold text-slate-700 rounded-lg border-rose-200 bg-rose-50 focus:ring-rose-500 py-2">
                                                    <option value="1" selected>Pasti 1 Digit</option>
                                                    <option value="2">Pasti 2 Digit</option>
                                                    <option value="2_max">Maks 2 Digit</option>
                                                    <option value="3">Pasti 3 Digit</option>
                                                    <option value="3_max">Maks 3 Digit</option>
                                                    <option value="4">Pasti 4 Digit</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- 3. PERKALIAN --}}
                            <div class="flex flex-col p-1 bg-white rounded-2xl border-2 transition-all"
                                :class="types.includes('multiplication') ? 'border-blue-500 bg-blue-50/10 shadow-lg shadow-blue-100/50' : 'border-slate-100'">
                                <label class="relative flex items-center p-3 cursor-pointer group">
                                    <input type="checkbox" name="types[]" value="multiplication" x-model="types"
                                        class="hidden">
                                    <div class="w-12 h-12 rounded-xl flex items-center justify-center font-black text-xl mr-4 transition-colors shrink-0"
                                        :class="types.includes('multiplication') ? 'bg-blue-500 text-white shadow-md shadow-blue-200' : 'bg-slate-100 text-slate-400 group-hover:bg-blue-100 group-hover:text-blue-500'">
                                        <i class="fas fa-times"></i>
                                    </div>
                                    <div class="flex-1"><span
                                            class="block font-black text-slate-800 text-lg">Perkalian</span></div>
                                    <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-colors shrink-0 ml-2"
                                        :class="types.includes('multiplication') ? 'border-blue-500 bg-blue-500' : 'border-slate-200'">
                                        <i class="fas fa-check text-white text-[10px]"
                                            x-show="types.includes('multiplication')"></i>
                                    </div>
                                </label>
                                <div x-show="types.includes('multiplication')" x-collapse>
                                    <div class="px-4 pb-4 pt-2">
                                        <div class="border-t border-blue-100 pt-3 grid grid-cols-2 gap-3">
                                            <div>
                                                <label
                                                    class="block text-[10px] font-black text-blue-600 mb-1 uppercase">Angka
                                                    1 (Kiri):</label>
                                                <select name="digits[multiplication][num1]"
                                                    class="w-full text-xs font-bold text-slate-700 rounded-lg border-blue-200 bg-blue-50 focus:ring-blue-500 py-2">
                                                    <option value="1">Pasti 1 Digit</option>
                                                    <option value="2">Pasti 2 Digit</option>
                                                    <option value="2_max" selected>Maks 2 Digit</option>
                                                    <option value="3">Pasti 3 Digit</option>
                                                    <option value="3_max">Maks 3 Digit</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-[10px] font-black text-blue-600 mb-1 uppercase">Angka
                                                    2 (Kanan):</label>
                                                <select name="digits[multiplication][num2]"
                                                    class="w-full text-xs font-bold text-slate-700 rounded-lg border-blue-200 bg-blue-50 focus:ring-blue-500 py-2">
                                                    <option value="1" selected>Pasti 1 Digit</option>
                                                    <option value="2">Pasti 2 Digit</option>
                                                    <option value="2_max">Maks 2 Digit</option>
                                                    <option value="3">Pasti 3 Digit</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- 4. PEMBAGIAN --}}
                            <div class="flex flex-col p-1 bg-white rounded-2xl border-2 transition-all"
                                :class="types.includes('division') ? 'border-purple-500 bg-purple-50/10 shadow-lg shadow-purple-100/50' : 'border-slate-100'">
                                <label class="relative flex items-center p-3 cursor-pointer group">
                                    <input type="checkbox" name="types[]" value="division" x-model="types"
                                        class="hidden">
                                    <div class="w-12 h-12 rounded-xl flex items-center justify-center font-black text-xl mr-4 transition-colors shrink-0"
                                        :class="types.includes('division') ? 'bg-purple-500 text-white shadow-md shadow-purple-200' : 'bg-slate-100 text-slate-400 group-hover:bg-purple-100 group-hover:text-purple-500'">
                                        <i class="fas fa-divide"></i>
                                    </div>
                                    <div class="flex-1"><span
                                            class="block font-black text-slate-800 text-lg">Pembagian</span></div>
                                    <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-colors shrink-0 ml-2"
                                        :class="types.includes('division') ? 'border-purple-500 bg-purple-500' : 'border-slate-200'">
                                        <i class="fas fa-check text-white text-[10px]"
                                            x-show="types.includes('division')"></i>
                                    </div>
                                </label>
                                <div x-show="types.includes('division')" x-collapse>
                                    <div class="px-4 pb-4 pt-2">
                                        <div class="border-t border-purple-100 pt-3 grid grid-cols-2 gap-3">
                                            <div>
                                                <label
                                                    class="block text-[10px] font-black text-purple-600 mb-1 uppercase">Yg
                                                    Dibagi (Kiri):</label>
                                                <select name="digits[division][num1]"
                                                    class="w-full text-xs font-bold text-slate-700 rounded-lg border-purple-200 bg-purple-50 focus:ring-purple-500 py-2">
                                                    <option value="2">Pasti 2 Digit</option>
                                                    <option value="2_max">Maks 2 Digit</option>
                                                    <option value="3" selected>Pasti 3 Digit</option>
                                                    <option value="3_max">Maks 3 Digit</option>
                                                    <option value="4">Pasti 4 Digit</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-[10px] font-black text-purple-600 mb-1 uppercase">Pembagi
                                                    (Kanan):</label>
                                                <select name="digits[division][num2]"
                                                    class="w-full text-xs font-bold text-slate-700 rounded-lg border-purple-200 bg-purple-50 focus:ring-purple-500 py-2">
                                                    <option value="1" selected>Pasti 1 Digit</option>
                                                    <option value="2">Pasti 2 Digit</option>
                                                    <option value="2_max">Maks 2 Digit</option>
                                                </select>
                                            </div>
                                        </div>
                                        <p class="text-[9px] text-purple-500 mt-2 font-bold"><i
                                                class="fas fa-shield-alt"></i> Dijamin membagi habis tanpa sisa.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-6 sm:p-8 rounded-[2rem] shadow-sm border border-slate-100">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
                                <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100 relative">
                                    <div
                                        class="w-10 h-10 rounded-full bg-white text-indigo-500 flex items-center justify-center shadow-sm mb-3">
                                        <i class="fas fa-list-ol"></i>
                                    </div>
                                    <label class="block font-black text-slate-700 mb-2">Total Soal</label>
                                    <input type="number" name="total_questions" value="20" min="1" max="200" required
                                        class="w-full rounded-xl border-slate-200 bg-white font-bold text-slate-600 focus:ring-2 focus:ring-indigo-500 shadow-sm py-3 pl-4 pr-12">
                                    <span
                                        class="absolute right-9 bottom-9 text-xs font-black text-slate-400 pointer-events-none">SOAL</span>
                                </div>

                                <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100 relative">
                                    <div
                                        class="w-10 h-10 rounded-full bg-white text-indigo-500 flex items-center justify-center shadow-sm mb-3">
                                        <i class="fas fa-hourglass-half"></i>
                                    </div>
                                    <label class="block font-black text-slate-700 mb-2">Batas Waktu</label>
                                    <input type="number" name="duration_minutes" value="30" min="1" required
                                        class="w-full rounded-xl border-slate-200 bg-white font-bold text-slate-600 focus:ring-2 focus:ring-indigo-500 shadow-sm py-3 pl-4 pr-14">
                                    <span
                                        class="absolute right-9 bottom-9 text-xs font-black text-slate-400 pointer-events-none">MENIT</span>
                                </div>
                            </div>

                            <button type="submit" :disabled="selectedStudents.length === 0 || types.length === 0"
                                class="w-full py-5 rounded-2xl font-black text-lg transition-all duration-300 flex justify-center items-center gap-3 relative overflow-hidden group"
                                :class="(selectedStudents.length === 0 || types.length === 0) ? 'bg-slate-100 text-slate-400 cursor-not-allowed' : 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-xl hover:-translate-y-1'">
                                <div x-show="selectedStudents.length > 0 && types.length > 0"
                                    class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/20 to-transparent group-hover:animate-[shimmer_1.5s_infinite]">
                                </div>
                                <i class="fas fa-rocket relative z-10"
                                    :class="selectedStudents.length > 0 && types.length > 0 ? 'animate-bounce' : ''"></i>
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
                search: '',
                selectedStudents: [],
                types: ['addition'], // Default aktif

                get filteredStudents() {
                    return this.students.filter(s => {
                        const matchSchool = this.selectedSchool === '' || (s.school_id && String(s.school_id) === String(this.selectedSchool));
                        const matchName = s.name.toLowerCase().includes(this.search.toLowerCase());
                        return matchSchool && matchName;
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