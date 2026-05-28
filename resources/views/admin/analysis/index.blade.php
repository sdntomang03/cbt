{{-- resources/views/teacher/analysis/index.blade.php --}}
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
            --sans: 'Sora', sans-serif;
            --mono: 'IBM Plex Mono', monospace;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: var(--sans);
            background: #f1f5f9;
            color: var(--ink);
        }

        .wrap {
            max-width: 860px;
            margin: 0 auto;
            padding: 2rem 1.5rem 4rem;
        }

        /* Header */
        .page-header {
            margin-bottom: 2rem;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .5rem 1rem;
            border-radius: .6rem;
            background: white;
            border: 1.5px solid var(--border);
            font-size: .82rem;
            font-weight: 600;
            color: var(--ink2);
            text-decoration: none;
            transition: all .15s;
            margin-bottom: .9rem;
        }

        .btn-back:hover {
            border-color: var(--indigo);
            color: var(--indigo);
        }

        .page-header h1 {
            font-size: 1.5rem;
            font-weight: 800;
            margin: 0 0 .25rem;
        }

        .page-header p {
            font-size: .85rem;
            color: var(--ink2);
            margin: 0;
        }

        /* Exam info strip */
        .exam-strip {
            background: white;
            border: 1.5px solid var(--border);
            border-radius: 1rem;
            padding: 1.2rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.8rem;
        }

        .exam-strip .icon {
            width: 46px;
            height: 46px;
            border-radius: .75rem;
            background: var(--indigo);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 1.2rem;
        }

        .exam-strip .name {
            font-weight: 700;
            font-size: 1rem;
        }

        .exam-strip .meta {
            font-size: .8rem;
            color: var(--ink2);
            margin-top: .15rem;
        }

        /* Session cards */
        .section-label {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--ink2);
            margin-bottom: .75rem;
        }

        .session-list {
            display: flex;
            flex-direction: column;
            gap: .75rem;
        }

        .session-card {
            background: white;
            border: 1.5px solid var(--border);
            border-radius: 1rem;
            padding: 1.2rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            transition: border-color .15s, box-shadow .15s;
            text-decoration: none;
            color: inherit;
        }

        .session-card:hover {
            border-color: var(--indigo);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, .08);
        }

        .session-card.disabled {
            opacity: .55;
            cursor: not-allowed;
            pointer-events: none;
        }

        .session-card .left {
            flex: 1;
            min-width: 0;
        }

        .session-card .sname {
            font-weight: 700;
            font-size: .95rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .session-card .smeta {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem .9rem;
            margin-top: .35rem;
        }

        .session-card .smeta span {
            font-size: .78rem;
            color: var(--ink2);
            display: flex;
            align-items: center;
            gap: .25rem;
        }

        .session-card .right {
            display: flex;
            align-items: center;
            gap: .75rem;
            flex-shrink: 0;
        }

        /* Peserta badge */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .3rem .75rem;
            border-radius: .5rem;
            font-size: .76rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .badge-green {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-gray {
            background: #f1f5f9;
            color: #475569;
        }

        .badge-amber {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-red {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Chevron icon */
        .chevron {
            color: #cbd5e1;
            transition: color .15s;
        }

        .session-card:hover .chevron {
            color: var(--indigo);
        }

        /* Empty state */
        .empty {
            text-align: center;
            padding: 3.5rem 2rem;
            background: white;
            border: 1.5px dashed var(--border);
            border-radius: 1rem;
            color: var(--ink2);
        }

        .empty svg {
            margin-bottom: .75rem;
            opacity: .3;
        }

        .empty p {
            margin: 0;
            font-size: .88rem;
        }
    </style>

    <div class="wrap">

        {{-- Header --}}
        <div class="page-header">
            <a href="{{ url()->previous() }}" class="btn-back">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M19 12H5M12 5l-7 7 7 7" />
                </svg>
                Kembali
            </a>
            <h1>Analisis Butir Soal</h1>
            <p>Pilih sesi ujian yang ingin dianalisis</p>
        </div>

        {{-- Exam Info --}}
        <div class="exam-strip">
            <div class="icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 11l3 3L22 4" />
                    <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11" />
                </svg>
            </div>
            <div>
                <div class="name">{{ $exam->title }}</div>
                <div class="meta">
                    {{ $sessions->count() }} sesi tersedia
                    @if($exam->duration_minutes)
                    &bull; {{ $exam->duration_minutes }} menit
                    @endif
                </div>
            </div>
        </div>

        {{-- Session List --}}
        @if($sessions->isEmpty())
        <div class="empty">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="8" x2="12" y2="12" />
                <line x1="12" y1="16" x2="12.01" y2="16" />
            </svg>
            <p>Belum ada sesi untuk ujian ini.</p>
        </div>
        @else
        <div class="section-label">Daftar Sesi</div>
        <div class="session-list">
            @foreach($sessions as $session)
            @php
            $completed = $session->completed_count ?? 0;
            $canAnalyze = $completed >= 2;
            @endphp
            <a href="{{ $canAnalyze ? route('teacher.analysis.show', [$exam, $session]) : '#' }}"
                class="session-card {{ $canAnalyze ? '' : 'disabled' }}"
                title="{{ $canAnalyze ? '' : 'Minimal 2 peserta selesai untuk analisis' }}">

                <div class="left">
                    <div class="sname">
                        {{ $session->name ?? 'Sesi ' . $loop->iteration }}
                    </div>
                    <div class="smeta">
                        <span>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                <line x1="16" y1="2" x2="16" y2="6" />
                                <line x1="8" y1="2" x2="8" y2="6" />
                                <line x1="3" y1="10" x2="21" y2="10" />
                            </svg>
                            {{ \Carbon\Carbon::parse($session->start_time)->format('d M Y, H:i') }}
                            &ndash;
                            {{ \Carbon\Carbon::parse($session->end_time)->format('H:i') }}
                        </span>
                        <span>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M23 21v-2a4 4 0 00-3-3.87" />
                                <path d="M16 3.13a4 4 0 010 7.75" />
                            </svg>
                            Token: <strong>{{ $session->token ?? '-' }}</strong>
                        </span>
                    </div>
                </div>

                <div class="right">
                    @if($completed === 0)
                    <span class="badge badge-gray">0 selesai</span>
                    @elseif($completed < 2) <span class="badge badge-amber">{{ $completed }} selesai</span>
                        @else
                        <span class="badge badge-green">{{ $completed }} selesai</span>
                        @endif

                        @if($canAnalyze)
                        <svg class="chevron" width="18" height="18" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2.5">
                            <polyline points="9 18 15 12 9 6" />
                        </svg>
                        @else
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                            <path d="M7 11V7a5 5 0 0110 0v4" />
                        </svg>
                        @endif
                </div>
            </a>
            @endforeach
        </div>

        {{-- Keterangan --}}
        <p style="margin-top:1.2rem;font-size:.78rem;color:var(--ink2);">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                style="vertical-align:middle">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="8" x2="12" y2="12" />
                <line x1="12" y1="16" x2="12.01" y2="16" />
            </svg>
            Analisis memerlukan minimal <strong>2 peserta</strong> yang telah menyelesaikan ujian.
        </p>
        @endif

    </div>
</x-app-layout>