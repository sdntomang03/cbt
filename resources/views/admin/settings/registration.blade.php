<x-app-layout>
    <div class="py-10 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto">
        
        <div class="mb-8">
            <h2 class="text-3xl font-black text-slate-800 tracking-tight leading-none mb-2">Pengaturan Pendaftaran</h2>
            <p class="text-slate-500 font-bold text-sm">Pilih sesi ujian otomatis untuk siswa baru.</p>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl font-bold flex items-center gap-3 shadow-sm">
                <i class="fas fa-check-circle text-xl"></i>
                {{ session('success') }}
            </div>
        @endif

        {{-- KARTU PILIH SEKOLAH --}}
        <div class="mb-6 bg-white p-6 rounded-[2rem] shadow-sm border border-slate-200">
            <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-3">
                <i class="fas fa-building text-indigo-500 mr-1"></i> Pilih Sekolah
            </label>
            <select onchange="window.location.href='?school_id=' + this.value"
                class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 font-bold text-slate-800 py-3 px-4 bg-slate-50">
                <option value="">-- Pilih Sekolah --</option>
                @foreach($schools as $school)
                    <option value="{{ $school->id }}" {{ $selectedSchoolId == $school->id ? 'selected' : '' }}>
                        {{ $school->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- FORM PENGATURAN --}}
        @if($selectedSchoolId)
            <div class="bg-white p-6 md:p-10 rounded-[2rem] shadow-sm border border-slate-200">
                <form action="{{ route('admin.settings.registration.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="school_id" value="{{ $selectedSchoolId }}">

                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-3">
                            <i class="fas fa-bullseye text-indigo-500 mr-1"></i> Sesi Ujian Default
                        </label>
                        <p class="text-xs text-slate-500 font-medium mb-4">
                            Pilih sesi ujian yang akan otomatis masuk ke dashboard siswa saat mereka mendaftar. <strong class="text-indigo-600">(Bisa pilih lebih dari satu)</strong>
                        </p>

                        @if($examSessions->isEmpty())
                            <div class="p-8 bg-slate-50 border border-dashed border-slate-200 rounded-2xl text-center">
                                <i class="fas fa-box-open text-3xl text-slate-300 mb-3 block"></i>
                                <p class="text-slate-500 font-bold text-sm">Belum ada sesi ujian untuk sekolah ini.</p>
                            </div>
                        @else
                            <div class="space-y-3 max-h-64 overflow-y-auto pr-2 custom-scrollbar">
                                {{-- Looping Sesi --}}
                                @foreach($examSessions as $session)
                                    @php
                                        // Ambil array dari database. Jika null/kosong, jadikan array kosong []
                                        $settingArray = $setting && $setting->default_exam_session_ids ? $setting->default_exam_session_ids : [];
                                        if (!is_array($settingArray)) $settingArray = [];
                                        
                                        // Cek apakah ID sesi ini ada di dalam array
                                        $isChecked = in_array($session->id, $settingArray);
                                    @endphp
                                    <label class="flex items-center p-4 bg-white border {{ $isChecked ? 'border-indigo-500 bg-indigo-50/50' : 'border-slate-200' }} rounded-xl cursor-pointer hover:border-indigo-300 transition shadow-sm group">
                                        {{-- PERHATIKAN: name menggunakan array kurung siku [] dan type menjadi checkbox --}}
                                        <input type="checkbox" name="default_exam_session_ids[]" value="{{ $session->id }}" 
                                            class="w-5 h-5 border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer rounded"
                                            {{ $isChecked ? 'checked' : '' }}>
                                        
                                        <div class="ml-4 flex-1">
                                            <span class="block font-black text-slate-800 group-hover:text-indigo-600 transition">
                                                {{ $session->session_name }}
                                            </span>
                                            <span class="block text-xs text-slate-500 font-bold mt-0.5">
                                                Ujian: {{ $session->exam->title ?? 'Tidak diketahui' }}
                                            </span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                        
                        @error('default_exam_session_ids')
                            <span class="text-rose-500 text-sm font-bold mt-2 block"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>

                    <div class="pt-8 mt-8 border-t border-slate-100 flex justify-end">
                        <button type="submit" class="px-8 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-black rounded-xl shadow-lg shadow-indigo-200 transition-all hover:-translate-y-0.5 flex items-center gap-2">
                            <i class="fas fa-save"></i> Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </div>
</x-app-layout>