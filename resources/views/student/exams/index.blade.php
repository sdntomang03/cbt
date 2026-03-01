<x-app-layout>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8fafc;
        }

        .hover-lift {
            transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .hover-lift:hover {
            transform: translateY(-4px);
        }

        [x-cloak] {
            display: none !important;
        }
    </style>

    <div class="bg-white border-b border-indigo-50 shadow-sm relative z-20" x-data="serverClock()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3 text-indigo-900">
                <div
                    class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-200">
                    <i class="fas fa-graduation-cap text-lg"></i>
                </div>
                <div class="flex flex-col">
                    <span class="font-black tracking-tight text-lg leading-none">PORTAL UJIAN</span>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">CBT System</span>
                </div>
            </div>

            <div class="flex items-center gap-4 bg-slate-50 px-5 py-2 rounded-2xl border border-slate-100">
                <div
                    class="hidden sm:flex w-8 h-8 rounded-full bg-white items-center justify-center text-indigo-600 shadow-sm">
                    <i class="fas fa-clock text-sm animate-pulse"></i>
                </div>
                <div class="flex flex-col items-end leading-tight">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide"
                        x-text="dateString">Memuat...</span>
                    <span class="font-mono font-black text-lg text-indigo-700 tracking-widest"
                        x-text="timeString">--:--:--</span>
                </div>
            </div>
        </div>
    </div>

    <div class="min-h-screen py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div
                class="bg-gradient-to-br from-indigo-600 to-violet-700 rounded-[2.5rem] p-8 md:p-10 mb-12 text-white shadow-2xl shadow-indigo-200 relative overflow-hidden">
                <div
                    class="absolute right-0 top-0 w-80 h-80 bg-white/10 rounded-full blur-3xl -mr-20 -mt-20 mix-blend-overlay">
                </div>
                <div class="absolute left-0 bottom-0 w-60 h-60 bg-indigo-500/30 rounded-full blur-3xl -ml-16 -mb-16">
                </div>

                <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-8">
                    <div>
                        <div
                            class="inline-flex items-center gap-2 mb-3 bg-white/10 px-3 py-1 rounded-full border border-white/10 backdrop-blur-md">
                            <span class="text-lg">👋</span>
                            <span class="font-bold text-indigo-100 uppercase tracking-widest text-[10px]">Selamat
                                Datang</span>
                        </div>
                        <h1 class="text-3xl md:text-5xl font-black mb-4 leading-tight tracking-tight">Halo, {{
                            Auth::user()->name }}!</h1>
                        <p class="text-indigo-100 font-medium max-w-xl text-sm md:text-lg leading-relaxed opacity-90">
                            Siap untuk menguji kemampuanmu? Pastikan koneksi internet stabil dan kerjakan dengan jujur
                            ya!
                        </p>
                    </div>

                    <div class="flex gap-4 shrink-0">
                        <div
                            class="bg-white/10 backdrop-blur-md rounded-2xl p-5 text-center border border-white/20 min-w-[100px]">
                            <span class="block text-3xl font-black mb-1">{{ $mySessions->where('is_open', true)->count()
                                }}</span>
                            <span class="text-[10px] font-bold text-indigo-200 uppercase tracking-wider">Tersedia</span>
                        </div>
                        <div
                            class="bg-white/10 backdrop-blur-md rounded-2xl p-5 text-center border border-white/20 min-w-[100px]">
                            <span class="block text-3xl font-black mb-1">{{ $mySessions->where('user_status',
                                'completed')->count() }}</span>
                            <span class="text-[10px] font-bold text-indigo-200 uppercase tracking-wider">Selesai</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4 mb-8 px-2">
                <div class="w-1.5 h-8 bg-indigo-600 rounded-full"></div>
                <div>
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">Daftar Ujian Kamu</h2>
                    <p class="text-xs font-bold text-slate-400 mt-0.5">Silakan pilih ujian yang tersedia</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse($mySessions as $session)
                <div
                    class="bg-white rounded-[2.5rem] p-2 shadow-[0_2px_15px_rgba(0,0,0,0.03)] border border-slate-100 hover:border-indigo-200 transition-all hover-lift group flex flex-col h-full relative overflow-hidden">

                    <div
                        class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r {{ $session->is_open ? 'from-indigo-500 to-violet-500' : 'from-slate-200 to-slate-300' }}">
                    </div>

                    <div class="p-6 flex flex-col h-full bg-white rounded-[2.2rem]">
                        <div class="flex justify-between items-start mb-6">
                            <span
                                class="bg-slate-50 text-slate-500 text-[10px] font-black px-3 py-1.5 rounded-lg border border-slate-100 uppercase tracking-wider">
                                {{ $session->session_name }}
                            </span>

                            @if($session->user_status == 'completed')
                            <span
                                class="bg-emerald-50 text-emerald-600 text-[10px] font-black px-3 py-1.5 rounded-full border border-emerald-100 flex items-center gap-1.5">
                                <div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div> SELESAI
                            </span>
                            @elseif($session->is_open && $session->user_status != 'completed')
                            <span
                                class="bg-indigo-50 text-indigo-600 text-[10px] font-black px-3 py-1.5 rounded-full border border-indigo-100 flex items-center gap-1.5">
                                <span class="relative flex h-2 w-2">
                                    <span
                                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                                </span>
                                DIBUKA
                            </span>
                            @else
                            <span
                                class="bg-slate-50 text-slate-400 text-[10px] font-black px-3 py-1.5 rounded-full border border-slate-200 flex items-center gap-1.5">
                                <i class="fas fa-lock text-[10px]"></i> DITUTUP
                            </span>
                            @endif
                        </div>

                        <div class="mb-8">
                            <h3
                                class="text-xl font-black text-slate-800 leading-snug mb-3 group-hover:text-indigo-600 transition-colors line-clamp-2">
                                {{ $session->exam->title }}
                            </h3>
                            <div class="flex items-center gap-4 text-xs font-bold text-slate-400">
                                <div class="flex items-center gap-1.5">
                                    <i class="fas fa-stopwatch text-indigo-400"></i> {{ $session->exam->duration_minutes
                                    }} Menit
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <i class="fas fa-file-alt text-indigo-400"></i> {{ $session->exam->questions_count
                                    ?? 0 }} Soal
                                </div>
                            </div>
                        </div>

                        <div class="bg-slate-50 rounded-2xl p-5 space-y-4 mb-8 border border-slate-100">
                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-8 h-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-emerald-500 shadow-sm shrink-0">
                                        <i class="fas fa-play text-[10px]"></i>
                                    </div>
                                    <div class="flex flex-col">
                                        <span
                                            class="text-[9px] font-black text-slate-400 uppercase tracking-wide">Mulai</span>
                                        <span class="text-xs font-bold text-slate-700">
                                            {{ \Carbon\Carbon::parse($session->start_time)->translatedFormat('d M, H:i')
                                            }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="border-t border-dashed border-slate-200"></div>

                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-8 h-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-rose-500 shadow-sm shrink-0">
                                        <i class="fas fa-stop text-[10px]"></i>
                                    </div>
                                    <div class="flex flex-col">
                                        <span
                                            class="text-[9px] font-black text-slate-400 uppercase tracking-wide">Selesai</span>
                                        <span class="text-xs font-bold text-slate-700">
                                            {{ \Carbon\Carbon::parse($session->end_time)->translatedFormat('d M, H:i')
                                            }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-auto">
                            @if($session->user_status == 'completed')
                            <div
                                class="w-full bg-emerald-50/50 text-emerald-600 py-4 rounded-2xl font-black flex flex-col items-center justify-center border border-emerald-100">
                                <span class="text-[10px] uppercase tracking-widest opacity-60">Nilai Kamu</span>
                                <span class="text-2xl">{{ $session->user_score }}</span>
                            </div>
                            @elseif($session->is_open)
                            {{-- TAUTAN INI YANG DIUBAH: Mengarah langsung ke halaman verify token --}}
                            <a href="{{ route('student.exam.verify.show', $session->exam_id) }}"
                                class="group/btn relative w-full flex justify-center py-4 px-4 border border-transparent text-sm font-black rounded-2xl text-white bg-slate-900 hover:bg-indigo-600 focus:outline-none focus:ring-4 focus:ring-indigo-200 shadow-xl shadow-slate-200 hover:shadow-indigo-200 transition-all active:scale-95">
                                <span class="flex items-center gap-2">
                                    Mulai Mengerjakan <i
                                        class="fas fa-arrow-right group-hover/btn:translate-x-1 transition-transform"></i>
                                </span>
                            </a>
                            @else
                            <div x-data="{
                                    startsAt: new Date('{{ \Carbon\Carbon::parse($session->start_time)->toIso8601String() }}'),
                                    now: new Date()
                                }" class="text-center w-full">

                                <template x-if="startsAt > now">
                                    <button disabled
                                        class="w-full bg-slate-100 text-slate-400 py-4 rounded-2xl font-black cursor-not-allowed border border-slate-200 flex items-center justify-center gap-2">
                                        <i class="fas fa-hourglass-half"></i> Menunggu Jadwal
                                    </button>
                                </template>

                                <template
                                    x-if="now > new Date('{{ \Carbon\Carbon::parse($session->end_time)->toIso8601String() }}')">
                                    <button disabled
                                        class="w-full bg-rose-50 text-rose-400 py-4 rounded-2xl font-black cursor-not-allowed border border-rose-100 flex items-center justify-center gap-2">
                                        <i class="fas fa-times-circle"></i> Ujian Berakhir
                                    </button>
                                </template>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-span-full py-20 text-center">
                    <div class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <span class="text-4xl">😴</span>
                    </div>
                    <h3 class="text-xl font-black text-slate-800">Tidak ada jadwal ujian</h3>
                    <p class="text-slate-400 font-bold mt-2">Belum ada ujian yang ditugaskan kepadamu saat ini.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('serverClock', () => ({
                timeString: '--:--:--',
                dateString: 'Memuat Tanggal...',
                serverTime: null,

                init() {
                    this.serverTime = new Date('{{ now()->toIso8601String() }}');
                    this.updateClock();
                    setInterval(() => {
                        this.serverTime.setSeconds(this.serverTime.getSeconds() + 1);
                        this.updateClock();
                    }, 1000);
                },

                updateClock() {
                    this.timeString = this.serverTime.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false }).replace(/\./g, ':');
                    this.dateString = this.serverTime.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'short', year: 'numeric' });
                }
            }));
        });

        document.addEventListener('DOMContentLoaded', () => {
            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Akses Ditolak!',
                    text: @json(session('error')),
                    confirmButtonText: 'Mengerti',
                    confirmButtonColor: '#ef4444',
                    background: '#fff',
                    allowOutsideClick: false
                });
            @endif

            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: @json(session('success')),
                    confirmButtonText: 'Tutup',
                    confirmButtonColor: '#10b981',
                    timer: 4000,
                    timerProgressBar: true
                });
            @endif

            @if(session('info'))
                Swal.fire({
                    icon: 'info',
                    title: 'Informasi',
                    text: @json(session('info')),
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#3b82f6'
                });
            @endif
        });
    </script>
</x-app-layout>