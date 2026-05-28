<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center w-full gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ url()->previous() }}"
                    class="w-10 h-10 rounded-xl bg-white text-slate-500 hover:text-indigo-600 shadow-sm border border-slate-200 flex items-center justify-center transition shrink-0">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h2 class="font-black text-2xl text-slate-800 tracking-tight leading-tight">Analisis Butir Soal</h2>
                    <p class="text-indigo-600 font-bold text-sm mt-0.5">
                        {{ $exam->title }} <span class="text-slate-400 mx-1">•</span>
                        Sesi: {{ $session->session_name ?? \Carbon\Carbon::parse($session->start_time)->format('d M Y')
                        }}
                        <span class="text-slate-400 mx-1">•</span> {{ $total_students }} Peserta
                    </p>
                </div>
            </div>

            <a href="{{ route('teacher.analysis.export', [$exam, $session]) }}"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-indigo-200 transition active:scale-95 flex items-center justify-center gap-2">
                <i class="fas fa-file-export"></i> Export JSON
            </a>
        </div>
    </x-slot>

    {{-- State Alpine untuk Tab & Filter --}}
    <div x-data="{
            activeTab: 'tbl',
            filterTk: '',
            filterDb: '',
            filterValid: '',
            search: ''
        }" class="w-full">

        {{-- KOTAK RELIABILITAS CRONBACH ALPHA --}}
        <div
            class="bg-slate-900 text-white rounded-3xl p-6 md:p-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 mb-8 shadow-xl">
            <div>
                <h3 class="font-bold text-slate-400 text-sm tracking-widest uppercase mb-1">Reliabilitas Tes (Cronbach
                    Alpha)</h3>
                <div
                    class="font-mono font-black text-5xl bg-gradient-to-br from-indigo-400 to-emerald-400 text-transparent bg-clip-text">
                    {{ number_format($alpha, 3) }}
                </div>
            </div>
            <div class="md:text-right">
                <div
                    class="inline-block px-4 py-1.5 rounded-full text-sm font-black tracking-wider bg-white/10 border border-white/20 mb-2">
                    {{ $summary['alpha_label'] }}
                </div>
                <div class="text-xs text-slate-400 font-semibold max-w-sm">
                    α ≥ 0.90 (Sangat Tinggi) • ≥ 0.70 (Tinggi) • ≥ 0.50 (Cukup) • &lt; 0.50 (Rendah)
                </div>
            </div>
        </div>

        {{-- GRID KARTU STATISTIK --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-1.5 bg-indigo-500"></div>
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Soal</div>
                <div class="text-3xl font-black text-slate-800">{{ $summary['total_items'] }}</div>
                <div class="text-xs font-bold text-slate-400 mt-1">{{ $total_students }} Peserta Dinilai</div>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-1.5 bg-emerald-500"></div>
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Status Validitas</div>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-black text-emerald-600">{{ $summary['valid_count'] }}</span>
                    <span class="text-sm font-bold text-emerald-600">Valid</span>
                </div>
                <div class="text-xs font-bold text-rose-500 mt-1">{{ $summary['invalid_count'] }} Tidak Valid (r <
                        0.3)</div>
                </div>

                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1.5 bg-sky-500"></div>
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Proporsi Kesukaran
                    </div>
                    <div class="flex justify-between items-end mt-1">
                        <div class="text-center">
                            <div class="text-xl font-black text-emerald-500">{{ $summary['mudah'] }}</div>
                            <div class="text-[10px] font-bold text-slate-400">MUDAH</div>
                        </div>
                        <div class="text-center">
                            <div class="text-xl font-black text-amber-500">{{ $summary['sedang'] }}</div>
                            <div class="text-[10px] font-bold text-slate-400">SEDANG</div>
                        </div>
                        <div class="text-center">
                            <div class="text-xl font-black text-rose-500">{{ $summary['sulit'] }}</div>
                            <div class="text-[10px] font-bold text-slate-400">SULIT</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1.5 bg-amber-500"></div>
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Daya Beda (DB)
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-black text-amber-500">{{ $summary['db_sangat_baik'] +
                            $summary['db_baik'] }}</span>
                        <span class="text-sm font-bold text-amber-500">Kategori Baik</span>
                    </div>
                    <div class="text-xs font-bold text-rose-500 mt-1">{{ $summary['db_jelek'] }} Jelek (D < 0.20)</div>
                    </div>
                </div>

                {{-- NAVIGASI TABS --}}
                <div class="flex gap-6 border-b-2 border-slate-200 mb-6 overflow-x-auto custom-scrollbar">
                    <button @click="activeTab = 'tbl'"
                        class="pb-3 text-sm font-black whitespace-nowrap transition-colors border-b-2"
                        :class="activeTab === 'tbl' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-400 hover:text-slate-700'">
                        <i class="fas fa-table mr-2"></i> Tabel Analisis
                    </button>
                    <button @click="activeTab = 'dist'"
                        class="pb-3 text-sm font-black whitespace-nowrap transition-colors border-b-2"
                        :class="activeTab === 'dist' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-400 hover:text-slate-700'">
                        <i class="fas fa-chart-pie mr-2"></i> Efektivitas Distraktor
                    </button>
                    <button @click="activeTab = 'ref'"
                        class="pb-3 text-sm font-black whitespace-nowrap transition-colors border-b-2"
                        :class="activeTab === 'ref' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-400 hover:text-slate-700'">
                        <i class="fas fa-info-circle mr-2"></i> Keterangan Kriteria
                    </button>
                </div>

                {{-- TAB 1: TABEL UTAMA --}}
                <div x-show="activeTab === 'tbl'" x-cloak>

                    {{-- Filter Bar --}}
                    <div class="flex flex-wrap items-center gap-3 mb-4">
                        <span class="text-xs font-black text-slate-400 uppercase">Filter:</span>
                        <select x-model="filterTk"
                            class="text-sm font-bold text-slate-600 bg-white border-slate-200 rounded-xl py-2 pl-3 pr-8 focus:ring-indigo-500">
                            <option value="">Semua Kesukaran</option>
                            <option value="Mudah">Mudah</option>
                            <option value="Sedang">Sedang</option>
                            <option value="Sulit">Sulit</option>
                        </select>
                        <select x-model="filterDb"
                            class="text-sm font-bold text-slate-600 bg-white border-slate-200 rounded-xl py-2 pl-3 pr-8 focus:ring-indigo-500">
                            <option value="">Semua Daya Beda</option>
                            <option value="Sangat Baik">Sangat Baik</option>
                            <option value="Baik">Baik</option>
                            <option value="Cukup">Cukup</option>
                            <option value="Jelek">Jelek</option>
                        </select>
                        <select x-model="filterValid"
                            class="text-sm font-bold text-slate-600 bg-white border-slate-200 rounded-xl py-2 pl-3 pr-8 focus:ring-indigo-500">
                            <option value="">Semua Validitas</option>
                            <option value="1">Valid</option>
                            <option value="0">Tidak Valid</option>
                        </select>
                        <input type="text" x-model="search" placeholder="Cari isi soal..."
                            class="text-sm font-bold text-slate-600 bg-white border-slate-200 rounded-xl py-2 px-4 focus:ring-indigo-500 w-full md:w-auto flex-1 md:flex-none">
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="overflow-x-auto custom-scrollbar">
                            <table class="w-full text-left border-collapse whitespace-nowrap">
                                <thead>
                                    <tr
                                        class="bg-slate-50 border-b border-slate-200 text-[10px] uppercase tracking-widest text-slate-500 font-black">
                                        <th class="px-5 py-4 w-12 text-center">No</th>
                                        <th class="px-5 py-4">Soal & Tipe</th>
                                        <th class="px-5 py-4 text-center">TK</th>
                                        <th class="px-5 py-4 text-center">Daya Beda</th>
                                        <th class="px-5 py-4 text-center">r-Hitung</th>
                                        <th class="px-5 py-4 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-sm">
                                    @foreach ($items as $i => $item)
                                    <tr x-show="(filterTk === '' || filterTk === '{{ $item['tk_label'] }}') &&
                                        (filterDb === '' || filterDb === '{{ $item['db_label'] }}') &&
                                        (filterValid === '' || filterValid === '{{ $item['valid'] ? '1' : '0' }}') &&
                                        ('{{ strtolower($item['content']) }}'.includes(search.toLowerCase()))"
                                        class="hover:bg-slate-50/70 transition-colors {{ !$item['valid'] ? 'bg-rose-50/30' : '' }}">

                                        <td class="px-5 py-4 text-center font-mono font-bold text-slate-400">{{ $i + 1
                                            }}</td>

                                        <td class="px-5 py-4 whitespace-normal min-w-[250px] max-w-xs">
                                            <div class="font-bold text-slate-700 line-clamp-2 leading-snug mb-1"
                                                title="{{ $item['content'] }}">{{ $item['content'] }}</div>
                                            <span
                                                class="inline-block px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-slate-100 text-slate-500">
                                                {{ str_replace('_', ' ', $item['type']) }}
                                            </span>
                                        </td>

                                        <td class="px-5 py-4 text-center">
                                            <div class="font-mono font-black text-slate-800">{{
                                                number_format($item['tk'], 3) }}</div>
                                            <div
                                                class="text-[10px] font-black uppercase tracking-wider mt-0.5
                                        {{ $item['tk_label'] == 'Mudah' ? 'text-sky-500' : ($item['tk_label'] == 'Sedang' ? 'text-emerald-500' : 'text-rose-500') }}">
                                                {{ $item['tk_label'] }}
                                            </div>
                                        </td>

                                        <td class="px-5 py-4 text-center">
                                            <div
                                                class="font-mono font-black {{ $item['db'] <= 0 ? 'text-rose-600' : 'text-slate-800' }}">
                                                {{ number_format($item['db'], 3) }}</div>
                                            <div
                                                class="text-[10px] font-black uppercase tracking-wider mt-0.5
                                        {{ in_array($item['db_label'], ['Sangat Baik', 'Baik']) ? 'text-emerald-500' : (in_array($item['db_label'], ['Jelek', 'Sangat Jelek (Revisi/Buang)']) ? 'text-rose-500' : 'text-amber-500') }}">
                                                {{ $item['db_label'] }}
                                            </div>
                                        </td>

                                        <td class="px-5 py-4 text-center">
                                            <div
                                                class="font-mono font-black {{ $item['valid'] ? 'text-emerald-600' : 'text-rose-600' }}">
                                                {{ number_format($item['validity'], 3) }}</div>
                                        </td>

                                        <td class="px-5 py-4 text-center">
                                            @if($item['valid'])
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-black uppercase tracking-wide bg-emerald-100 text-emerald-700">
                                                <i class="fas fa-check-circle"></i> Valid
                                            </span>
                                            @else
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-black uppercase tracking-wide bg-rose-100 text-rose-700">
                                                <i class="fas fa-times-circle"></i> Revisi
                                            </span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- TAB 2: DISTRAKTOR --}}
                <div x-show="activeTab === 'dist'" x-cloak>
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="overflow-x-auto custom-scrollbar">
                            <table class="w-full text-left border-collapse whitespace-nowrap">
                                <thead>
                                    <tr
                                        class="bg-slate-50 border-b border-slate-200 text-[10px] uppercase tracking-widest text-slate-500 font-black">
                                        <th class="px-5 py-4 w-12 text-center">No</th>
                                        <th class="px-5 py-4 min-w-[200px]">Soal</th>
                                        <th class="px-5 py-4">Distribusi Pilihan Jawaban</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-sm">
                                    @foreach ($items as $i => $item)
                                    @if(count($item['distractors']) > 0)
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-5 py-4 text-center font-mono font-bold text-slate-400 align-top">
                                            {{ $i + 1 }}</td>
                                        <td
                                            class="px-5 py-4 whitespace-normal font-bold text-slate-700 align-top max-w-sm">
                                            {{ $item['content'] }}</td>
                                        <td class="px-5 py-4">
                                            <div class="flex flex-col gap-2">
                                                @foreach($item['distractors'] as $d)
                                                <div class="flex items-center gap-3">
                                                    <div class="w-5 flex justify-center shrink-0">
                                                        @if($d['is_correct'])
                                                        <i class="fas fa-check text-emerald-500 text-lg"></i>
                                                        @else
                                                        <i class="fas fa-times text-rose-400 text-lg"></i>
                                                        @endif
                                                    </div>
                                                    <div class="flex-1 min-w-[150px] max-w-[250px] truncate text-slate-600 font-semibold text-xs"
                                                        title="{{ $d['text'] }}">
                                                        {{ $d['text'] }}
                                                    </div>
                                                    <div
                                                        class="w-32 h-2 bg-slate-100 rounded-full overflow-hidden shrink-0">
                                                        <div class="h-full rounded-full transition-all"
                                                            style="width: {{ $d['percent'] }}%; background-color: {{ $d['is_correct'] ? '#10b981' : ($d['effective'] ? '#f59e0b' : '#cbd5e1') }};">
                                                        </div>
                                                    </div>
                                                    <div
                                                        class="w-12 text-right font-mono font-bold text-xs text-slate-500">
                                                        {{ $d['percent'] }}%</div>
                                                    <div class="w-24 shrink-0">
                                                        @if(!$d['is_correct'])
                                                        @if($d['effective'])
                                                        <span
                                                            class="px-2 py-0.5 bg-amber-100 text-amber-700 rounded text-[9px] font-black uppercase">Efektif</span>
                                                        @else
                                                        <span
                                                            class="px-2 py-0.5 bg-slate-100 text-slate-500 rounded text-[9px] font-black uppercase">Tdk
                                                            Efektif</span>
                                                        @endif
                                                        @endif
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        </td>
                                    </tr>
                                    @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- TAB 3: REFERENSI --}}
                <div x-show="activeTab === 'ref'" x-cloak class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

                    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
                        <div class="bg-slate-50 px-4 py-3 border-b border-slate-200 font-black text-sm text-slate-700">
                            Tingkat Kesukaran (TK)</div>
                        <div class="p-4 space-y-3 text-sm">
                            <div class="flex justify-between items-center"><span
                                    class="font-mono font-bold text-slate-600">&gt; 0.70</span> <span
                                    class="px-2 py-1 bg-sky-100 text-sky-700 font-bold rounded text-xs">Mudah</span>
                            </div>
                            <div class="flex justify-between items-center"><span
                                    class="font-mono font-bold text-slate-600">0.30 – 0.70</span> <span
                                    class="px-2 py-1 bg-emerald-100 text-emerald-700 font-bold rounded text-xs">Sedang</span>
                            </div>
                            <div class="flex justify-between items-center"><span
                                    class="font-mono font-bold text-slate-600">&lt; 0.30</span> <span
                                    class="px-2 py-1 bg-rose-100 text-rose-700 font-bold rounded text-xs">Sulit</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
                        <div class="bg-slate-50 px-4 py-3 border-b border-slate-200 font-black text-sm text-slate-700">
                            Daya Beda (DB)</div>
                        <div class="p-4 space-y-3 text-sm">
                            <div class="flex justify-between items-center"><span
                                    class="font-mono font-bold text-slate-600">≥ 0.40</span> <span
                                    class="px-2 py-1 bg-emerald-100 text-emerald-700 font-bold rounded text-xs">Sangat
                                    Baik</span></div>
                            <div class="flex justify-between items-center"><span
                                    class="font-mono font-bold text-slate-600">0.30 – 0.39</span> <span
                                    class="px-2 py-1 bg-sky-100 text-sky-700 font-bold rounded text-xs">Baik</span>
                            </div>
                            <div class="flex justify-between items-center"><span
                                    class="font-mono font-bold text-slate-600">0.20 – 0.29</span> <span
                                    class="px-2 py-1 bg-amber-100 text-amber-700 font-bold rounded text-xs">Cukup</span>
                            </div>
                            <div class="flex justify-between items-center"><span
                                    class="font-mono font-bold text-slate-600">&lt; 0.20</span> <span
                                    class="px-2 py-1 bg-rose-100 text-rose-700 font-bold rounded text-xs">Jelek</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
                        <div class="bg-slate-50 px-4 py-3 border-b border-slate-200 font-black text-sm text-slate-700">
                            Validitas (r)</div>
                        <div class="p-4 space-y-3 text-sm">
                            <div class="flex justify-between items-center"><span
                                    class="font-mono font-bold text-slate-600">≥ 0.30</span> <span
                                    class="px-2 py-1 bg-emerald-100 text-emerald-700 font-bold rounded text-xs">Valid</span>
                            </div>
                            <div class="flex justify-between items-center"><span
                                    class="font-mono font-bold text-slate-600">&lt; 0.30</span> <span
                                    class="px-2 py-1 bg-rose-100 text-rose-700 font-bold rounded text-xs">Tidak
                                    Valid</span></div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
                        <div class="bg-slate-50 px-4 py-3 border-b border-slate-200 font-black text-sm text-slate-700">
                            Efektivitas Distraktor</div>
                        <div class="p-4 space-y-3 text-sm">
                            <div class="flex justify-between items-center"><span
                                    class="font-mono font-bold text-slate-600">Dipilih ≥ 5%</span> <span
                                    class="px-2 py-1 bg-amber-100 text-amber-700 font-bold rounded text-xs">Efektif</span>
                            </div>
                            <div class="flex justify-between items-center"><span
                                    class="font-mono font-bold text-slate-600">Dipilih &lt; 5%</span> <span
                                    class="px-2 py-1 bg-slate-100 text-slate-600 font-bold rounded text-xs">Tdk
                                    Efektif</span></div>
                        </div>
                    </div>

                </div>
            </div>
</x-app-layout>