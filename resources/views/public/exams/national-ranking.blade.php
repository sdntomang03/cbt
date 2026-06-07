<x-public-layout :page-title="'Papan Peringkat Nasional - CBT Pro'"
    :meta-description="'Lihat daftar peringkat nasional siswa terbaik berdasarkan total akumulasi poin simulasi try out dan ulangan harian.'">

    <style>
        /* Podium glow effects */
        .glow-gold {
            box-shadow: 0 0 30px rgba(251, 191, 36, .45), 0 0 60px rgba(251, 191, 36, .15);
        }

        .glow-silver {
            box-shadow: 0 0 20px rgba(148, 163, 184, .40), 0 0 40px rgba(148, 163, 184, .10);
        }

        .glow-bronze {
            box-shadow: 0 0 20px rgba(251, 146, 60, .35), 0 0 40px rgba(251, 146, 60, .10);
        }

        /* Shimmer animasi nama juara 1 */
        @keyframes shimmer {
            0% {
                background-position: -200% center;
            }

            100% {
                background-position: 200% center;
            }
        }

        .text-shimmer {
            background: linear-gradient(90deg, #fbbf24, #fef08a, #fbbf24, #f59e0b);
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: shimmer 3s linear infinite;
        }

        /* Animasi bintang melayang */
        @keyframes float {

            0%,
            100% {
                transform: translateY(0) rotate(0deg);
                opacity: .7;
            }

            50% {
                transform: translateY(-12px) rotate(15deg);
                opacity: 1;
            }
        }

        .star-float {
            animation: float 3s ease-in-out infinite;
        }

        .star-float-delay {
            animation: float 3s ease-in-out .8s infinite;
        }

        /* Rank badge 4-10 */
        .rank-row:hover {
            background-color: #f8fafc;
        }
    </style>

    <div class="bg-slate-50 min-h-screen pb-20">

        {{-- ========================================== --}}
        {{-- HERO SECTION --}}
        {{-- ========================================== --}}
        <div class="w-full bg-slate-900 relative overflow-hidden pt-16 pb-40">
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-900 via-slate-900 to-slate-900"></div>

            {{-- dekorasi blur --}}
            <div
                class="absolute top-[-20%] left-[-10%] w-96 h-96 bg-amber-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20">
            </div>
            <div
                class="absolute bottom-[-10%] right-[-5%]  w-72 h-72 bg-indigo-600 rounded-full mix-blend-multiply filter blur-3xl opacity-25">
            </div>

            {{-- bintang dekoratif --}}
            <div class="absolute top-10 left-1/4 text-amber-400 text-2xl star-float select-none">★</div>
            <div class="absolute top-20 right-1/3 text-amber-300 text-lg star-float-delay select-none">✦</div>
            <div class="absolute top-14 right-1/4 text-yellow-400 text-xl star-float select-none"
                style="animation-delay:.4s">★</div>

            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
                <span
                    class="inline-block px-4 py-1.5 rounded-full bg-amber-500/20 text-amber-300 text-xs font-black tracking-widest uppercase mb-4 border border-amber-400/30">
                    <i class="fas fa-crown mr-1"></i> Leaderboard
                </span>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-white tracking-tight mb-4 leading-tight">
                    Peringkat Nasional
                </h1>
                <p class="text-lg text-slate-300 max-w-2xl mx-auto font-medium">
                    10 siswa terbaik dengan akumulasi poin tertinggi dari seluruh simulasi try out dan latihan soal.
                </p>
            </div>
        </div>

        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 -mt-32 relative z-20">

            {{-- ========================================== --}}
            {{-- KARTU PERINGKAT USER LOGIN --}}
            {{-- ========================================== --}}
            @auth
            <div
                class="bg-indigo-600 rounded-2xl p-6 mb-8 text-white shadow-xl flex flex-col sm:flex-row items-center justify-between gap-4 border border-indigo-500">
                <div class="flex items-center gap-4">
                    <div
                        class="w-14 h-14 rounded-full bg-white/20 flex items-center justify-center font-black text-xl border-2 border-white/30">
                        #{{ $currentUserRank }}
                    </div>
                    <div>
                        <p class="text-indigo-200 text-xs font-bold uppercase tracking-wider">Peringkat Anda</p>
                        <h3 class="text-xl font-black">{{ $currentUserData->name }}</h3>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-indigo-200 text-xs font-bold uppercase tracking-wider">Total Poin</p>
                    <div class="text-3xl font-black tracking-tighter">{{ number_format($currentUserData->total_poin) }}
                    </div>
                </div>
            </div>
            @endauth

            @if($topUsers->isEmpty())
            {{-- ========================================== --}}
            {{-- EMPTY STATE --}}
            {{-- ========================================== --}}
            <div class="bg-white rounded-[2rem] border border-slate-200 p-16 text-center shadow-sm">
                <i class="fas fa-trophy text-6xl text-slate-200 mb-4 block"></i>
                <h3 class="text-xl font-bold text-slate-700">Belum Ada Data</h3>
                <p class="text-slate-500 mt-2">Belum ada peserta yang menyelesaikan ujian dan mendapatkan poin.</p>
            </div>
            @else

            {{-- ========================================== --}}
            {{-- PODIUM 3 BESAR --}}
            {{-- ========================================== --}}
            @php
            $first = $topUsers->get(0);
            $second = $topUsers->get(1);
            $third = $topUsers->get(2);
            @endphp

            @if($first)
            <div class="mb-6">
                <p class="text-center text-xs font-black text-amber-400 uppercase tracking-widest mb-4">
                    <i class="fas fa-star mr-1"></i> Hall of Fame <i class="fas fa-star ml-1"></i>
                </p>

                {{-- Podium Row: 2 - 1 - 3 --}}
                <div class="flex items-end justify-center gap-3 sm:gap-5">

                    {{-- ── PERINGKAT 2 ── --}}
                    @if($second)
                    <div class="flex flex-col items-center flex-1 max-w-[180px]">
                        {{-- Avatar --}}
                        <div class="relative mb-3">
                            <div
                                class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-gradient-to-br from-slate-300 to-slate-400 flex items-center justify-center text-white text-2xl sm:text-3xl font-black border-4 border-slate-300 glow-silver">
                                {{ mb_substr($second->name, 0, 1) }}
                            </div>
                            <div
                                class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-7 h-7 bg-slate-300 rounded-full border-2 border-white flex items-center justify-center">
                                <i class="fas fa-medal text-slate-500 text-xs"></i>
                            </div>
                        </div>
                        <p class="text-sm font-black text-slate-700 text-center truncate w-full px-1 mt-1">{{
                            $second->name }}</p>
                        <p class="text-xs font-bold text-slate-400 mb-2">{{ number_format($second->total_poin) }} poin
                        </p>
                        {{-- Tiang podium --}}
                        <div
                            class="w-full bg-gradient-to-b from-slate-300 to-slate-400 rounded-t-xl flex items-center justify-center py-4 glow-silver">
                            <span class="text-white font-black text-3xl">2</span>
                        </div>
                    </div>
                    @endif

                    {{-- ── PERINGKAT 1 (tengah, lebih tinggi) ── --}}
                    <div class="flex flex-col items-center flex-1 max-w-[200px]">
                        {{-- Mahkota --}}
                        <div class="text-3xl mb-1 star-float">👑</div>
                        {{-- Avatar --}}
                        <div class="relative mb-3">
                            <div
                                class="w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-gradient-to-br from-amber-400 to-yellow-500 flex items-center justify-center text-white text-3xl sm:text-4xl font-black border-4 border-amber-300 glow-gold">
                                {{ mb_substr($first->name, 0, 1) }}
                            </div>
                            <div
                                class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-8 h-8 bg-amber-400 rounded-full border-2 border-white flex items-center justify-center shadow-md">
                                <i class="fas fa-crown text-white text-sm"></i>
                            </div>
                        </div>
                        <p class="text-sm sm:text-base font-black text-center truncate w-full px-1 mt-1 text-shimmer">{{
                            $first->name }}</p>
                        <p class="text-xs font-bold text-amber-500 mb-2">{{ number_format($first->total_poin) }} poin
                        </p>
                        {{-- Tiang podium (paling tinggi) --}}
                        <div
                            class="w-full bg-gradient-to-b from-amber-400 to-yellow-500 rounded-t-xl flex items-center justify-center py-7 glow-gold">
                            <span class="text-white font-black text-4xl">1</span>
                        </div>
                    </div>

                    {{-- ── PERINGKAT 3 ── --}}
                    @if($third)
                    <div class="flex flex-col items-center flex-1 max-w-[180px]">
                        {{-- Avatar --}}
                        <div class="relative mb-3">
                            <div
                                class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-gradient-to-br from-orange-300 to-orange-400 flex items-center justify-center text-white text-2xl sm:text-3xl font-black border-4 border-orange-300 glow-bronze">
                                {{ mb_substr($third->name, 0, 1) }}
                            </div>
                            <div
                                class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-7 h-7 bg-orange-400 rounded-full border-2 border-white flex items-center justify-center">
                                <i class="fas fa-medal text-white text-xs"></i>
                            </div>
                        </div>
                        <p class="text-sm font-black text-slate-700 text-center truncate w-full px-1 mt-1">{{
                            $third->name }}</p>
                        <p class="text-xs font-bold text-slate-400 mb-2">{{ number_format($third->total_poin) }} poin
                        </p>
                        {{-- Tiang podium --}}
                        <div
                            class="w-full bg-gradient-to-b from-orange-300 to-orange-400 rounded-t-xl flex items-center justify-center py-2 glow-bronze">
                            <span class="text-white font-black text-3xl">3</span>
                        </div>
                    </div>
                    @endif

                </div>
            </div>
            @endif

            {{-- ========================================== --}}
            {{-- DAFTAR PERINGKAT 4 – 10 --}}
            {{-- ========================================== --}}
            @if($topUsers->count() > 3)
            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-2">
                    <i class="fas fa-list-ol text-slate-400"></i>
                    <h3 class="font-black text-slate-700 text-sm uppercase tracking-wider">Peringkat 4 – 10</h3>
                </div>

                <div class="flex flex-col divide-y divide-slate-100">
                    @foreach($topUsers->skip(3) as $index => $user)
                    @php
                    $rank = $index + 4;
                    $isCurrentUser = auth()->check() && auth()->id() === $user->id;
                    @endphp
                    <div
                        class="rank-row flex items-center px-6 py-4 transition-colors {{ $isCurrentUser ? 'bg-indigo-50' : '' }}">

                        {{-- Nomor --}}
                        <div class="w-10 flex-shrink-0 text-2xl font-black text-slate-200 text-center">
                            {{ $rank }}
                        </div>

                        {{-- Inisial avatar --}}
                        <div
                            class="w-10 h-10 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center font-black text-slate-500 text-sm ml-3 flex-shrink-0">
                            {{ mb_substr($user->name, 0, 1) }}
                        </div>

                        {{-- Nama --}}
                        <div class="flex-1 px-4 min-w-0">
                            <h4 class="font-black text-slate-800 text-sm truncate flex items-center gap-2">
                                {{ $user->name }}
                                @if($isCurrentUser)
                                <span
                                    class="bg-indigo-600 text-white text-[9px] px-2 py-0.5 rounded-full uppercase tracking-wider font-bold flex-shrink-0">Anda</span>
                                @endif
                            </h4>
                        </div>

                        {{-- Poin --}}
                        <div class="text-right flex-shrink-0">
                            <div class="text-lg font-black text-indigo-600 tracking-tighter">{{
                                number_format($user->total_poin) }}</div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Poin</div>
                        </div>

                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @endif {{-- end if topUsers --}}

            <div class="mt-8 text-center">
                <a href="{{ route('public.exams.index') }}"
                    class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white font-bold py-3.5 px-6 rounded-xl transition-all shadow-md">
                    <i class="fas fa-arrow-left text-sm"></i> Kembali ke Try Out
                </a>
            </div>

        </div>
    </div>
</x-public-layout>