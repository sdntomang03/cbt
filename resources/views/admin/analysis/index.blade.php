<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center w-full gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ url()->previous() }}"
                    class="w-10 h-10 rounded-xl bg-white text-slate-500 hover:text-indigo-600 shadow-sm border border-slate-200 flex items-center justify-center transition shrink-0">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h2 class="font-black text-2xl text-slate-800 tracking-tight leading-tight">Pilih Sesi Analisis</h2>
                    <p class="text-slate-500 font-bold text-sm mt-0.5">Pilih sesi ujian yang ingin dianalisis butir
                        soalnya</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8 min-h-screen">
        <div class="w-full max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- KARTU INFORMASI UJIAN --}}
            <div
                class="bg-indigo-600 rounded-[2rem] p-6 sm:p-8 text-white shadow-xl shadow-indigo-200 mb-8 flex flex-col sm:flex-row items-start sm:items-center gap-5 sm:gap-6 relative overflow-hidden">
                {{-- Ornamen Latar --}}
                <div
                    class="absolute top-0 right-0 -mt-10 -mr-10 w-40 h-40 bg-white opacity-10 rounded-full blur-3xl pointer-events-none">
                </div>

                <div
                    class="w-16 h-16 rounded-2xl bg-white/20 flex items-center justify-center text-3xl shrink-0 shadow-inner">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="relative z-10">
                    <h3 class="text-2xl sm:text-3xl font-black tracking-tight mb-2">{{ $exam->title }}</h3>
                    <div class="flex flex-wrap items-center gap-3 text-indigo-100 font-semibold text-sm">
                        <span class="flex items-center gap-1.5 bg-indigo-700/50 px-3 py-1 rounded-lg">
                            <i class="fas fa-layer-group"></i> {{ $sessions->count() }} Sesi Tersedia
                        </span>
                        @if($exam->duration_minutes)
                        <span class="flex items-center gap-1.5 bg-indigo-700/50 px-3 py-1 rounded-lg">
                            <i class="far fa-clock"></i> {{ $exam->duration_minutes }} Menit
                        </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- DAFTAR SESI --}}
            @if($sessions->isEmpty())
            <div
                class="bg-white rounded-[2rem] p-12 shadow-sm border border-slate-200 text-center flex flex-col items-center justify-center">
                <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mb-6">
                    <i class="fas fa-folder-open text-4xl text-slate-300"></i>
                </div>
                <h3 class="text-xl font-black text-slate-700 mb-2">Belum Ada Sesi</h3>
                <p class="text-slate-400 font-bold max-w-sm">Ujian ini belum memiliki sesi yang dijadwalkan. Buat sesi
                    terlebih dahulu di menu Manajemen Ujian.</p>
            </div>
            @else
            <div class="flex items-center justify-between mb-4 px-2">
                <h4 class="font-black text-slate-700 text-sm uppercase tracking-widest">Daftar Sesi Ujian</h4>
                <span class="text-xs font-bold text-slate-400"><i class="fas fa-info-circle text-indigo-400 mr-1"></i>
                    Syarat Analisis: Min. 2 Peserta Selesai</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                @foreach($sessions as $session)
                @php
                $completed = $session->completed_count ?? 0;
                $canAnalyze = $completed >= 2;
                @endphp

                @if($canAnalyze)
                {{-- KARTU SESI AKTIF (Bisa di-klik) --}}
                <a href="{{ route('admin.analysis.show', [$exam, $session]) }}"
                    class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200 hover:border-indigo-400 hover:shadow-md transition-all transform hover:-translate-y-1 group flex flex-col h-full relative overflow-hidden">

                    {{-- Aksen Garis Kiri --}}
                    <div
                        class="absolute left-0 top-0 bottom-0 w-1 bg-emerald-400 group-hover:bg-indigo-500 transition-colors">
                    </div>

                    <div class="flex justify-between items-start mb-4 pl-2">
                        <div class="min-w-0 pr-4">
                            <h4 class="font-black text-lg text-slate-800 truncate mb-1">{{ $session->session_name ??
                                'Sesi ' . $loop->iteration }}</h4>
                            <div class="text-xs font-bold text-slate-500 flex items-center gap-2">
                                <i class="far fa-calendar-alt text-slate-400"></i>
                                {{ \Carbon\Carbon::parse($session->start_time)->format('d M Y, H:i') }} WIB
                            </div>
                        </div>
                        <div
                            class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-500 flex items-center justify-center shrink-0 group-hover:bg-indigo-500 group-hover:text-white transition-colors">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </div>

                    <div class="mt-auto pt-4 border-t border-slate-100 flex items-center justify-between pl-2">
                        <div class="text-xs font-black text-slate-400 flex items-center gap-1.5">
                            <i class="fas fa-key"></i> Token: <span class="text-slate-700 font-mono tracking-widest">{{
                                $session->token ?? '-' }}</span>
                        </div>
                        <div
                            class="bg-emerald-50 text-emerald-600 px-3 py-1 rounded-lg text-xs font-black flex items-center gap-1.5">
                            <i class="fas fa-check-circle"></i> {{ $completed }} Selesai
                        </div>
                    </div>
                </a>
                @else
                {{-- KARTU SESI DISABLE (Tidak memenuhi syarat) --}}
                <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200 opacity-75 relative flex flex-col h-full cursor-not-allowed"
                    title="Membutuhkan minimal 2 peserta yang selesai untuk bisa dianalisis">

                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-slate-300"></div>

                    <div class="flex justify-between items-start mb-4 pl-2">
                        <div class="min-w-0 pr-4">
                            <h4 class="font-black text-lg text-slate-600 truncate mb-1">{{ $session->session_name ??
                                'Sesi ' . $loop->iteration }}</h4>
                            <div class="text-xs font-bold text-slate-400 flex items-center gap-2">
                                <i class="far fa-calendar-alt opacity-70"></i>
                                {{ \Carbon\Carbon::parse($session->start_time)->format('d M Y, H:i') }} WIB
                            </div>
                        </div>
                        <div
                            class="w-8 h-8 rounded-full bg-slate-200 text-slate-400 flex items-center justify-center shrink-0">
                            <i class="fas fa-lock"></i>
                        </div>
                    </div>

                    <div class="mt-auto pt-4 border-t border-slate-200 flex items-center justify-between pl-2">
                        <div class="text-xs font-black text-slate-400 flex items-center gap-1.5">
                            <i class="fas fa-key"></i> Token: <span class="font-mono tracking-widest">{{ $session->token
                                ?? '-' }}</span>
                        </div>
                        <div
                            class="bg-slate-200 text-slate-500 px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fas fa-users"></i> {{ $completed }}/2 Selesai
                        </div>
                    </div>
                </div>
                @endif

                @endforeach
            </div>
            @endif

        </div>
    </div>
</x-app-layout>