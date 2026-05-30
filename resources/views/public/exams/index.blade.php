<x-public-layout>
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">

        {{-- JUDUL HALAMAN --}}
        <div class="text-center mb-12">
            <div
                class="inline-block bg-indigo-100 text-indigo-600 px-4 py-1.5 rounded-full text-[10px] font-black tracking-widest uppercase mb-4 shadow-sm border border-indigo-200">
                <i class="fas fa-rocket mr-1"></i> Mode Pengunjung Terbuka
            </div>
            <h1 class="text-4xl md:text-5xl font-black text-slate-900 tracking-tight">Tryout & Latihan Soal</h1>
            <p class="mt-4 text-base md:text-lg text-slate-500 max-w-2xl mx-auto font-medium">
                Uji kemampuanmu dengan mengerjakan berbagai latihan soal secara gratis. Tidak perlu mendaftar atau
                login, langsung mulai!
            </p>
        </div>

        {{-- JIKA BELUM ADA SOAL --}}
        @if($publicExams->isEmpty())
        <div class="text-center py-16 bg-white rounded-[2.5rem] border border-slate-200 shadow-sm max-w-3xl mx-auto">
            <div
                class="w-24 h-24 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-6 text-4xl border border-slate-100 shadow-inner">
                <i class="fas fa-folder-open"></i>
            </div>
            <h3 class="text-2xl font-black text-slate-800">Belum Ada Ujian Publik</h3>
            <p class="text-slate-500 mt-3 text-base font-semibold">Admin belum mempublikasikan soal untuk umum. Silakan
                kembali lagi nanti.</p>
        </div>
        @else

        {{-- GRID KARTU UJIAN --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($publicExams as $exam)
            <div
                class="bg-white rounded-[2rem] p-8 border border-slate-200 hover:shadow-2xl hover:shadow-indigo-500/20 hover:border-indigo-200 hover:-translate-y-1.5 transition-all duration-300 group flex flex-col h-full relative overflow-hidden">

                <div
                    class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-indigo-500 via-purple-500 to-cyan-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                </div>

                <div class="flex items-start justify-between mb-5 relative z-10">
                    <div
                        class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-2xl shadow-sm border border-indigo-100 group-hover:scale-110 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300">
                        <i class="fas fa-file-signature"></i>
                    </div>

                    @if($exam->require_token)
                    <span
                        class="bg-amber-50 text-amber-600 px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-1.5 border border-amber-200 shadow-sm">
                        <i class="fas fa-lock text-amber-500"></i> Ada Token
                    </span>
                    @else
                    <span
                        class="bg-emerald-50 text-emerald-600 px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-1.5 border border-emerald-200 shadow-sm">
                        <i class="fas fa-globe text-emerald-500"></i> Terbuka
                    </span>
                    @endif
                </div>

                <div class="flex-1 relative z-10">
                    <h3
                        class="text-xl font-black text-slate-800 mb-3 group-hover:text-indigo-600 transition-colors line-clamp-2">
                        {{ $exam->title }}
                    </h3>
                    <p class="text-sm text-slate-500 mb-8 line-clamp-3 leading-relaxed font-medium">
                        {{ $exam->description ?? 'Tidak ada deskripsi tambahan untuk ujian ini.' }}
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-8 relative z-10">
                    <div class="bg-slate-50 rounded-2xl p-4 text-center border border-slate-100">
                        <i class="fas fa-stopwatch text-indigo-400 mb-2 text-lg"></i>
                        <div class="text-[10px] uppercase font-black tracking-widest text-slate-400 mb-0.5">Waktu</div>
                        <div class="text-sm font-black text-slate-700">{{ $exam->duration_minutes }} Menit</div>
                    </div>
                    <div class="bg-slate-50 rounded-2xl p-4 text-center border border-slate-100">
                        <i class="fas fa-list-ul text-emerald-400 mb-2 text-lg"></i>
                        <div class="text-[10px] uppercase font-black tracking-widest text-slate-400 mb-0.5">Jumlah</div>
                        <div class="text-sm font-black text-slate-700">{{ $exam->questions_count }} Soal</div>
                    </div>
                </div>

                <a href="{{ route('public.exam.run', $exam->id) }}"
                    class="relative z-10 w-full bg-slate-900 hover:bg-indigo-600 text-white font-bold py-4 px-4 rounded-xl text-sm text-center transition-all shadow-md hover:shadow-xl flex items-center justify-center gap-2 group/btn">
                    Mulai Ujian <i
                        class="fas fa-arrow-right text-xs group-hover/btn:translate-x-1.5 transition-transform"></i>
                </a>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</x-public-layout>