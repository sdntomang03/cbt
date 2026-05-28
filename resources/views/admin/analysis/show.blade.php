{{-- resources/views/teacher/analysis/show.blade.php --}}
<x-app-layout>
    <link
        href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;600&family=Sora:wght@400;600;700;800&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --ink: #0f172a;
            --ink2: #475569;
            --surface: #ffffff;
            --border: #e2e8f0;
            --muted: #f8fafc;
            --indigo: #4f46e5;
            --emerald: #10b981;
            --amber: #f59e0b;
            --rose: #f43f5e;
            --sky: #0ea5e9;
            --mono: 'IBM Plex Mono', monospace;
            --sans: 'Sora', sans-serif;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: var(--sans);
            background: #f1f5f9;
            color: var(--ink);
        }

        /* ── Page Shell ── */
        .analysis-wrap {
            max-width: 1280px;
            margin: 0 auto;
            padding: 2rem 1.5rem 4rem;
        }

        /* ── Page Header ── */
        .page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .page-header h1 {
            font-size: 1.5rem;
            font-weight: 800;
            margin: 0;
            line-height: 1.2;
        }

        .page-header p {
            font-size: .85rem;
            color: var(--ink2);
            margin: .25rem 0 0;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .55rem 1.1rem;
            border-radius: .65rem;
            background: white;
            border: 1.5px solid var(--border);
            font-family: var(--sans);
            font-size: .82rem;
            font-weight: 600;
            color: var(--ink2);
            text-decoration: none;
            transition: all .15s;
        }

        .btn-back:hover {
            border-color: var(--indigo);
            color: var(--indigo);
        }

        /* ── Summary Cards ── */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border: 1.5px solid var(--border);
            border-radius: 1rem;
            padding: 1.2rem 1.4rem;
            position: relative;
            overflow: hidden;
        }

        .stat-card .accent {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            border-radius: 1rem 1rem 0 0;
        }

        .stat-card .label {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--ink2);
            margin-bottom: .4rem;
        }

        .stat-card .value {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
        }

        .stat-card .sub {
            font-size: .75rem;
            color: var(--ink2);
            margin-top: .3rem;
        }

        /* ── Cronbach Alpha Block ── */
        .alpha-block {
            background: var(--ink);
            color: white;
            border-radius: 1rem;
            padding: 1.4rem 1.8rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .alpha-block .left h3 {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            opacity: .7;
        }

        .alpha-block .left .aval {
            font-family: var(--mono);
            font-size: 2.8rem;
            font-weight: 600;
            line-height: 1;
            margin-top: .2rem;
            background: linear-gradient(135deg, #a5b4fc, #34d399);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .alpha-block .right {
            text-align: right;
        }

        .alpha-block .badge {
            display: inline-block;
            padding: .4rem .9rem;
            border-radius: 2rem;
            font-size: .8rem;
            font-weight: 700;
            background: rgba(255, 255, 255, .15);
            color: white;
        }

        .alpha-block .desc {
            font-size: .78rem;
            opacity: .55;
            margin-top: .3rem;
        }

        /* ── Filter Bar ── */
        .filter-bar {
            display: flex;
            align-items: center;
            gap: .6rem;
            flex-wrap: wrap;
            margin-bottom: 1.2rem;
        }

        .filter-bar select,
        .filter-bar input {
            padding: .5rem .9rem;
            border-radius: .6rem;
            border: 1.5px solid var(--border);
            font-family: var(--sans);
            font-size: .82rem;
            background: white;
            color: var(--ink);
            outline: none;
            transition: border .15s;
        }

        .filter-bar select:focus,
        .filter-bar input:focus {
            border-color: var(--indigo);
        }

        .filter-bar label {
            font-size: .8rem;
            font-weight: 600;
            color: var(--ink2);
        }

        /* ── Table ── */
        .table-wrap {
            background: white;
            border: 1.5px solid var(--border);
            border-radius: 1rem;
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .analysis-table {
            width: 100%;
            border-collapse: collapse;
        }

        .analysis-table thead th {
            background: var(--muted);
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: var(--ink2);
            padding: .85rem 1rem;
            text-align: left;
            border-bottom: 1.5px solid var(--border);
            white-space: nowrap;
        }

        .analysis-table tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background .1s;
        }

        .analysis-table tbody tr:last-child {
            border-bottom: none;
        }

        .analysis-table tbody tr:hover {
            background: #f8fafc;
        }

        .analysis-table td {
            padding: .85rem 1rem;
            font-size: .84rem;
            vertical-align: middle;
        }

        .analysis-table td.q-content {
            max-width: 260px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: .82rem;
        }

        .analysis-table td.mono {
            font-family: var(--mono);
            font-size: .82rem;
        }

        /* ── Badges ── */
        .badge {
            display: inline-block;
            padding: .25rem .65rem;
            border-radius: .4rem;
            font-size: .72rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .badge-green {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-yellow {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-red {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-blue {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-purple {
            background: #ede9fe;
            color: #4c1d95;
        }

        .badge-gray {
            background: #f1f5f9;
            color: #475569;
        }

        /* Validity dot */
        .dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            display: inline-block;
            margin-right: .3rem;
        }

        .dot-ok {
            background: var(--emerald);
        }

        .dot-bad {
            background: var(--rose);
        }

        /* ── Progress bar ── */
        .mini-bar {
            display: flex;
            height: 6px;
            border-radius: 3px;
            overflow: hidden;
            background: var(--border);
            width: 80px;
        }

        .mini-bar-fill {
            border-radius: 3px;
            transition: width .3s;
        }

        /* ── Distractor Panel ── */
        .distractor-toggle {
            cursor: pointer;
            color: var(--indigo);
            font-size: .78rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .distractor-panel {
            display: none;
            background: var(--muted);
            padding: .8rem 1rem;
            border-top: 1px solid var(--border);
        }

        .distractor-panel.open {
            display: block;
        }

        .dist-row {
            display: flex;
            align-items: center;
            gap: .8rem;
            margin-bottom: .45rem;
            font-size: .8rem;
        }

        .dist-text {
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .dist-bar-wrap {
            width: 120px;
            height: 8px;
            background: #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
            flex-shrink: 0;
        }

        .dist-bar-fill {
            height: 100%;
            border-radius: 4px;
        }

        .dist-pct {
            width: 42px;
            text-align: right;
            font-family: var(--mono);
            font-size: .75rem;
            color: var(--ink2);
        }

        /* ── Tabs ── */
        .tabs {
            display: flex;
            gap: 0;
            border-bottom: 2px solid var(--border);
            margin-bottom: 1.5rem;
        }

        .tab-btn {
            padding: .65rem 1.2rem;
            font-family: var(--sans);
            font-size: .85rem;
            font-weight: 600;
            background: none;
            border: none;
            border-bottom: 2px solid transparent;
            cursor: pointer;
            color: var(--ink2);
            margin-bottom: -2px;
            transition: all .15s;
        }

        .tab-btn.active {
            border-bottom-color: var(--indigo);
            color: var(--indigo);
        }

        .tab-pane {
            display: none;
        }

        .tab-pane.active {
            display: block;
        }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .analysis-table {
                font-size: .78rem;
            }

            .analysis-table td,
            .analysis-table th {
                padding: .6rem .7rem;
            }

            .q-content {
                max-width: 140px;
            }
        }
    </style>

    <div class="analysis-wrap">

        {{-- Header --}}
        <div class="page-header">
            <div>
                <a href="{{ url()->previous() }}" class="btn-back">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5">
                        <path d="M19 12H5M12 5l-7 7 7 7" />
                    </svg>
                    Kembali
                </a>
                <h1 style="margin-top:.75rem">Analisis Butir Soal</h1>
                <p>{{ $exam->title }} &mdash; Sesi: {{ $session->name ??
                    \Carbon\Carbon::parse($session->start_time)->format('d M Y') }} &bull; {{ $total_students }} Peserta
                </p>
            </div>
            <a href="{{ route('teacher.analysis.export', [$exam, $session]) }}"
                style="display:inline-flex;align-items:center;gap:.4rem;padding:.6rem 1.2rem;border-radius:.65rem;background:var(--indigo);color:white;font-weight:700;font-size:.82rem;text-decoration:none;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3" />
                </svg>
                Export JSON
            </a>
        </div>

        {{-- Cronbach Alpha --}}
        <div class="alpha-block">
            <div class="left">
                <h3>Reliabilitas Cronbach Alpha</h3>
                <div class="aval">{{ number_format($alpha, 3) }}</div>
            </div>
            <div class="right">
                <div class="badge">{{ $summary['alpha_label'] }}</div>
                <div class="desc">
                    α ≥ 0.90 Sangat Tinggi &bull; ≥ 0.70 Tinggi &bull; ≥ 0.50 Cukup &bull; &lt; 0.50 Rendah
                </div>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="stat-grid">
            <div class="stat-card">
                <div class="accent" style="background:var(--indigo)"></div>
                <div class="label">Total Soal</div>
                <div class="value">{{ $summary['total_items'] }}</div>
                <div class="sub">{{ $total_students }} peserta</div>
            </div>
            <div class="stat-card">
                <div class="accent" style="background:var(--emerald)"></div>
                <div class="label">Valid</div>
                <div class="value" style="color:var(--emerald)">{{ $summary['valid_count'] }}</div>
                <div class="sub">r ≥ 0.30</div>
            </div>
            <div class="stat-card">
                <div class="accent" style="background:var(--rose)"></div>
                <div class="label">Tidak Valid</div>
                <div class="value" style="color:var(--rose)">{{ $summary['invalid_count'] }}</div>
                <div class="sub">r &lt; 0.30</div>
            </div>
            <div class="stat-card">
                <div class="accent" style="background:var(--sky)"></div>
                <div class="label">Mudah</div>
                <div class="value" style="color:var(--sky)">{{ $summary['mudah'] }}</div>
                <div class="sub">TK &gt; 0.70</div>
            </div>
            <div class="stat-card">
                <div class="accent" style="background:var(--emerald)"></div>
                <div class="label">Sedang</div>
                <div class="value" style="color:var(--emerald)">{{ $summary['sedang'] }}</div>
                <div class="sub">TK 0.30 – 0.70</div>
            </div>
            <div class="stat-card">
                <div class="accent" style="background:var(--rose)"></div>
                <div class="label">Sulit</div>
                <div class="value" style="color:var(--rose)">{{ $summary['sulit'] }}</div>
                <div class="sub">TK &lt; 0.30</div>
            </div>
            <div class="stat-card">
                <div class="accent" style="background:var(--amber)"></div>
                <div class="label">DB Baik+</div>
                <div class="value" style="color:var(--amber)">{{ $summary['db_sangat_baik'] + $summary['db_baik'] }}
                </div>
                <div class="sub">D ≥ 0.30</div>
            </div>
            <div class="stat-card">
                <div class="accent" style="background:var(--rose)"></div>
                <div class="label">DB Jelek</div>
                <div class="value" style="color:var(--rose)">{{ $summary['db_jelek'] }}</div>
                <div class="sub">D &lt; 0.20</div>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab('tbl', this)">Tabel Analisis</button>
            <button class="tab-btn" onclick="switchTab('dist', this)">Efektivitas Distraktor</button>
            <button class="tab-btn" onclick="switchTab('ref', this)">Keterangan Kriteria</button>
        </div>

        {{-- TAB 1: Tabel Utama --}}
        <div id="tab-tbl" class="tab-pane active">

            {{-- Filter --}}
            <div class="filter-bar">
                <label>Filter:</label>
                <select id="filter-tk" onchange="applyFilter()">
                    <option value="">Semua TK</option>
                    <option value="Mudah">Mudah</option>
                    <option value="Sedang">Sedang</option>
                    <option value="Sulit">Sulit</option>
                </select>
                <select id="filter-db" onchange="applyFilter()">
                    <option value="">Semua Daya Beda</option>
                    <option value="Sangat Baik">Sangat Baik</option>
                    <option value="Baik">Baik</option>
                    <option value="Cukup">Cukup</option>
                    <option value="Jelek">Jelek</option>
                </select>
                <select id="filter-valid" onchange="applyFilter()">
                    <option value="">Semua Validitas</option>
                    <option value="1">Valid</option>
                    <option value="0">Tidak Valid</option>
                </select>
                <input type="text" id="filter-search" placeholder="Cari isi soal…" oninput="applyFilter()">
            </div>

            <div class="table-wrap">
                <table class="analysis-table" id="main-table">
                    <thead>
                        <tr>
                            <th style="width:50px">No</th>
                            <th>Soal</th>
                            <th>Tipe</th>
                            <th>TK</th>
                            <th>Kat.</th>
                            <th>DB</th>
                            <th>Kat.</th>
                            <th>r hitung</th>
                            <th>Valid?</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $i => $item)
                        <tr class="item-row" data-tk="{{ $item['tk_label'] }}" data-db="{{ $item['db_label'] }}"
                            data-valid="{{ $item['valid'] ? '1' : '0' }}"
                            data-content="{{ strtolower($item['content']) }}">
                            <td class="mono">{{ $i + 1 }}</td>
                            <td class="q-content" title="{{ $item['content'] }}">{{ $item['content'] }}</td>
                            <td>
                                <span class="badge badge-gray">{{ str_replace('_', ' ', ucfirst($item['type']))
                                    }}</span>
                            </td>
                            <td class="mono">{{ number_format($item['tk'], 3) }}</td>
                            <td>
                                @if($item['tk_label'] === 'Mudah')
                                <span class="badge badge-blue">Mudah</span>
                                @elseif($item['tk_label'] === 'Sedang')
                                <span class="badge badge-green">Sedang</span>
                                @else
                                <span class="badge badge-red">Sulit</span>
                                @endif
                            </td>
                            <td class="mono">{{ number_format($item['db'], 3) }}</td>
                            <td>
                                @if($item['db_label'] === 'Sangat Baik')
                                <span class="badge badge-green">Sangat Baik</span>
                                @elseif($item['db_label'] === 'Baik')
                                <span class="badge badge-blue">Baik</span>
                                @elseif($item['db_label'] === 'Cukup')
                                <span class="badge badge-yellow">Cukup</span>
                                @else
                                <span class="badge badge-red">Jelek</span>
                                @endif
                            </td>
                            <td class="mono">{{ number_format($item['validity'], 3) }}</td>
                            <td>
                                @if($item['valid'])
                                <span class="dot dot-ok"></span><span
                                    style="color:var(--emerald);font-weight:700;font-size:.8rem">Valid</span>
                                @else
                                <span class="dot dot-bad"></span><span
                                    style="color:var(--rose);font-weight:700;font-size:.8rem">Tidak Valid</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- TAB 2: Distraktor --}}
        <div id="tab-dist" class="tab-pane">
            <div class="table-wrap">
                <table class="analysis-table">
                    <thead>
                        <tr>
                            <th style="width:50px">No</th>
                            <th>Soal</th>
                            <th colspan="4">Distribusi Pilihan Jawaban</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $i => $item)
                        @if(count($item['distractors']) > 0)
                        <tr>
                            <td class="mono" style="vertical-align:top;padding-top:1rem">{{ $i + 1 }}</td>
                            <td style="vertical-align:top;padding-top:1rem" class="q-content"
                                title="{{ $item['content'] }}">{{ $item['content'] }}</td>
                            <td colspan="4">
                                @foreach($item['distractors'] as $d)
                                <div class="dist-row">
                                    @if($d['is_correct'])
                                    <svg width="13" height="13" style="color:var(--emerald);flex-shrink:0"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                        <polyline points="20 6 9 17 4 12" />
                                    </svg>
                                    @else
                                    <svg width="13" height="13" style="color:var(--rose);flex-shrink:0"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                        <line x1="18" y1="6" x2="6" y2="18" />
                                        <line x1="6" y1="6" x2="18" y2="18" />
                                    </svg>
                                    @endif
                                    <div class="dist-text" title="{{ $d['text'] }}">{{ $d['text'] }}</div>
                                    <div class="dist-bar-wrap">
                                        <div class="dist-bar-fill"
                                            style="width:{{ $d['percent'] }}%;background:{{ $d['is_correct'] ? 'var(--emerald)' : ($d['effective'] ? 'var(--amber)' : 'var(--border)') }}">
                                        </div>
                                    </div>
                                    <div class="dist-pct">{{ $d['percent'] }}%</div>
                                    @if(!$d['is_correct'])
                                    @if($d['effective'])
                                    <span class="badge badge-yellow" style="font-size:.68rem">Efektif</span>
                                    @else
                                    <span class="badge badge-gray" style="font-size:.68rem">Tidak Efektif</span>
                                    @endif
                                    @endif
                                </div>
                                @endforeach
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- TAB 3: Referensi Kriteria --}}
        <div id="tab-ref" class="tab-pane">
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1rem;">

                <div class="table-wrap">
                    <div style="padding:1rem 1.2rem;font-weight:700;border-bottom:1.5px solid var(--border)">Tingkat
                        Kesukaran (TK)</div>
                    <table class="analysis-table">
                        <thead>
                            <tr>
                                <th>Nilai TK</th>
                                <th>Kategori</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="mono">&gt; 0.70</td>
                                <td><span class="badge badge-blue">Mudah</span></td>
                            </tr>
                            <tr>
                                <td class="mono">0.30 – 0.70</td>
                                <td><span class="badge badge-green">Sedang</span></td>
                            </tr>
                            <tr>
                                <td class="mono">&lt; 0.30</td>
                                <td><span class="badge badge-red">Sulit</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="table-wrap">
                    <div style="padding:1rem 1.2rem;font-weight:700;border-bottom:1.5px solid var(--border)">Daya Beda
                        (DB)</div>
                    <table class="analysis-table">
                        <thead>
                            <tr>
                                <th>Nilai D</th>
                                <th>Kategori</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="mono">≥ 0.40</td>
                                <td><span class="badge badge-green">Sangat Baik</span></td>
                            </tr>
                            <tr>
                                <td class="mono">0.30 – 0.39</td>
                                <td><span class="badge badge-blue">Baik</span></td>
                            </tr>
                            <tr>
                                <td class="mono">0.20 – 0.29</td>
                                <td><span class="badge badge-yellow">Cukup</span></td>
                            </tr>
                            <tr>
                                <td class="mono">&lt; 0.20</td>
                                <td><span class="badge badge-red">Jelek</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="table-wrap">
                    <div style="padding:1rem 1.2rem;font-weight:700;border-bottom:1.5px solid var(--border)">Validitas
                        Butir (r)</div>
                    <table class="analysis-table">
                        <thead>
                            <tr>
                                <th>Nilai r</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="mono">≥ 0.30</td>
                                <td><span class="badge badge-green">Valid</span></td>
                            </tr>
                            <tr>
                                <td class="mono">&lt; 0.30</td>
                                <td><span class="badge badge-red">Tidak Valid</span></td>
                            </tr>
                        </tbody>
                    </table>
                    <div style="padding:.75rem 1.2rem;font-size:.75rem;color:var(--ink2)">Menggunakan korelasi Pearson
                        antara skor item dan skor total.</div>
                </div>

                <div class="table-wrap">
                    <div style="padding:1rem 1.2rem;font-weight:700;border-bottom:1.5px solid var(--border)">Cronbach
                        Alpha (α)</div>
                    <table class="analysis-table">
                        <thead>
                            <tr>
                                <th>Nilai α</th>
                                <th>Reliabilitas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="mono">≥ 0.90</td>
                                <td><span class="badge badge-green">Sangat Tinggi</span></td>
                            </tr>
                            <tr>
                                <td class="mono">0.70 – 0.89</td>
                                <td><span class="badge badge-blue">Tinggi</span></td>
                            </tr>
                            <tr>
                                <td class="mono">0.50 – 0.69</td>
                                <td><span class="badge badge-yellow">Cukup</span></td>
                            </tr>
                            <tr>
                                <td class="mono">&lt; 0.50</td>
                                <td><span class="badge badge-red">Rendah</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="table-wrap">
                    <div style="padding:1rem 1.2rem;font-weight:700;border-bottom:1.5px solid var(--border)">Efektivitas
                        Distraktor</div>
                    <table class="analysis-table">
                        <thead>
                            <tr>
                                <th>Dipilih</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="mono">≥ 5%</td>
                                <td><span class="badge badge-yellow">Efektif</span></td>
                            </tr>
                            <tr>
                                <td class="mono">&lt; 5%</td>
                                <td><span class="badge badge-gray">Tidak Efektif</span></td>
                            </tr>
                        </tbody>
                    </table>
                    <div style="padding:.75rem 1.2rem;font-size:.75rem;color:var(--ink2)">Distraktor yang tidak dipilih
                        perlu diganti atau diperbaiki.</div>
                </div>

            </div>
        </div>

    </div>{{-- /analysis-wrap --}}

    <script>
        function switchTab(id, btn) {
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + id).classList.add('active');
    btn.classList.add('active');
}

function applyFilter() {
    const tk     = document.getElementById('filter-tk').value;
    const db     = document.getElementById('filter-db').value;
    const valid  = document.getElementById('filter-valid').value;
    const search = document.getElementById('filter-search').value.toLowerCase();

    document.querySelectorAll('#main-table .item-row').forEach(row => {
        let show = true;
        if (tk     && row.dataset.tk      !== tk)     show = false;
        if (db     && row.dataset.db      !== db)     show = false;
        if (valid  && row.dataset.valid   !== valid)  show = false;
        if (search && !row.dataset.content.includes(search)) show = false;
        row.style.display = show ? '' : 'none';
    });
}
    </script>
</x-app-layout>