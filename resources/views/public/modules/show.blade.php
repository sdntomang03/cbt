<x-public-layout :pageTitle="$module->title . ' - CBT Pro'"
    :metaDescription="$module->description ?? 'Modul pembelajaran interaktif CBT Pro.'"
    :metaImage="$module->thumbnail ?? null">

    <div class="bg-slate-50 min-h-screen pb-20">

        {{-- ========================================== --}}
        {{-- HERO SECTION (Thumbnail & Judul) --}}
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
        {{-- KONTEN UTAMA & SIDEBAR (2 KOLOM) --}}
        {{-- ========================================== --}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10 relative z-20">
            <div class="flex flex-col lg:flex-row gap-8">

                {{-- KIRI: Konten Artikel (Materi/Panduan) --}}
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

                        {{-- Frame Video Pembuat (Jika Ada) --}}
                        @if($module->video_url)
                        <div class="aspect-w-16 aspect-h-9 bg-slate-900 rounded-2xl overflow-hidden mb-10 shadow-md">
                            <iframe src="{{ $module->video_url }}" frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen class="w-full h-[350px] sm:h-[400px]"></iframe>
                        </div>
                        @endif

                        {{-- ============================================== --}}
                        {{-- KONTEN MATERI (DENGAN PROSE TAILWIND) --}}
                        {{-- ============================================== --}}
                        <div class="prose prose-indigo prose-lg max-w-none text-slate-600">
                            {!! $module->content !!}
                        </div>

                    </article>
                </div>

                {{-- KANAN: Sidebar Statistik & Tombol Pintar --}}
                <div class="lg:w-1/3">
                    <div
                        class="bg-white rounded-[2rem] shadow-xl shadow-indigo-500/5 border border-slate-200 p-8 sticky top-28">

                        <h3 class="font-black text-slate-800 text-lg mb-6 border-b border-slate-100 pb-4">
                            Detail Modul
                        </h3>

                        {{-- List Informasi Modul --}}
                        <div class="space-y-4 mb-8">
                            <div
                                class="flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-100">
                                <div class="flex items-center gap-3 text-slate-600 font-bold"><i
                                        class="fas fa-book text-blue-500 text-xl w-6"></i> Mapel</div>
                                <div class="font-black text-slate-800">{{ $module->subject->name ?? '-' }}</div>
                            </div>
                            <div
                                class="flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-100">
                                <div class="flex items-center gap-3 text-slate-600 font-bold"><i
                                        class="fas fa-layer-group text-orange-500 text-xl w-6"></i> Tingkat</div>
                                <div class="font-black text-slate-800">{{ $module->level->name ?? '-' }}</div>
                            </div>
                            <div
                                class="flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-100">
                                <div class="flex items-center gap-3 text-slate-600 font-bold"><i
                                        class="fas fa-stopwatch text-emerald-500 text-xl w-6"></i> Estimasi</div>
                                <div class="font-black text-slate-800">{{ $module->estimated_time_minutes }} Menit</div>
                            </div>
                            <div
                                class="flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-100">
                                <div class="flex items-center gap-3 text-slate-600 font-bold"><i
                                        class="fas fa-user-edit text-purple-500 text-xl w-6"></i> Penulis</div>
                                <div class="font-black text-slate-800 text-right truncate max-w-[120px]">{{
                                    $module->author->name ?? 'Admin' }}</div>
                            </div>
                        </div>

                        {{-- Tombol Aksi --}}
                        <div class="space-y-3">
                            @if($module->document_path)
                            <a href="{{ asset('storage/' . $module->document_path) }}" target="_blank"
                                class="w-full bg-white border-2 border-rose-100 hover:border-rose-500 text-rose-600 hover:text-rose-700 font-bold py-3.5 px-4 rounded-xl text-sm text-center transition-all flex items-center justify-center gap-2 shadow-sm group">
                                <i class="fas fa-file-pdf text-lg group-hover:scale-110 transition-transform"></i>
                                Download Rangkuman PDF
                            </a>
                            @endif

                            {{-- Tombol Klaim Poin (Gamifikasi) --}}
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
    {{-- SCRIPT KATEX UNTUK RENDER RUMUS LATEX --}}
    {{-- ========================================== --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css">
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>

    {{-- SANGAT PENTING: auto-render.min.js wajib ada agar "renderMathInElement" berfungsi --}}
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/contrib/auto-render.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // 1. Render elemen formula bawaan Quill (span dengan class ql-formula)
            document.querySelectorAll('.ql-formula').forEach(el => {
                const exp = el.getAttribute('data-value');
                if (exp) {
                    // Terjemahkan entitas HTML jika editor menyimpannya sebagai &gt; atau &lt;
                    const decoded = exp.replace(/&gt;/g, '>').replace(/&lt;/g, '<').replace(/&amp;/g, '&');
                    try {
                        window.katex.render(decoded, el, { throwOnError: false });
                    } catch (e) {
                        console.error("KaTeX Render Error:", e);
                    }
                }
            });

            // 2. Auto-render jika Admin mengetik kode LaTeX secara manual di dalam teks (menggunakan $$ atau $)
            if (typeof renderMathInElement === 'function') {
                renderMathInElement(document.body, {
                    delimiters: [
                        { left: '$$', right: '$$', display: true },
                        { left: '$', right: '$', display: false },
                        { left: '\\(', right: '\\)', display: false },
                        { left: '\\[', right: '\\]', display: true }
                    ],
                    throwOnError: false
                });
            }
        });
    </script>
</x-public-layout>