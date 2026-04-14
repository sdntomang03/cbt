<x-app-layout>
    <div class="py-10 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto">

        {{-- ================= HEADER ================= --}}
        <div class="mb-8 flex items-center gap-4">
            <a href="{{ route('admin.exams.index') }}"
                class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 transition-all shadow-sm border border-slate-200 group">
                <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
            </a>
            <div>
                <h2 class="text-3xl font-black text-slate-800 tracking-tight leading-none">Buat Ujian Baru</h2>
                <p class="text-slate-500 font-bold mt-2 text-sm">Pengaturan master bank soal ujian</p>
            </div>
        </div>

        {{-- ================= FORM CARD ================= --}}
        <div class="bg-white p-6 md:p-10 rounded-[2rem] shadow-sm border border-slate-200 relative overflow-hidden">

            {{-- Aksen Latar Belakang --}}
            <div
                class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-bl from-indigo-50 to-transparent rounded-bl-full -z-10">
            </div>

            <form action="{{ route('admin.exams.store') }}" method="POST" class="space-y-6">
                @csrf

                {{-- NAMA UJIAN --}}
                <div>
                    <label for="title" class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">
                        Nama Ujian <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="title" name="title" value="{{ old('title') }}"
                        class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-bold text-slate-800 placeholder-slate-300 py-3 px-4 transition-colors"
                        placeholder="Contoh: Ujian Akhir Semester Ganjil 2026" required autofocus>
                    @error('title')
                    <span class="text-rose-500 text-sm font-bold mt-1.5 block flex items-center gap-1"><i
                            class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                    @enderror
                </div>

                {{-- GRID: DURASI & STATUS --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Durasi --}}
                    <div>
                        <label for="duration"
                            class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">
                            Durasi (Menit) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-stopwatch text-slate-400"></i>
                            </div>
                            <input type="number" id="duration" name="duration_minutes"
                                value="{{ old('duration_minutes', 60) }}" min="1"
                                class="block w-full pl-11 rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-bold text-slate-800 py-3 px-4 transition-colors"
                                required>
                        </div>
                        @error('duration_minutes')
                        <span class="text-rose-500 text-sm font-bold mt-1.5 block flex items-center gap-1"><i
                                class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Status --}}
                    <div>
                        <label for="status"
                            class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">
                            Status Awal <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-toggle-on text-slate-400"></i>
                            </div>
                            <select id="status" name="status"
                                class="block w-full pl-11 rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-bold text-slate-800 py-3 px-4 appearance-none transition-colors cursor-pointer">
                                @foreach(App\Enums\ExamStatus::cases() as $status)
                                <option value="{{ $status->value }}" {{ old('status')==$status->value ? 'selected' : ''
                                    }}>
                                    {{ $status->getLabel() }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @error('status')
                        <span class="text-rose-500 text-sm font-bold mt-1.5 block flex items-center gap-1"><i
                                class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- PENGATURAN TAMBAHAN (KARTU CHECKBOX) --}}
                <div class="bg-slate-50 p-6 rounded-2xl border border-slate-200 mt-8 space-y-4">
                    <h4 class="font-black text-sm text-slate-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                        <i class="fas fa-cog text-indigo-500"></i> Pengaturan Soal
                    </h4>

                    {{-- Opsi Acak Soal --}}
                    <label
                        class="flex items-start p-4 bg-white rounded-xl border border-slate-200 cursor-pointer hover:border-indigo-300 transition-colors shadow-sm group">
                        <div class="flex items-center h-5 mt-0.5">
                            <input type="checkbox" name="random_question" value="1"
                                class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer transition-colors"
                                {{ old('random_question', true) ? 'checked' : '' }}>
                        </div>
                        <div class="ml-3 flex-1">
                            <span
                                class="block font-black text-slate-800 group-hover:text-indigo-600 transition-colors">Acak
                                Urutan Soal</span>
                            <span class="block text-xs text-slate-500 font-medium mt-1">Soal akan ditampilkan dengan
                                urutan yang berbeda-beda untuk setiap siswa.</span>
                        </div>
                    </label>

                    {{-- Opsi Acak Jawaban --}}
                    <label
                        class="flex items-start p-4 bg-white rounded-xl border border-slate-200 cursor-pointer hover:border-indigo-300 transition-colors shadow-sm group">
                        <div class="flex items-center h-5 mt-0.5">
                            <input type="checkbox" name="random_answer" value="1"
                                class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer transition-colors"
                                {{ old('random_answer', true) ? 'checked' : '' }}>
                        </div>
                        <div class="ml-3 flex-1">
                            <span
                                class="block font-black text-slate-800 group-hover:text-indigo-600 transition-colors">Acak
                                Pilihan Jawaban</span>
                            <span class="block text-xs text-slate-500 font-medium mt-1">Opsi jawaban (A, B, C, D) akan
                                dirotasi secara otomatis untuk mencegah siswa menyontek.</span>
                        </div>
                    </label>
                </div>

                {{-- BUTTONS --}}
                <div
                    class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-6 border-t border-slate-100">
                    <a href="{{ route('admin.exams.index') }}"
                        class="w-full sm:w-auto px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl transition-colors text-center">
                        Batal
                    </a>
                    <button type="submit"
                        class="w-full sm:w-auto px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-200 transition-all hover:-translate-y-0.5 flex items-center justify-center gap-2">
                        <i class="fas fa-save"></i> Simpan Ujian
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>