<x-guest-layout>
    <style>
        /* MENGAMBIL ALIH LAYOUT UTAMA AGAR BISA DI-SCROLL */
        body {
            overflow-y: auto !important;
            /* Memaksa scrollbar muncul jika konten panjang */
            align-items: flex-start !important;
            /* Mencegah bagian atas terpotong saat layar penuh */
            padding-top: 3rem !important;
            padding-bottom: 3rem !important;
        }

        /* MENGAMBIL ALIH LEBAR KOTAK KACA */
        @media (min-width: 640px) {
            .sm\:max-w-md {
                max-width: 72rem !important;
                /* setara dengan max-w-6xl */
                padding: 3rem !important;
            }
        }
    </style>

    {{-- KONTEN DI DALAM KOTAK GLASSMORPHISM --}}
    <div class="w-full">

        {{-- JUDUL HALAMAN --}}
        <div class="text-center mb-10 mt-2">
            <div
                class="inline-block bg-indigo-50 text-indigo-600 px-4 py-1.5 rounded-full text-[10px] font-black tracking-widest uppercase mb-4 border border-indigo-100">
                <i class="fas fa-rocket mr-1"></i> Mode Pengunjung Terbuka
            </div>
            <h2 class="text-3xl md:text-4xl font-black text-slate-800 tracking-tight">Tryout & Latihan Soal</h2>
            <p class="mt-3 text-sm md:text-base text-slate-500 max-w-2xl mx-auto font-medium">
                Uji kemampuanmu dengan mengerjakan berbagai latihan soal secara gratis. Tidak perlu mendaftar atau
                login, langsung mulai!
            </p>
        </div>

        {{-- JIKA BELUM ADA SOAL --}}
        @if($publicExams->isEmpty())
        <div class="text-center py-12 bg-slate-50/50 rounded-3xl border border-slate-100 border-dashed">
            <div
                class="w-20 h-20 bg-white text-slate-300 rounded-full flex items-center justify-center mx-auto mb-5 text-3xl shadow-sm border border-slate-200">
                <i class="fas fa-folder-open"></i>
            </div>
            <h3 class="text-xl font-black text-slate-700">Belum Ada Ujian Publik</h3>
            <p class="text-slate-400 mt-2 text-sm font-bold">Admin belum mempublikasikan soal untuk umum. Silakan
                kembali lagi nanti.</p>
        </div>
        @else

        {{-- GRID KARTU UJIAN --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($publicExams as $exam)
            <div
                class="bg-slate-50/70 rounded-[1.5rem] p-6 border border-slate-200 hover:shadow-xl hover:shadow-indigo-500/10 hover:border-indigo-300 hover:-translate-y-1 transition-all duration-300 group flex flex-col h-full relative">

                <div class="flex items-start justify-between mb-4">
                    <div
                        class="w-12 h-12 bg-white text-indigo-600 rounded-xl flex items-center justify-center text-xl shadow-sm border border-slate-100 group-hover:scale-110 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                        <i class="fas fa-file-signature"></i>
                    </div>

                    @if($exam->require_token)
                    <span
                        class="bg-amber-100 text-amber-700 px-3 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-widest flex items-center gap-1.5 border border-amber-200">
                        <i class="fas fa-lock text-amber-500"></i> Ada Token
                    </span>
                    @else
                    <span
                        class="bg-emerald-100 text-emerald-700 px-3 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-widest flex items-center gap-1.5 border border-emerald-200">
                        <i class="fas fa-globe text-emerald-500"></i> Akses Bebas
                    </span>
                    @endif
                </div>

                <h3
                    class="text-lg font-black text-slate-800 mb-2 group-hover:text-indigo-600 transition-colors line-clamp-2">
                    {{ $exam->title }}
                </h3>

                <p class="text-xs text-slate-500 mb-6 line-clamp-3 leading-relaxed flex-1 font-semibold">
                    {{ $exam->description ?? 'Tidak ada deskripsi khusus untuk ujian ini.' }}
                </p>

                <div class="grid grid-cols-2 gap-3 mb-6">
                    <div class="bg-white rounded-xl p-3 text-center border border-slate-100 shadow-sm">
                        <i class="fas fa-stopwatch text-indigo-400 mb-1.5 text-base"></i>
                        <div class="text-[9px] uppercase font-black tracking-widest text-slate-400 mb-0.5">Waktu</div>
                        <div class="text-xs font-black text-slate-700">{{ $exam->duration_minutes }} Mnt</div>
                    </div>
                    <div class="bg-white rounded-xl p-3 text-center border border-slate-100 shadow-sm">
                        <i class="fas fa-list-ul text-emerald-400 mb-1.5 text-base"></i>
                        <div class="text-[9px] uppercase font-black tracking-widest text-slate-400 mb-0.5">Jumlah</div>
                        <div class="text-xs font-black text-slate-700">{{ $exam->questions_count }} Soal</div>
                    </div>
                </div>

                <a href="{{ route('public.exam.run', $exam->id) }}"
                    class="w-full bg-slate-900 hover:bg-indigo-600 text-white font-bold py-3.5 px-4 rounded-xl text-sm text-center transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2 group/btn">
                    Mulai Kerjakan <i
                        class="fas fa-arrow-right text-xs group-hover/btn:translate-x-1 transition-transform"></i>
                </a>
            </div>
            @endforeach
        </div>

        @endif
    </div>
</x-guest-layout>