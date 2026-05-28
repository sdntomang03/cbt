<x-app-layout>
    <x-slot name="header">
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap"
            rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
        <style>
            body {
                font-family: 'Nunito', sans-serif;
                background-color: #f0f4f8;
            }

            .custom-scrollbar::-webkit-scrollbar {
                height: 8px;
                width: 8px;
            }

            .custom-scrollbar::-webkit-scrollbar-thumb {
                background: #cbd5e1;
                border-radius: 10px;
            }
        </style>

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center w-full gap-4 py-2 px-2">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.analysis.index', $exam->id) }}"
                    class="w-10 h-10 rounded-xl bg-white text-slate-500 hover:text-indigo-600 shadow-sm flex items-center justify-center transition border border-slate-200 shrink-0">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h2 class="font-black text-2xl text-slate-800 tracking-tight leading-tight">Analisis Butir Soal</h2>
                    <p class="text-indigo-600 font-bold text-sm line-clamp-1">{{ $exam->title }} — <span
                            class="text-slate-500">{{ $session->session_name }}</span></p>
                </div>
            </div>

            {{-- Tombol Aksi Cetak & Excel --}}
            <div class="flex gap-3 w-full md:w-auto">
                <a href="{{ route('admin.analysis.print', [$exam->id, $session->id]) }}" target="_blank"
                    class="flex-1 md:flex-none bg-white text-slate-700 hover:text-indigo-600 px-5 py-2.5 rounded-xl font-bold shadow-sm border border-slate-200 transition flex items-center justify-center gap-2">
                    <i class="fas fa-print text-indigo-500"></i> Cetak Laporan
                </a>
                <a href="{{ route('admin.analysis.export', [$exam->id, $session->id]) }}"
                    class="flex-1 md:flex-none bg-emerald-500 hover:bg-emerald-600 text-white px-5 py-2.5 rounded-xl font-black shadow-lg shadow-emerald-200 transition active:scale-95 flex items-center justify-center gap-2">
                    <i class="fas fa-file-excel"></i> Unduh Excel
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8 min-h-screen">
        <div class="w-full 2xl:max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- KARTU RINGKASAN STATISTIK --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

                {{-- Total Peserta --}}
                <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 flex items-center gap-5">
                    <div
                        class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-500 flex items-center justify-center text-2xl shrink-0">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Peserta Ujian
                        </p>
                        <h3 class="text-2xl font-black text-slate-800">{{ $total_students }} <span
                                class="text-sm font-bold text-slate-500">Siswa</span></h3>
                    </div>
                </div>

                {{-- Reliabilitas --}}
                <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 flex items-center gap-5">
                    <div
                        class="w-14 h-14 rounded-2xl bg-purple-50 text-purple-500 flex items-center justify-center text-2xl shrink-0">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Reliabilitas
                            (Alpha)</p>
                        <h3 class="text-2xl font-black text-slate-800">{{ $summary['alpha'] }}</h3>
                        <p
                            class="text-xs font-bold {{ $summary['alpha'] >= 0.7 ? 'text-emerald-500' : 'text-rose-500' }}">
                            {{ $summary['alpha_label'] }}</p>
                    </div>
                </div>

                {{-- Status Validitas --}}
                <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 flex items-center gap-5">
                    <div
                        class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-2xl shrink-0">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Status Validitas
                        </p>
                        <h3 class="text-2xl font-black text-emerald-600">{{ $summary['valid_count'] }} <span
                                class="text-sm font-bold text-slate-500">Valid</span></h3>
                        @if($summary['invalid_count'] > 0)
                        <p class="text-xs font-bold text-rose-500">{{ $summary['invalid_count'] }} Butir Perlu Revisi
                        </p>
                        @else
                        <p class="text-xs font-bold text-slate-400">Semua Soal Valid</p>
                        @endif
                    </div>
                </div>

                {{-- Proporsi Kesukaran --}}
                <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 flex items-center gap-5">
                    <div
                        class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-2xl shrink-0">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                    <div class="w-full">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Tingkat
                            Kesukaran</p>
                        <div class="flex justify-between text-xs font-bold">
                            <span class="text-emerald-500" title="Mudah">M: {{ $summary['mudah'] }}</span>
                            <span class="text-amber-500" title="Sedang">S: {{ $summary['sedang'] }}</span>
                            <span class="text-rose-500" title="Sulit">Sl: {{ $summary['sulit'] }}</span>
                        </div>
                        {{-- Mini Progress Bar Visualisasi --}}
                        <div class="w-full h-1.5 bg-slate-100 rounded-full mt-2 flex overflow-hidden">
                            @if($summary['total_items'] > 0)
                            <div style="width: {{ ($summary['mudah'] / $summary['total_items']) * 100 }}%"
                                class="bg-emerald-400 h-full"></div>
                            <div style="width: {{ ($summary['sedang'] / $summary['total_items']) * 100 }}%"
                                class="bg-amber-400 h-full"></div>
                            <div style="width: {{ ($summary['sulit'] / $summary['total_items']) * 100 }}%"
                                class="bg-rose-400 h-full"></div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>

            {{-- TABEL DETAIL PER BUTIR SOAL --}}
            <div class="bg-white shadow-sm sm:rounded-[2.5rem] border border-slate-100 overflow-hidden">
                <div class="p-6 border-b border-slate-50 bg-slate-50/50 flex justify-between items-center">
                    <h3 class="font-black text-slate-700 text-sm uppercase tracking-widest">Detail Analisis Per Butir
                        Soal</h3>
                    <div class="text-xs font-bold text-slate-400">Total: <span class="text-indigo-600">{{
                            $summary['total_items'] }} Soal</span></div>
                </div>

                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead>
                            <tr
                                class="bg-slate-50/50 border-b border-slate-100 text-[10px] uppercase tracking-widest text-slate-400 font-black">
                                <th class="px-6 py-5 text-center w-16">No</th>
                                <th class="px-6 py-5">Potongan Soal & Tipe</th>
                                <th class="px-6 py-5 text-center">Tingkat Kesukaran (TK)</th>
                                <th class="px-6 py-5 text-center">Daya Beda (DB)</th>
                                <th class="px-6 py-5 text-center">Validitas (r-xy)</th>
                                <th class="px-6 py-5 text-right pr-8">Kesimpulan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 text-sm">
                            @foreach($items as $index => $item)
                            <tr
                                class="hover:bg-slate-50/50 transition-colors {{ !$item['valid'] ? 'bg-rose-50/20' : '' }}">

                                {{-- Nomor --}}
                                <td class="px-6 py-4 text-center font-black text-slate-400">{{ $index + 1 }}</td>

                                {{-- Konten Soal --}}
                                <td class="px-6 py-4 whitespace-normal min-w-[300px] max-w-md">
                                    <div class="font-bold text-slate-700 line-clamp-2 leading-relaxed mb-1"
                                        title="{{ $item['content'] }}">
                                        {{ Str::limit($item['content'], 100) }}
                                    </div>
                                    <span
                                        class="inline-block bg-slate-100 text-slate-500 px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider">
                                        {{ str_replace('_', ' ', $item['type']) }}
                                    </span>
                                </td>

                                {{-- Tingkat Kesukaran --}}
                                <td class="px-6 py-4 text-center">
                                    <div class="font-mono font-black text-base text-slate-800">{{ $item['tk'] }}</div>
                                    <div
                                        class="text-[10px] font-black uppercase tracking-wider mt-0.5
                                        {{ $item['tk_label'] == 'Mudah' ? 'text-emerald-500' : ($item['tk_label'] == 'Sedang' ? 'text-amber-500' : 'text-rose-500') }}">
                                        {{ $item['tk_label'] }}
                                    </div>
                                </td>

                                {{-- Daya Beda --}}
                                <td class="px-6 py-4 text-center">
                                    <div
                                        class="font-mono font-black text-base {{ $item['db'] <= 0 ? 'text-rose-600' : 'text-slate-800' }}">
                                        {{ $item['db'] }}</div>
                                    <div
                                        class="text-[10px] font-black uppercase tracking-wider mt-0.5
                                        {{ in_array($item['db_label'], ['Sangat Baik', 'Baik']) ? 'text-emerald-500' : (in_array($item['db_label'], ['Jelek', 'Sangat Jelek (Revisi/Buang)']) ? 'text-rose-500' : 'text-amber-500') }}">
                                        {{ $item['db_label'] }}
                                    </div>
                                </td>

                                {{-- Validitas Korelasi --}}
                                <td class="px-6 py-4 text-center">
                                    <div
                                        class="font-mono font-black text-base {{ $item['valid'] ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $item['validity'] }}</div>
                                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-wider mt-0.5">
                                        r-Hitung</div>
                                </td>

                                {{-- Kesimpulan / Status --}}
                                <td class="px-6 py-4 text-right pr-8">
                                    @if($item['valid'])
                                    <div
                                        class="inline-flex items-center gap-2 bg-emerald-50 text-emerald-600 px-3 py-1.5 rounded-lg border border-emerald-100">
                                        <i class="fas fa-check-circle"></i>
                                        <span class="text-xs font-black uppercase tracking-wider">Valid</span>
                                    </div>
                                    @else
                                    <div
                                        class="inline-flex items-center gap-2 bg-rose-50 text-rose-600 px-3 py-1.5 rounded-lg border border-rose-100">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span class="text-xs font-black uppercase tracking-wider">Revisi</span>
                                    </div>
                                    @endif
                                </td>

                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>