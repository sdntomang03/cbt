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

            <a href="{{ route('admin.analysis.export', [$exam, $session]) }}"
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
                    α ≥ 0.90 (Sangat Tinggi) • ≥ 0.80 (Tinggi) • ≥ 0.70 (Dapat Diterima) •
                    ≥ 0.60 (Perlu Ditinjau) • &lt; 0.60 (Rendah)
                    <span class="block mt-1 text-slate-500">Interpretasi bersifat indikatif dan perlu dikonfirmasi
                        dengan jumlah peserta yang memadai.</span>
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
                    <button @click="activeTab = 'chart'"
                        class="pb-3 text-sm font-black whitespace-nowrap transition-colors border-b-2"
                        :class="activeTab === 'chart' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-400 hover:text-slate-700'">
                        <i class="fas fa-chart-bar mr-2"></i> Grafik Dinamis
                    </button>
                    <button @click="activeTab = 'ref'"
                        class="pb-3 text-sm font-black whitespace-nowrap transition-colors border-b-2"
                        :class="activeTab === 'ref' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-400 hover:text-slate-700'">
                        <i class="fas fa-info-circle mr-2"></i> Keterangan Kriteria
                    </button>
                    <button @click="activeTab = 'ai'"
                        class="pb-3 text-sm font-black whitespace-nowrap transition-colors border-b-2"
                        :class="activeTab === 'ai' ? 'border-violet-600 text-violet-600' : 'border-transparent text-slate-400 hover:text-slate-700'">
                        <i class="fas fa-robot mr-2"></i> Kesimpulan AI
                    </button>
                </div>

                <div x-show="activeTab === 'chart'" x-cloak class="space-y-5">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-4">
                            <div>
                                <h3 class="font-black text-slate-800">Grafik Analisis Semua Butir</h3>
                                <p class="text-xs text-slate-500 mt-1">Pilih beberapa soal untuk membandingkan
                                    indeksnya.</p>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" onclick="selectChartItems(true)"
                                    class="text-xs font-bold text-indigo-600">Pilih Semua</button>
                                <button type="button" onclick="selectChartItems(false)"
                                    class="text-xs font-bold text-slate-500">Kosongkan</button>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-2 mb-5 max-h-32 overflow-y-auto">
                            @foreach($items as $i => $item)
                            <label class="text-xs font-bold text-slate-600 flex items-center gap-1">
                                <input type="checkbox" class="chart-item rounded text-indigo-600"
                                    value="{{ $item['id'] }}" checked onchange="renderItemChart()">
                                Soal {{ $i + 1 }}
                            </label>
                            @endforeach
                        </div>
                        <div class="relative h-[360px]">
                            <canvas id="itemAnalysisChart"></canvas>
                        </div>
                    </div>
                </div>

                <div x-show="activeTab === 'ai'" x-cloak class="space-y-5">
                    <div class="rounded-2xl border border-violet-200 bg-violet-50 p-5">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="font-black text-violet-900">Kesimpulan dan Tindak Lanjut</h3>
                                <p class="mt-1 text-xs text-violet-700">AI membaca isi soal yang sulit dan berdaya beda
                                    rendah untuk menyarankan materi yang perlu diperkuat.</p>
                            </div>
                            <button type="button" id="aiConclusionButton" onclick="requestConclusion()"
                                class="shrink-0 rounded-lg bg-violet-600 px-4 py-2 text-xs font-bold text-white hover:bg-violet-700">
                                <i class="fas fa-robot mr-1"></i> Buat Kesimpulan
                            </button>
                        </div>
                    </div>
                    <div id="aiConclusion" class="hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-3 text-xs font-black uppercase tracking-wider text-violet-700">Hasil Analisis AI
                        </div>
                        <div id="aiConclusionContent"
                            class="prose prose-sm max-w-none overflow-hidden break-words text-slate-700 [&_h2]:mt-5 [&_h2]:mb-2 [&_h3]:mt-4 [&_h3]:mb-2 [&_h4]:mt-3 [&_h4]:mb-1 [&_ul]:my-2 [&_ol]:my-2 [&_li]:my-1">
                        </div>
                    </div>
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
                            <div class="w-full overflow-x-auto bg-white rounded-xl shadow-sm border border-slate-200">
                                <table class="w-full text-left border-collapse whitespace-nowrap">
                                    <thead
                                        class="bg-slate-50 border-b border-slate-200 text-[10px] uppercase tracking-widest text-slate-500 font-black sticky top-0 z-10">
                                        <tr>
                                            <th class="px-4 py-4 w-12 text-center">No</th>
                                            <th class="px-5 py-4 min-w-[250px]">Soal & Tipe</th>
                                            <th class="px-4 py-4 text-center">TK</th>
                                            <th class="px-4 py-4 text-center">Daya Beda</th>
                                            <th class="px-4 py-4 text-center">r-Hitung</th>
                                            <th class="px-4 py-4 text-center">Validitas</th>
                                            <th class="px-4 py-4 text-center">Rekomendasi</th>
                                            <th class="px-4 py-4 text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 text-sm">
                                        @foreach ($items as $i => $item)
                                        <tr x-show="(filterTk === '' || filterTk === '{{ $item['tk_label'] }}') &&
                        (filterDb === '' || filterDb === '{{ $item['db_label'] }}') &&
                        (filterValid === '' || filterValid === '{{ $item['valid'] ? '1' : '0' }}') &&
                        ('{{ strtolower($item['content']) }}'.includes(search.toLowerCase()))"
                                            class="hover:bg-slate-50/80 transition-colors {{ !$item['valid'] ? 'bg-rose-50/40' : 'bg-white' }}">

                                            <!-- No -->
                                            <td class="px-4 py-4 text-center font-mono font-bold text-slate-400">
                                                {{ $i + 1 }}
                                            </td>

                                            <!-- Soal & Tipe -->
                                            <td class="px-5 py-4 whitespace-normal min-w-[250px] max-w-sm align-top">
                                                <div class="font-semibold text-slate-800 line-clamp-2 leading-relaxed mb-1.5"
                                                    title="{{ $item['content'] }}">
                                                    {{ $item['content'] }}
                                                </div>
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-500 border border-slate-200">
                                                    {{ str_replace('_', ' ', $item['type']) }}
                                                </span>
                                            </td>

                                            <!-- TK -->
                                            <td class="px-4 py-4 text-center align-top">
                                                <div class="font-mono font-bold text-slate-800">{{
                                                    number_format($item['tk'], 3) }}</div>
                                                <div
                                                    class="text-[10px] font-bold uppercase tracking-wider mt-1
                        {{ $item['tk_label'] == 'Mudah' ? 'text-sky-500' : ($item['tk_label'] == 'Sedang' ? 'text-emerald-500' : 'text-rose-500') }}">
                                                    {{ $item['tk_label'] }}
                                                </div>
                                            </td>

                                            <!-- Daya Beda -->
                                            <td class="px-4 py-4 text-center align-top">
                                                <div
                                                    class="font-mono font-bold {{ $item['db'] <= 0 ? 'text-rose-600' : 'text-slate-800' }}">
                                                    {{ number_format($item['db'], 3) }}
                                                </div>
                                                <div
                                                    class="text-[10px] font-bold uppercase tracking-wider mt-1
                        {{ in_array($item['db_label'], ['Sangat Baik', 'Baik']) ? 'text-emerald-500' : (in_array($item['db_label'], ['Jelek', 'Sangat Jelek (Revisi/Buang)']) ? 'text-rose-500' : 'text-amber-500') }}">
                                                    {{ $item['db_label'] }}
                                                </div>
                                            </td>

                                            <!-- r-Hitung -->
                                            <td class="px-4 py-4 text-center align-top">
                                                <div
                                                    class="font-mono font-bold {{ $item['valid'] ? 'text-emerald-600' : 'text-rose-600' }}">
                                                    {{ number_format($item['validity'], 3) }}
                                                </div>
                                            </td>

                                            <!-- Validitas -->
                                            <td class="px-4 py-4 text-center align-top">
                                                <span
                                                    class="text-[11px] font-bold uppercase tracking-wider {{ $item['validity'] >= 0.30 ? 'text-emerald-600' : 'text-amber-600' }}">
                                                    {{ $item['validity_label'] }}
                                                </span>
                                            </td>

                                            <!-- Rekomendasi (Diperbarui) -->
                                            <td
                                                class="px-4 py-4 text-center align-top whitespace-normal min-w-[120px] max-w-[160px]">
                                                <span
                                                    class="inline-block w-full text-center rounded-md px-2.5 py-1.5 text-[10px] font-bold uppercase tracking-wide leading-tight
                        {{ $item['recommendation'] === 'Pertahankan' ? 'bg-emerald-100/80 text-emerald-700' : ($item['recommendation'] === 'Revisi' ? 'bg-amber-100/80 text-amber-700' : 'bg-rose-100/80 text-rose-700') }}">
                                                    {{ $item['recommendation'] }}
                                                </span>
                                            </td>

                                            <!-- Status -->
                                            <td class="px-4 py-4 text-center align-top">
                                                @if($item['valid'])
                                                <span
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold uppercase tracking-wide bg-emerald-100 text-emerald-700">
                                                    <i class="fas fa-check-circle"></i> Valid
                                                </span>
                                                @else
                                                <span
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold uppercase tracking-wide bg-rose-100 text-rose-700">
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
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
            <script>
                const analysisItems = @json($items);
