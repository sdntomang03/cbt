<x-app-layout>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8fafc;
        }

        [x-cloak] {
            display: none !important;
        }

        /* Custom Scrollbar untuk Tabel */
        .custom-scrollbar::-webkit-scrollbar {
            height: 8px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 8px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 8px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>

    {{-- Topbar & Server Clock --}}
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

    <div class="min-h-screen py-10" x-data="{
        filterTab: 'pending', // Default: Belum dilaksanakan
        searchQuery: '',

        // Cek apakah ada baris yang tampil (untuk empty state)
        hasVisibleRows() {
            let count = 0;
            const rows = document.querySelectorAll('.exam-row');
            rows.forEach(row => {
                if(row.style.display !== 'none') count++;
            });
            return count > 0;
        }
    }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Banner Welcome --}}
            <div
                class="bg-gradient-to-br from-indigo-600 to-violet-700 rounded-[2.5rem] p-8 md:p-10 mb-8 text-white shadow-2xl shadow-indigo-200 relative overflow-hidden">
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
                            ya![cite: 3]
                        </p>
                    </div>

                    <div class="flex gap-4 shrink-0">
                        <div
                            class="bg-white/10 backdrop-blur-md rounded-2xl p-5 text-center border border-white/20 min-w-[100px]">
                            <span class="block text-3xl font-black mb-1">{{ $mySessions->where('is_open', true)->count()
                                }}[cite: 3]</span>
                            <span class="text-[10px] font-bold text-indigo-200 uppercase tracking-wider">Tersedia[cite:
                                3]</span>
                        </div>
                        <div
                            class="bg-white/10 backdrop-blur-md rounded-2xl p-5 text-center border border-white/20 min-w-[100px]">
                            <span class="block text-3xl font-black mb-1">{{ $mySessions->where('user_status',
                                'completed')->count() }}[cite: 3]</span>
                            <span class="text-[10px] font-bold text-indigo-200 uppercase tracking-wider">Selesai[cite:
                                3]</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Datatable Toolbar (Tab Filter & Search) --}}
            <div
                class="bg-white p-4 rounded-t-[2rem] border border-slate-200 border-b-0 shadow-sm flex flex-col md:flex-row justify-between items-center gap-4 mt-8">

                {{-- Toggle Filter Tabs --}}
                <div class="flex bg-slate-100 p-1 rounded-xl w-full md:w-auto overflow-hidden">
                    <button @click="filterTab = 'pending'"
                        :class="filterTab === 'pending' ? 'bg-white shadow-sm text-indigo-600 font-bold' : 'text-slate-500 font-medium hover:text-slate-700'"
                        class="flex-1 md:flex-none px-6 py-2.5 rounded-lg text-sm transition-all duration-200">
                        <i class="fas fa-hourglass-half mr-1.5"></i> Belum Dilaksanakan
                    </button>
                    <button @click="filterTab = 'completed'"
                        :class="filterTab === 'completed' ? 'bg-white shadow-sm text-emerald-600 font-bold' : 'text-slate-500 font-medium hover:text-slate-700'"
                        class="flex-1 md:flex-none px-6 py-2.5 rounded-lg text-sm transition-all duration-200">
                        <i class="fas fa-check-circle mr-1.5"></i> Sudah Terlaksana
                    </button>
                </div>

                {{-- Search Box --}}
                <div class="relative w-full md:w-72">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-slate-400"></i>
                    </div>
                    <input type="text" x-model="searchQuery" placeholder="Cari nama ujian atau sesi..."
                        class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all font-medium text-slate-700">
                </div>
            </div>

            {{-- Datatable Content --}}
            <div class="bg-white rounded-b-[2rem] border border-slate-200 shadow-sm overflow-hidden relative">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse whitespace-nowrap min-w-[800px]">
                        <thead
                            class="bg-slate-50 border-y border-slate-200 text-slate-500 text-xs uppercase font-black tracking-wider">
                            <tr>
                                <th class="px-6 py-4 rounded-tl-[2rem]">Ujian & Sesi</th>
                                <th class="px-6 py-4">Jadwal Pelaksanaan</th>
                                <th class="px-6 py-4">Detail</th>
                                <th class="px-6 py-4 text-center">Status</th>
                                <th class="px-6 py-4 text-center">Nilai</th>
                                <th class="px-6 py-4 text-right rounded-tr-[2rem]">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm font-medium text-slate-700 relative">
                            @forelse($mySessions as $session)
                            <tr class="hover:bg-slate-50/50 transition-colors exam-row" x-show="
                                        (filterTab === 'completed' ? '{{ $session->user_status }}' === 'completed' : '{{ $session->user_status }}' !== 'completed') &&
                                        ('{{ strtolower($session->session_name . ' ' . ($session->exam->title ?? '')) }}'.includes(searchQuery.toLowerCase()))
                                    ">
                                {{-- Kolom 1: Informasi Ujian --}}
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1.5">
                                        <span
                                            class="inline-flex max-w-max bg-slate-100 text-slate-600 text-[10px] font-black px-2 py-1 rounded uppercase tracking-wider">
                                            {{ $session->session_name }}[cite: 3]
                                        </span>
                                        <span
                                            class="font-black text-slate-800 text-base max-w-xs truncate whitespace-normal leading-tight">
                                            {{ $session->exam?->title ?? 'Ujian Tidak Tersedia (Dihapus)' }}[cite: 3]
                                        </span>
                                    </div>
                                </td>

                                {{-- Kolom 2: Jadwal --}}
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center gap-2 text-emerald-600">
                                            <i class="fas fa-play-circle text-xs"></i>
                                            <span class="font-bold text-xs">{{
                                                \Carbon\Carbon::parse($session->start_time)->translatedFormat('d M Y,
                                                H:i') }}[cite: 3]</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-rose-500">
                                            <i class="fas fa-stop-circle text-xs"></i>
                                            <span class="font-bold text-xs">{{
                                                \Carbon\Carbon::parse($session->end_time)->translatedFormat('d M Y,
                                                H:i') }}[cite: 3]</span>
                                        </div>
                                    </div>
                                </td>

                                {{-- Kolom 3: Durasi & Soal --}}
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1 text-slate-500">
                                        <span class="flex items-center gap-2">
                                            <i class="fas fa-stopwatch w-4 text-center"></i> {{
                                            $session->exam?->duration_minutes ?? 0 }} Menit[cite: 3]
                                        </span>
                                        <span class="flex items-center gap-2">
                                            <i class="fas fa-file-alt w-4 text-center"></i> {{
                                            $session->exam->questions_count ?? 0 }} Soal[cite: 3]
                                        </span>
                                    </div>
                                </td>

                                {{-- Kolom 4: Status --}}
                                <td class="px-6 py-4 text-center">
                                    @if($session->user_status == 'completed')
                                    <span
                                        class="inline-flex bg-emerald-50 text-emerald-600 text-[10px] font-black px-3 py-1.5 rounded-full border border-emerald-100 items-center gap-1.5 uppercase">
                                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div> Selesai[cite: 3]
                                    </span>
                                    @elseif(isset($session->pivot) && $session->pivot->is_locked)
                                    <span
                                        class="inline-flex bg-rose-50 text-rose-600 text-[10px] font-black px-3 py-1.5 rounded-full border border-rose-100 items-center gap-1.5 uppercase">
                                        <i class="fas fa-lock"></i> Dikunci[cite: 3]
                                    </span>
                                    @elseif($session->is_open)
                                    <span
                                        class="inline-flex bg-indigo-50 text-indigo-600 text-[10px] font-black px-3 py-1.5 rounded-full border border-indigo-100 items-center gap-1.5 uppercase">
                                        <span class="relative flex h-2 w-2">
                                            <span
                                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                                            <span
                                                class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                                        </span>
                                        Tersedia[cite: 3]
                                    </span>
                                    @else
                                    <span
                                        class="inline-flex bg-slate-50 text-slate-400 text-[10px] font-black px-3 py-1.5 rounded-full border border-slate-200 items-center gap-1.5 uppercase">
                                        <i class="fas fa-clock"></i> Menunggu/Berakhir
                                    </span>
                                    @endif
                                </td>

                                {{-- Kolom 5: Nilai --}}
                                <td class="px-6 py-4 text-center">
                                    @if($session->user_status == 'completed')
                                    <div
                                        class="inline-flex items-center justify-center min-w-[3rem] px-3 py-1.5 bg-slate-900 text-white rounded-lg font-black text-sm">
                                        {{ $session->user_score }}[cite: 3]
                                    </div>
                                    @else
                                    <span class="text-slate-300 font-bold">-</span>
                                    @endif
                                </td>

                                {{-- Kolom 6: Aksi --}}
                                <td class="px-6 py-4 text-right">
                                    @if($session->user_status == 'completed')
                                    @if(isset($exam) ? $exam->show_explanation : $session->exam->show_explanation)
                                    <a href="{{ route('student.exams.explanation', Hashids::encode($session->id)) }}"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white rounded-xl font-bold text-xs transition-all border border-indigo-100 shadow-sm">
                                        <i class="fas fa-lightbulb"></i> Pembahasan[cite: 3]
                                    </a>
                                    @else
                                    <span class="text-xs text-slate-400 font-bold italic">Selesai Dikerjakan</span>
                                    @endif

                                    @elseif(isset($session->pivot) && $session->pivot->is_locked)
                                    <button disabled
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-rose-50 text-rose-500 rounded-xl font-bold text-xs cursor-not-allowed border border-rose-200">
                                        <i class="fas fa-ban"></i> Terblokir[cite: 3]
                                    </button>

                                    @elseif($session->is_open)
                                    <a href="{{ route('student.exam.verify.show', $session->exam) }}"
                                        class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-black text-xs transition-all shadow-md hover:shadow-lg active:scale-95">
                                        <i class="fas fa-play"></i> Kerjakan[cite: 3]
                                    </a>

                                    @else
                                    <button disabled
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 text-slate-400 rounded-xl font-bold text-xs cursor-not-allowed">
                                        <i class="fas fa-lock"></i> Ditutup[cite: 3]
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-slate-400">
                                        <div
                                            class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                                            <i class="fas fa-inbox text-2xl"></i>[cite: 3]
                                        </div>
                                        <h3 class="text-lg font-black text-slate-600">Tidak ada jadwal ujian</h3>
                                        <p class="text-sm font-bold mt-1">Belum ada ujian yang ditugaskan kepadamu saat
                                            ini.</p>[cite: 3]
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Empty State Alert (Pencarian Tidak Ditemukan / Filter Kosong) --}}
                @if(count($mySessions) > 0)
                <div x-cloak x-show="!hasVisibleRows()" class="p-12 text-center bg-slate-50/50">
                    <div
                        class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 text-slate-400 mb-4">
                        <i class="fas fa-search text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-black text-slate-700">Tidak ada data ditemukan</h3>
                    <p class="text-slate-500 font-medium text-sm mt-1">Coba ubah kata kunci pencarian atau ganti tab
                        filter di atas.</p>
                </div>
                @endif
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
                    this.serverTime = new Date('{{ now()->toIso8601String() }}');[cite: 3]
                    this.updateClock();[cite: 3]
                    setInterval(() => {
                        this.serverTime.setSeconds(this.serverTime.getSeconds() + 1);[cite: 3]
                        this.updateClock();[cite: 3]
                    }, 1000);[cite: 3]
                },

                updateClock() {
                    this.timeString = this.serverTime.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false }).replace(/\./g, ':');[cite: 3]
                    this.dateString = this.serverTime.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'short', year: 'numeric' });[cite: 3]
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
                });[cite: 3]
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
                });[cite: 3]
            @endif

            @if(session('info'))
                Swal.fire({
                    icon: 'info',
                    title: 'Informasi',
                    text: @json(session('info')),
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#3b82f6'
                });[cite: 3]
            @endif
        });
    </script>
</x-app-layout>