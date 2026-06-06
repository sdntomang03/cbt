<x-public-layout :page-title="'Papan Peringkat Nasional - CBT Pro'"
    :meta-description="'Lihat daftar peringkat nasional siswa terbaik berdasarkan total akumulasi poin simulasi try out dan ulangan harian.'">
    <div class="bg-slate-50 min-h-screen pb-20">
        {{-- HERO SECTION --}}
        <div class="w-full bg-slate-900 relative overflow-hidden pt-16 pb-32">
            <div class="absolute inset-0 bg-gradient-to-r from-indigo-800 to-slate-900 opacity-90"></div>

            {{-- Hiasan Background --}}
            <div
                class="absolute top-[-20%] left-[-10%] w-96 h-96 bg-amber-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20">
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
                <span
                    class="inline-block px-4 py-1.5 rounded-full bg-amber-500/20 text-amber-300 text-xs font-black tracking-widest uppercase mb-4 border border-amber-400/30">
                    <i class="fas fa-crown mr-1"></i> Leaderboard
                </span>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-white tracking-tight mb-4 leading-tight">
                    Peringkat Nasional
                </h1>
                <p class="text-lg text-slate-300 max-w-2xl mx-auto font-medium">
                    Daftar siswa terdaftar terbaik dengan akumulasi poin tertinggi berdasarkan hasil simulasi try out
                    dan latihan soal.
                </p>
            </div>
        </div>

        {{-- KONTEN UTAMA --}}
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 -mt-20 relative z-20">

            {{-- TAMPILKAN RANKING USER SAAT INI JIKA LOGIN --}}
            @auth
            <div
                class="bg-indigo-600 rounded-2xl p-6 mb-8 text-white shadow-xl flex flex-col sm:flex-row items-center justify-between gap-4 border border-indigo-500">
                <div class="flex items-center gap-4">
                    <div
                        class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center font-black text-2xl">
                        #{{ $currentUserRank }}
                    </div>
                    <div>
                        <p class="text-indigo-200 text-sm font-bold uppercase tracking-wider">Peringkat Anda</p>
                        <h3 class="text-2xl font-black">{{ $currentUserData->name }}</h3>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-indigo-200 text-sm font-bold uppercase tracking-wider">Total Poin</p>
                    <div class="text-3xl font-black tracking-tighter">{{ number_format($currentUserData->total_poin) }}
                    </div>
                </div>
            </div>
            @endauth

            {{-- DAFTAR TOP 100 --}}
            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden">

                @if($topUsers->isEmpty())
                <div class="p-12 text-center">
                    <div
                        class="w-20 h-20 bg-slate-50 text-slate-400 rounded-full flex items-center justify-center text-4xl mx-auto mb-4">
                        <i class="fas fa-trophy text-slate-300"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-700">Belum Ada Data</h3>
                    <p class="text-slate-500 mt-2">Belum ada peserta yang menyelesaikan ujian dan mendapatkan poin.</p>
                </div>
                @else
                <div class="flex flex-col">
                    @foreach($topUsers as $index => $user)
                    @php
                    $rank = $index + 1;
                    $isCurrentUser = auth()->check() && auth()->id() === $user->id;
                    @endphp

                    <div
                        class="flex items-center p-4 sm:p-6 border-b border-slate-100 transition-colors {{ $isCurrentUser ? 'bg-indigo-50/50' : 'hover:bg-slate-50' }}">

                        {{-- ANGKA PERINGKAT / MEDALI --}}
                        <div class="w-12 sm:w-16 flex-shrink-0 flex justify-center">
                            @if($rank === 1)
                            <div
                                class="w-10 h-10 sm:w-12 sm:h-12 bg-amber-100 rounded-full flex items-center justify-center border-2 border-amber-400 shadow-[0_0_15px_rgba(251,191,36,0.4)]">
                                <i class="fas fa-crown text-amber-500 text-lg sm:text-xl"></i>
                            </div>
                            @elseif($rank === 2)
                            <div
                                class="w-10 h-10 sm:w-12 sm:h-12 bg-slate-100 rounded-full flex items-center justify-center border-2 border-slate-300 shadow-[0_0_15px_rgba(203,213,225,0.4)]">
                                <i class="fas fa-medal text-slate-400 text-lg sm:text-xl"></i>
                            </div>
                            @elseif($rank === 3)
                            <div
                                class="w-10 h-10 sm:w-12 sm:h-12 bg-orange-50 rounded-full flex items-center justify-center border-2 border-orange-300 shadow-[0_0_15px_rgba(253,186,116,0.4)]">
                                <i class="fas fa-medal text-orange-400 text-lg sm:text-xl"></i>
                            </div>
                            @else
                            <div class="text-xl sm:text-2xl font-black text-slate-300">
                                {{ $rank }}
                            </div>
                            @endif
                        </div>

                        {{-- NAMA PESERTA --}}
                        <div class="flex-1 px-4">
                            <h4 class="text-base sm:text-lg font-black text-slate-800 flex items-center gap-2">
                                {{ $user->name }}
                                @if($isCurrentUser)
                                <span
                                    class="bg-indigo-600 text-white text-[10px] px-2 py-0.5 rounded-full uppercase tracking-wider font-bold">Anda</span>
                                @endif
                            </h4>
                            {{-- Jika ada kolom asal sekolah di tabel user, tampilkan di sini. Contoh: --}}
                            {{-- <p class="text-xs sm:text-sm font-bold text-slate-400">{{ $user->asal_sekolah ?? 'Siswa
                                Publik' }}</p> --}}
                        </div>

                        {{-- POIN --}}
                        <div class="text-right">
                            <div class="text-xl sm:text-2xl font-black text-indigo-600 tracking-tighter">
                                {{ number_format($user->total_poin) }}
                            </div>
                            <div class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-widest">
                                Poin
                            </div>
                        </div>

                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            <div class="mt-8 text-center">
                <a href="{{ route('public.exams.index') }}"
                    class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white font-bold py-3.5 px-6 rounded-xl transition-all shadow-md">
                    <i class="fas fa-arrow-left text-sm"></i> Kembali ke Try Out
                </a>
            </div>

        </div>
    </div>
</x-public-layout>