<x-public-layout>
    <div class="min-h-screen bg-slate-50 py-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto space-y-6">

            {{-- Header --}}
            <div class="bg-indigo-600 rounded-2xl p-8 text-white relative overflow-hidden">
                <div
                    class="absolute top-[-30%] right-[-10%] w-64 h-64 bg-indigo-500 rounded-full mix-blend-multiply filter blur-3xl opacity-40">
                </div>
                <div
                    class="absolute bottom-[-30%] left-[-5%] w-48 h-48 bg-purple-500 rounded-full mix-blend-multiply filter blur-3xl opacity-40">
                </div>

                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center text-2xl">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div>
                            <p class="text-indigo-200 text-xs font-bold uppercase tracking-widest">Peringkat Nasional
                            </p>
                            <h1 class="text-2xl font-black">{{ $exam->title }}</h1>
                        </div>
                    </div>
                    <div class="flex items-center gap-6 mt-4 text-sm text-indigo-200 font-medium">
                        <span><i class="fas fa-users mr-1"></i> {{ $results->count() }} Peserta</span>
                        <span><i class="fas fa-stopwatch mr-1"></i> {{ $exam->duration_minutes }} Menit</span>
                        <span><i class="fas fa-list-ul mr-1"></i> {{ $exam->questions()->count() }} Soal</span>
                    </div>
                </div>
            </div>

            {{-- Podium Top 3 --}}
            @if($results->count() >= 3)
            <div class="bg-white rounded-2xl shadow p-6">
                <h2 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-6 text-center">🏆 Podium
                    Teratas</h2>
                <div class="flex items-end justify-center gap-4">

                    {{-- Rank 2 --}}
                    <div class="flex flex-col items-center gap-2 flex-1">
                        <div
                            class="w-14 h-14 bg-slate-100 rounded-full flex items-center justify-center text-2xl font-black text-slate-500">
                            2</div>
                        <div class="text-center">
                            <div class="font-bold text-sm text-slate-800 truncate max-w-[100px]">{{
                                $results[1]->nama_peserta }}</div>
                            <div class="text-xs text-slate-400 truncate max-w-[100px]">{{ $results[1]->asal_sekolah }}
                            </div>
                            <div class="text-lg font-black text-indigo-600 mt-1">{{ $results[1]->score }}<span
                                    class="text-xs font-normal text-slate-400">/100</span></div>
                        </div>
                        <div class="w-full bg-slate-200 rounded-t-xl h-16"></div>
                    </div>

                    {{-- Rank 1 --}}
                    <div class="flex flex-col items-center gap-2 flex-1">
                        <div class="text-3xl">🥇</div>
                        <div
                            class="w-16 h-16 bg-amber-400 rounded-full flex items-center justify-center text-2xl font-black text-white shadow-lg">
                            1</div>
                        <div class="text-center">
                            <div class="font-bold text-sm text-slate-800 truncate max-w-[100px]">{{
                                $results[0]->nama_peserta }}</div>
                            <div class="text-xs text-slate-400 truncate max-w-[100px]">{{ $results[0]->asal_sekolah }}
                            </div>
                            <div class="text-lg font-black text-indigo-600 mt-1">{{ $results[0]->score }}<span
                                    class="text-xs font-normal text-slate-400">/100</span></div>
                        </div>
                        <div class="w-full bg-amber-400 rounded-t-xl h-24"></div>
                    </div>

                    {{-- Rank 3 --}}
                    <div class="flex flex-col items-center gap-2 flex-1">
                        <div
                            class="w-14 h-14 bg-orange-100 rounded-full flex items-center justify-center text-2xl font-black text-orange-500">
                            3</div>
                        <div class="text-center">
                            <div class="font-bold text-sm text-slate-800 truncate max-w-[100px]">{{
                                $results[2]->nama_peserta }}</div>
                            <div class="text-xs text-slate-400 truncate max-w-[100px]">{{ $results[2]->asal_sekolah }}
                            </div>
                            <div class="text-lg font-black text-indigo-600 mt-1">{{ $results[2]->score }}<span
                                    class="text-xs font-normal text-slate-400">/100</span></div>
                        </div>
                        <div class="w-full bg-orange-300 rounded-t-xl h-10"></div>
                    </div>

                </div>
            </div>
            @endif

            {{-- Kartu Ranking Saya --}}
            @if($userResult)
            <div class="bg-indigo-50 border-2 border-indigo-300 rounded-2xl p-5 flex items-center gap-4">
                <div
                    class="w-14 h-14 bg-indigo-600 text-white rounded-xl flex items-center justify-center font-black text-xl flex-shrink-0">
                    #{{ $userResult->rank }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-xs text-indigo-500 font-bold uppercase tracking-widest mb-0.5">Peringkat Anda</div>
                    <div class="font-black text-slate-800 truncate">{{ $userResult->nama_peserta }}</div>
                    <div class="text-sm text-slate-500 truncate">{{ $userResult->asal_sekolah }}</div>
                </div>
                <div class="text-right flex-shrink-0">
                    <div class="text-3xl font-black text-indigo-600">{{ $userResult->score }}</div>
                    <div class="text-xs text-slate-400">dari 100</div>
                    @php
                    $menit = floor($userResult->duration_seconds / 60);
                    $detik = $userResult->duration_seconds % 60;
                    @endphp
                    <div class="text-xs text-slate-400 mt-1"><i class="fas fa-clock mr-1"></i>{{ $menit }}m {{ $detik
                        }}d</div>
                </div>
            </div>
            @endif

            {{-- Tabel Ranking Lengkap --}}
            <div class="bg-white rounded-2xl shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="font-bold text-slate-700">Semua Peserta</h2>
                    <span class="text-xs text-slate-400">Diurutkan: Nilai ↓ · Waktu ↑</span>
                </div>

                @if($results->isEmpty())
                <div class="py-16 text-center text-slate-400">
                    <i class="fas fa-inbox text-4xl mb-3"></i>
                    <p class="font-medium">Belum ada peserta yang mengerjakan ujian ini.</p>
                </div>
                @else
                <div class="divide-y divide-slate-50">
                    @foreach($results as $result)
                    @php
                    $isMe = $userResult && $result->id === $userResult->id;
                    $menit = floor($result->duration_seconds / 60);
                    $detik = $result->duration_seconds % 60;
                    $medal = match($result->rank) {
                    1 => '🥇',
                    2 => '🥈',
                    3 => '🥉',
                    default => null,
                    };
                    @endphp
                    <div
                        class="flex items-center gap-4 px-6 py-4 {{ $isMe ? 'bg-indigo-50' : 'hover:bg-slate-50' }} transition-colors">

                        {{-- Rank --}}
                        <div class="w-10 text-center flex-shrink-0">
                            @if($medal)
                            <span class="text-2xl">{{ $medal }}</span>
                            @else
                            <span class="text-sm font-bold text-slate-400">#{{ $result->rank }}</span>
                            @endif
                        </div>

                        {{-- Nama & Sekolah --}}
                        <div class="flex-1 min-w-0">
                            <div class="font-bold text-slate-800 truncate flex items-center gap-2">
                                {{ $result->nama_peserta }}
                                @if($isMe)
                                <span
                                    class="text-[10px] bg-indigo-600 text-white px-2 py-0.5 rounded-full font-bold">ANDA</span>
                                @endif
                            </div>
                            <div class="text-xs text-slate-400 truncate">{{ $result->asal_sekolah }}</div>
                        </div>

                        {{-- Stats --}}
                        <div class="hidden sm:flex items-center gap-4 text-xs text-slate-500 flex-shrink-0">
                            <span class="text-emerald-600 font-semibold"><i class="fas fa-check mr-1"></i>{{
                                $result->correct_count }}</span>
                            <span class="text-rose-500 font-semibold"><i class="fas fa-times mr-1"></i>{{
                                $result->wrong_count }}</span>
                            <span class="text-slate-400"><i class="fas fa-clock mr-1"></i>{{ $menit }}m {{ $detik
                                }}d</span>
                        </div>

                        {{-- Nilai --}}
                        <div class="text-right flex-shrink-0">
                            <div
                                class="text-xl font-black {{ $result->score >= 80 ? 'text-emerald-600' : ($result->score >= 60 ? 'text-amber-500' : 'text-rose-500') }}">
                                {{ $result->score }}
                            </div>
                            <div class="text-[10px] text-slate-400">poin</div>
                        </div>

                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Tombol Aksi --}}
            <div class="flex flex-col sm:flex-row gap-3 pb-6">
                <a href="{{ route('public.exams.index') }}"
                    class="flex-1 text-center bg-white border border-slate-200 text-slate-700 font-bold rounded-xl py-3 px-5 hover:bg-slate-50 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar Ujian
                </a>
                <form action="{{ route('public.exams.restart', $exam) }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit"
                        class="w-full text-center bg-indigo-600 text-white font-bold rounded-xl py-3 px-5 hover:bg-indigo-700 transition-colors flex items-center justify-center gap-2">
                        <i class="fas fa-redo"></i> Ikuti Ujian Lagi
                    </button>
                </form>
            </div>

        </div>
    </div>
</x-public-layout>