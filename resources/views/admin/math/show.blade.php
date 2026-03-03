<x-app-layout>
    <div class="py-10 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">

        <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.math.index') }}"
                    class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 transition-colors shadow-sm border border-slate-100">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h2 class="text-3xl font-black text-slate-800 tracking-tight">{{ $exam->title }}</h2>
                    <p class="text-slate-500 font-bold mt-1">Rekapitulasi Nilai Ujian Matematika Siswa</p>
                </div>
            </div>

            <button onclick="window.print()"
                class="bg-slate-800 hover:bg-black text-white px-6 py-3 rounded-xl font-bold shadow-lg transition-transform hover:-translate-y-1 flex items-center gap-2">
                <i class="fas fa-print"></i> Cetak PDF
            </button>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 flex items-center gap-4">
                <div class="w-14 h-14 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center text-2xl">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <span class="block text-xs font-black text-slate-400 uppercase tracking-widest">Selesai /
                        Total</span>
                    <span class="text-2xl font-black text-slate-800">{{ $stats['completed_count'] }} <span
                            class="text-sm text-slate-400">/ {{ $stats['total_students'] }}</span></span>
                </div>
            </div>
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 flex items-center gap-4">
                <div
                    class="w-14 h-14 rounded-full bg-indigo-50 text-indigo-500 flex items-center justify-center text-2xl">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div>
                    <span class="block text-xs font-black text-slate-400 uppercase tracking-widest">Rata-rata</span>
                    <span class="text-2xl font-black text-slate-800">{{ $stats['average_score'] }}</span>
                </div>
            </div>
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 flex items-center gap-4">
                <div
                    class="w-14 h-14 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center text-2xl">
                    <i class="fas fa-arrow-up"></i>
                </div>
                <div>
                    <span class="block text-xs font-black text-slate-400 uppercase tracking-widest">Tertinggi</span>
                    <span class="text-2xl font-black text-emerald-500">{{ $stats['highest_score'] }}</span>
                </div>
            </div>
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 flex items-center gap-4">
                <div class="w-14 h-14 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center text-2xl">
                    <i class="fas fa-arrow-down"></i>
                </div>
                <div>
                    <span class="block text-xs font-black text-slate-400 uppercase tracking-widest">Terendah</span>
                    <span class="text-2xl font-black text-rose-500">{{ $stats['lowest_score'] }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                <h3 class="font-black text-lg text-slate-800">Daftar Peserta & Nilai</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider font-black border-b border-slate-100">
                            <th class="p-5 text-center w-16">No</th>
                            <th class="p-5">Nama Siswa</th>
                            <th class="p-5">Sekolah</th>
                            <th class="p-5 text-center">Status</th>
                            <th class="p-5 text-center">Waktu Pengerjaan</th>
                            <th class="p-5 text-center">Nilai Akhir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($exam->examUsers->sortByDesc('score') as $index => $user)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="p-5 text-center font-bold text-slate-400">{{ $loop->iteration }}</td>

                            <td class="p-5">
                                <span class="block font-black text-slate-800">{{ $user->student->name ?? 'Siswa
                                    Terhapus' }}</span>
                            </td>

                            <td class="p-5">
                                <span class="text-xs font-bold text-indigo-500 uppercase">{{
                                    $user->student->school->name ?? 'Pusat' }}</span>
                            </td>

                            <td class="p-5 text-center">
                                @if($user->status === 'not_started')
                                <span
                                    class="bg-slate-100 text-slate-500 px-3 py-1 rounded-full text-xs font-black uppercase">Belum
                                    Mulai</span>
                                @elseif($user->status === 'ongoing')
                                <span
                                    class="bg-amber-100 text-amber-600 px-3 py-1 rounded-full text-xs font-black uppercase animate-pulse">Sedang
                                    Ujian</span>
                                @else
                                <span
                                    class="bg-emerald-100 text-emerald-600 px-3 py-1 rounded-full text-xs font-black uppercase">Selesai</span>
                                @endif
                            </td>

                            <td class="p-5 text-center">
                                @if($user->status === 'completed' && $user->started_at && $user->finished_at)
                                @php
                                $start = \Carbon\Carbon::parse($user->started_at);
                                $finish = \Carbon\Carbon::parse($user->finished_at);

                                // 1. Ambil total detiknya saja (misal: 42 detik)
                                $totalSeconds = $start->diffInSeconds($finish);

                                // 2. Bagi 60 dan paksa bulatkan ke bawah (42 / 60 = 0.7 -> dibulatkan jadi 0)
                                $diffInMinutes = floor($totalSeconds / 60);

                                // 3. Ambil sisa detiknya
                                $diffInSeconds = $totalSeconds % 60;
                                @endphp

                                <span class="text-sm font-bold text-slate-600 bg-slate-100 px-3 py-1 rounded-lg">
                                    <i class="fas fa-stopwatch mr-1 text-slate-400"></i>
                                    {{ $diffInMinutes }}m {{ $diffInSeconds }}s
                                </span>
                                @else
                                <span class="text-slate-300 font-bold">-</span>
                                @endif
                            </td>

                            <td class="p-5 text-center">
                                @if($user->status === 'completed')
                                <span
                                    class="text-2xl font-black {{ $user->score >= 70 ? 'text-emerald-500' : 'text-rose-500' }}">{{
                                    $user->score }}</span>
                                @else
                                <span class="text-slate-300 font-bold text-xl">-</span>
                                @endif
                                <a href="{{ route('admin.math.student_result', $user->id) }}"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 text-slate-500 hover:bg-indigo-500 hover:text-white transition-colors"
                                    title="Lihat Lembar Jawaban">
                                    <i class="fas fa-search"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="p-10 text-center text-slate-400 font-bold">
                                <i class="fas fa-users-slash text-4xl mb-3 block opacity-30"></i>
                                Belum ada siswa yang ditugaskan untuk ujian ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <style>
            @media print {
                body * {
                    visibility: hidden;
                }

                .max-w-7xl,
                .max-w-7xl * {
                    visibility: visible;
                }

                .max-w-7xl {
                    position: absolute;
                    left: 0;
                    top: 0;
                    width: 100%;
                    padding: 0;
                }

                button,
                a {
                    display: none !important;
                }

                .shadow-sm,
                .shadow-lg {
                    box-shadow: none !important;
                }

                .bg-white {
                    background-color: transparent !important;
                }

                .rounded-\[2rem\] {
                    border-radius: 0 !important;
                    border: none !important;
                }

                table {
                    border: 1px solid #e2e8f0;
                }

                th,
                td {
                    border: 1px solid #e2e8f0;
                }
            }
        </style>
    </div>
</x-app-layout>