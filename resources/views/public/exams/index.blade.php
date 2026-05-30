<x-guest-layout>
    <div class="min-h-screen bg-slate-50 py-16 px-4 sm:px-6 lg:px-8 font-sans">
        <div class="max-w-7xl mx-auto">

            {{-- HEADER HALAMAN --}}
            <div class="text-center mb-16 relative">
                <div
                    class="inline-block bg-indigo-100 text-indigo-600 px-4 py-1.5 rounded-full text-xs font-black tracking-widest uppercase mb-4 shadow-sm border border-indigo-200">
                    Mode Pengunjung
                </div>
                <h1 class="text-4xl md:text-5xl font-black text-slate-900 tracking-tight">Tryout & Latihan Soal Terbuka
                </h1>
                <p class="mt-5 text-lg text-slate-500 max-w-2xl mx-auto">
                    Uji kemampuanmu dengan mengerjakan berbagai latihan soal secara gratis. Tidak perlu mendaftar atau
                    login, langsung mulai!
                </p>
            </div>

            {{-- KONDISI JIKA KOSONG --}}
            @if($publicExams->isEmpty())
            <div class="bg-white rounded-[2.5rem] p-12 text-center shadow-sm border border-slate-200 max-w-2xl mx-auto">
                <div
                    class="w-24 h-24 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-6 text-4xl shadow-inner border-2 border-slate-100">
                    <i class="fas fa-folder-open"></i>
                </div>
                <h3 class="text-2xl font-black text-slate-800">Belum Ada Ujian Publik</h3>
                <p class="text-slate-500 mt-3 text-lg">Admin belum mempublikasikan soal untuk umum. Silakan kembali lagi
                    nanti.</p>
            </div>
            @else

            {{-- GRID DAFTAR UJIAN --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($publicExams as $exam)
                <div
                    class="bg-white rounded-[2rem] p-8 shadow-sm border border-slate-200 hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 group flex flex-col h-full relative overflow-hidden">

                    <div
                        class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500">
                    </div>

                    <div class="flex-1">
                        <div class="flex items-center justify-between mb-5">
                            <div
                                class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-xl shadow-sm border border-indigo-100 group-hover:scale-110 transition-transform">
                                <i class="fas fa-file-signature"></i>
                            </div>
                            @if($exam->require_token)
                            <span
                                class="bg-amber-50 text-amber-600 px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-1.5 border border-amber-100"
                                title="Membutuhkan Token">
                                <i class="fas fa-lock"></i> Ada Token
                            </span>
                            @else
                            <span
                                class="bg-emerald-50 text-emerald-600 px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-1.5 border border-emerald-100"
                                title="Akses Bebas">
                                <i class="fas fa-globe"></i> Terbuka
                            </span>
                            @endif
                        </div>

                        <h3
                            class="text-xl font-black text-slate-800 mb-3 group-hover:text-indigo-600 transition-colors line-clamp-2">
                            {{ $exam->title }}
                        </h3>

                        <p class="text-sm text-slate-500 mb-8 line-clamp-3 leading-relaxed">
                            {{ $exam->description ?? 'Tidak ada deskripsi tambahan untuk ujian ini.' }}
                        </p>

                        <div class="grid grid-cols-2 gap-4 mb-8">
                            <div class="bg-slate-50 rounded-2xl p-4 text-center border border-slate-100">
                                <i class="fas fa-stopwatch text-indigo-400 mb-2 text-lg"></i>
                                <div class="text-[10px] uppercase font-black tracking-widest text-slate-400 mb-0.5">
                                    Waktu</div>
                                <div class="text-sm font-bold text-slate-700">{{ $exam->duration_minutes }} Menit</div>
                            </div>
                            <div class="bg-slate-50 rounded-2xl p-4 text-center border border-slate-100">
                                <i class="fas fa-list-ul text-emerald-400 mb-2 text-lg"></i>
                                <div class="text-[10px] uppercase font-black tracking-widest text-slate-400 mb-0.5">
                                    Jumlah</div>
                                <div class="text-sm font-bold text-slate-700">{{ $exam->questions_count }} Soal</div>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('public.exam.run', $exam->id) }}"
                        class="w-full bg-slate-900 hover:bg-indigo-600 text-white font-bold py-4 px-6 rounded-xl text-center transition-all shadow-lg flex items-center justify-center gap-2 group/btn">
                        Mulai Kerjakan <i
                            class="fas fa-arrow-right text-sm group-hover/btn:translate-x-1 transition-transform"></i>
                    </a>
                </div>
                @endforeach
            </div>
            @endif

        </div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</x-guest-layout>