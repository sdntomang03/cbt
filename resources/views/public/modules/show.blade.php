<x-public-layout :pageTitle="$module->title . ' - CBT Pro'"
    :metaDescription="$module->description ?? 'Modul pembelajaran interaktif CBT Pro.'"
    :metaImage="$module->thumbnail ?? null">

    {{-- ========================================== --}}
    {{-- STYLE: Prose Fallback + Video Fix --}}
    {{-- ========================================== --}}
    <style>
        /* ── Video responsive (pure CSS, tanpa plugin Tailwind) ── */
        .video-wrapper {
            position: relative;
            width: 100%;
            padding-bottom: 56.25%;
            /* 16:9 ratio */
            height: 0;
            overflow: hidden;
            border-radius: 1rem;
            background: #0f172a;
        }

        .video-wrapper iframe {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            border: 0;
        }

        /* ── Prose fallback (jika @tailwindcss/typography belum aktif) ── */
        .module-content {
            color: #475569;
            font-size: 1.0625rem;
            line-height: 1.85;
        }

        .module-content h1,
        .module-content h2,
        .module-content h3,
        .module-content h4 {
            color: #1e293b;
            font-weight: 800;
            margin-top: 2em;
            margin-bottom: 0.75em;
            line-height: 1.3;
        }

        .module-content h1 {
            font-size: 1.875rem;
        }

        .module-content h2 {
            font-size: 1.5rem;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 0.4em;
        }

        .module-content h3 {
            font-size: 1.25rem;
        }

        .module-content h4 {
            font-size: 1.0625rem;
        }

        .module-content p {
            margin-top: 0;
            margin-bottom: 1.25em;
        }

        .module-content a {
            color: #4f46e5;
            text-decoration: underline;
        }

        .module-content a:hover {
            color: #4338ca;
        }

        .module-content strong {
            color: #1e293b;
            font-weight: 700;
        }

        .module-content em {
            font-style: italic;
        }

        .module-content ul,
        .module-content ol {
            padding-left: 1.75em;
            margin-bottom: 1.25em;
        }

        .module-content ul {
            list-style-type: disc;
        }

        .module-content ol {
            list-style-type: decimal;
        }

        .module-content li {
            margin-bottom: 0.4em;
        }

        .module-content blockquote {
            border-left: 4px solid #818cf8;
            padding: 0.5em 1.25em;
            margin: 1.5em 0;
            background: #eef2ff;
            border-radius: 0 0.5rem 0.5rem 0;
            color: #4338ca;
            font-style: italic;
        }

        .module-content pre {
            background: #1e293b;
            color: #e2e8f0;
            padding: 1.25em;
            border-radius: 0.75rem;
            overflow-x: auto;
            margin-bottom: 1.25em;
            font-size: 0.9em;
        }

        .module-content code {
            background: #f1f5f9;
            color: #e11d48;
            padding: 0.15em 0.4em;
            border-radius: 0.35em;
            font-size: 0.9em;
        }

        .module-content pre code {
            background: transparent;
            color: inherit;
            padding: 0;
        }

        .module-content table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5em;
            font-size: 0.9375rem;
        }

        .module-content th,
        .module-content td {
            border: 1px solid #e2e8f0;
            padding: 0.6em 0.9em;
            text-align: left;
        }

        .module-content th {
            background: #f8fafc;
            font-weight: 700;
            color: #1e293b;
        }

        .module-content tr:nth-child(even) td {
            background: #f8fafc;
        }

        .module-content img {
            max-width: 100%;
            border-radius: 0.75rem;
            margin: 1.5em auto;
            display: block;
            box-shadow: 0 4px 20px rgba(0, 0, 0, .08);
        }

        /* Quill formula */
        .module-content .ql-formula {
            display: inline-block;
        }
    </style>

    <div class="bg-slate-50 min-h-screen pb-20">

        {{-- ========================================== --}}
        {{-- HERO SECTION --}}
        {{-- ========================================== --}}
        <div class="w-full bg-slate-900 relative overflow-hidden pt-20 pb-24">
            @if($module->thumbnail)
            <div class="absolute inset-0 bg-cover bg-center opacity-20 filter blur-sm"
                style="background-image: url('{{ asset('storage/' . $module->thumbnail) }}')"></div>
            @else
            <div class="absolute inset-0 bg-gradient-to-r from-blue-800 to-slate-900 opacity-90"></div>
            @endif

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
                <span
                    class="inline-block px-4 py-1.5 rounded-full bg-blue-500/20 text-blue-300 text-xs font-black tracking-widest uppercase mb-6 border border-blue-400/30">
                    <i class="fas fa-book-reader mr-1"></i> Modul Belajar
                </span>
                <h1
                    class="text-4xl md:text-5xl lg:text-6xl font-black text-white tracking-tight mb-6 max-w-4xl mx-auto leading-tight">
                    {{ $module->title }}
                </h1>
                <p class="text-lg text-slate-300 max-w-2xl mx-auto font-medium">
                    {{ $module->description ?? 'Pelajari materi dengan cermat untuk meningkatkan pemahaman Anda.' }}
                </p>
            </div>
        </div>

        {{-- ========================================== --}}
        {{-- KONTEN UTAMA & SIDEBAR --}}
        {{-- ========================================== --}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10 relative z-20">
            <div class="flex flex-col lg:flex-row gap-8">

                {{-- KIRI: Konten Artikel --}}
                <div class="lg:w-2/3">
                    <article
                        class="bg-white rounded-[2rem] shadow-sm border border-slate-200 p-8 sm:p-12 overflow-hidden">

                        <header
                            class="mb-8 border-b border-slate-100 pb-4 flex flex-wrap justify-between items-center gap-4">
                            <a href="{{ route('public.modules.index') }}"
                                class="inline-flex items-center text-sm font-bold text-slate-500 hover:text-blue-600 transition-colors">
                                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Katalog
                            </a>
                            <div class="text-sm font-bold text-slate-400">
                                <i class="far fa-eye mr-1"></i> {{ number_format($module->view_count) }}x Dibaca
                            </div>
                        </header>

                        {{-- ── FIX VIDEO: konversi URL YouTube ke embed ── --}}
                        @if($module->video_url)
                        @php
                        /**
                        * Konversi berbagai format URL YouTube ke embed URL.
                        * Format yang didukung:
                        * https://www.youtube.com/watch?v=VIDEO_ID
                        * https://youtu.be/VIDEO_ID
                        * https://www.youtube.com/live/VIDEO_ID
                        * https://www.youtube.com/shorts/VIDEO_ID
                        * https://www.youtube.com/embed/VIDEO_ID (sudah embed, langsung pakai)
                        */
                        $videoId = null;
                        $videoUrl = trim($module->video_url);
                        $parsed = parse_url($videoUrl);
                        $host = strtolower($parsed['host'] ?? '');
                        $path = $parsed['path'] ?? '';

                        if (str_contains($host, 'youtube.com')) {
                        if (str_starts_with($path, '/embed/')) {
                        // Sudah dalam format embed — pakai langsung
                        $embedUrl = $videoUrl;
                        } elseif (str_starts_with($path, '/shorts/')) {
                        $videoId = trim(explode('/', $path)[2] ?? '');
                        } elseif (str_starts_with($path, '/live/')) {
                        $videoId = trim(explode('/', $path)[2] ?? '');
                        } else {
                        // /watch?v=VIDEO_ID
                        parse_str($parsed['query'] ?? '', $query);
                        $videoId = $query['v'] ?? null;
                        }
                        } elseif (str_contains($host, 'youtu.be')) {
                        $videoId = ltrim($path, '/');
                        }

                        if (!isset($embedUrl)) {
                        $embedUrl = $videoId
                        ? 'https://www.youtube-nocookie.com/embed/' . $videoId . '?rel=0&modestbranding=1'
                        : null;
                        }
                        @endphp

                        @if($embedUrl)
                        <div class="video-wrapper mb-10 shadow-md">
                            <iframe src="{{ $embedUrl }}"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
                        </div>
                        @endif
                        @endif

                        {{-- ── FIX PROSE: gunakan .module-content ── --}}
                        <div class="module-content prose prose-indigo prose-lg max-w-none">
                            {!! $module->content !!}
                        </div>

                    </article>
                </div>

                {{-- KANAN: Sidebar --}}
                <div class="lg:w-1/3">
                    <div
                        class="bg-white rounded-[2rem] shadow-xl shadow-indigo-500/5 border border-slate-200 p-8 sticky top-28">

                        <h3 class="font-black text-slate-800 text-lg mb-6 border-b border-slate-100 pb-4">
                            Detail Modul
                        </h3>

                        <div class="space-y-4 mb-8">
                            <div
                                class="flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-100">
                                <div class="flex items-center gap-3 text-slate-600 font-bold">
                                    <i class="fas fa-book text-blue-500 text-xl w-6"></i> Mapel
                                </div>
                                <div class="font-black text-slate-800">{{ $module->subject->name ?? '-' }}</div>
                            </div>
                            <div
                                class="flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-100">
                                <div class="flex items-center gap-3 text-slate-600 font-bold">
                                    <i class="fas fa-layer-group text-orange-500 text-xl w-6"></i> Tingkat
                                </div>
                                <div class="font-black text-slate-800">{{ $module->level->name ?? '-' }}</div>
                            </div>
                            <div
                                class="flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-100">
                                <div class="flex items-center gap-3 text-slate-600 font-bold">
                                    <i class="fas fa-stopwatch text-emerald-500 text-xl w-6"></i> Estimasi
                                </div>
                                <div class="font-black text-slate-800">{{ $module->estimated_time_minutes }} Menit</div>
                            </div>
                            <div
                                class="flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-100">
                                <div class="flex items-center gap-3 text-slate-600 font-bold">
                                    <i class="fas fa-user-edit text-purple-500 text-xl w-6"></i> Penulis
                                </div>
                                <div class="font-black text-slate-800 text-right truncate max-w-[120px]">
                                    {{ $module->author->name ?? 'Admin' }}
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            @if($module->document_path)
                            <a href="{{ asset('storage/' . $module->document_path) }}" target="_blank"
                                class="w-full bg-white border-2 border-rose-100 hover:border-rose-500 text-rose-600 hover:text-rose-700 font-bold py-3.5 px-4 rounded-xl text-sm text-center transition-all flex items-center justify-center gap-2 shadow-sm group">
                                <i class="fas fa-file-pdf text-lg group-hover:scale-110 transition-transform"></i>
                                Download Rangkuman PDF
                            </a>
                            @endif

                            <form action="#" method="POST">
                                @csrf
                                <button type="submit"
                                    class="w-full bg-slate-900 hover:bg-blue-600 text-white font-bold py-4 px-4 rounded-xl text-sm text-center transition-all flex items-center justify-center gap-2 shadow-md group">
                                    <i class="fas fa-check-circle group-hover:scale-110 transition-transform"></i>
                                    Tandai Selesai (+{{ $module->reward_points }} Poin)
                                </button>
                            </form>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- KATEX --}}
    {{-- ========================================== --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css">
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/contrib/auto-render.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // 1. Render elemen formula bawaan Quill (span.ql-formula)
            document.querySelectorAll('.ql-formula').forEach(el => {
                const exp = el.getAttribute('data-value');
                if (exp) {
                    const decoded = exp
                        .replace(/&gt;/g, '>')
                        .replace(/&lt;/g, '<')
                        .replace(/&amp;/g, '&');
                    try {
                        window.katex.render(decoded, el, { throwOnError: false });
                    } catch (e) {
                        console.error("KaTeX Render Error:", e);
                    }
                }
            });

            // 2. Auto-render LaTeX manual ($$ / $ / \( \[ )
            if (typeof renderMathInElement === 'function') {
                renderMathInElement(document.body, {
                    delimiters: [
                        { left: '$$', right: '$$', display: true },
                        { left: '$',  right: '$',  display: false },
                        { left: '\\(', right: '\\)', display: false },
                        { left: '\\[', right: '\\]', display: true }
                    ],
                    throwOnError: false
                });
            }
        });
    </script>
</x-public-layout>