let itemChart = null;

function selectChartItems(selected) {
    document.querySelectorAll('.chart-item').forEach(input => input.checked = selected);
    renderItemChart();
}

function renderItemChart() {
    const selectedIds = [...document.querySelectorAll('.chart-item:checked')].map(input => String(input.value));
    const selected = analysisItems.filter(item => selectedIds.includes(String(item.id)));
    const canvas = document.getElementById('itemAnalysisChart');
    if (!canvas || typeof Chart === 'undefined') return;

    if (itemChart) itemChart.destroy();
    itemChart = new Chart(canvas, {
        type: 'bar',
        data: {
            labels: selected.map(item => 'Soal ' + item.number),
            datasets: [
                { label: 'Kesukaran (P)', data: selected.map(item => item.tk), backgroundColor: '#38bdf8' },
                { label: 'Daya Beda (D)', data: selected.map(item => item.db), backgroundColor: '#818cf8' },
                { label: 'Validitas (r)', data: selected.map(item => item.validity), backgroundColor: '#34d399' }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { min: -1, max: 1, title: { display: true, text: 'Indeks' } } },
            plugins: { legend: { position: 'bottom' } }
        }
    });
}

async function requestConclusion() {
    const button = document.getElementById('aiConclusionButton');
    const box = document.getElementById('aiConclusion');
    const content = document.getElementById('aiConclusionContent');
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Menganalisis...';
    try {
        const response = await fetch('{{ route('admin.analysis.conclusion', [$exam, $session]) }}', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || 'Gagal mengambil kesimpulan.');
        const cleanContent = String(data.content || '')
            .replace(/^\s*```(?:html?|markdown)?\s*/i, '')
            .replace(/\s*```\s*$/i, '')
            .trim();
        content.textContent = '';
        content.insertAdjacentHTML('beforeend', cleanContent);
        box.classList.remove('hidden');
    } catch (error) {
        content.textContent = error.message;
        box.classList.remove('hidden');
    } finally {
        button.disabled = false;
        button.innerHTML = '<i class="fas fa-robot mr-1"></i> Kesimpulan AI';
    }
}

document.addEventListener('DOMContentLoaded', renderItemChart);
            </script>
</x-app-layout>