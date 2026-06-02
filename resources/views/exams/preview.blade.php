<x-app-layout>
    @section('title', 'PREVIEW: ' . $exam->title)

    @push('meta')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/contrib/auto-render.min.js"></script>
    @endpush

    <div class="bg-slate-50 min-h-screen pb-20">
        {{-- PITA PERINGATAN PREVIEW --}}
        @if(isset($isPreview) && $isPreview)
        <div
            class="bg-amber-500 text-amber-950 text-center py-2 text-sm font-black tracking-widest shadow-md relative z-50">
            <i class="fas fa-eye mr-2"></i> MODE PREVIEW - DATA BELUM DISIMPAN
        </div>
        @endif

        {{-- HERO SECTION --}}
        <div class="w-full bg-slate-900 relative overflow-hidden pt-20 pb-24">
            <div class="absolute inset-0 bg-gradient-to-r from-indigo-800 to-slate-900 opacity-90"></div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
                <span
                    class="inline-block px-4 py-1.5 rounded-full bg-indigo-500/20 text-indigo-300 text-xs font-black tracking-widest uppercase mb-6 border border-indigo-400/30">
                    <i class="fas fa-book-open mr-1"></i> Try Out
                </span>
                <h1
                    class="text-4xl md:text-5xl lg:text-6xl font-black text-white tracking-tight mb-6 max-w-4xl mx-auto leading-tight">
                    {{ $exam->title }}
                </h1>
                <p class="text-lg text-slate-300 max-w-2xl mx-auto font-medium">
                    {{ $exam->description }}
                </p>
            </div>
        </div>

        {{-- KONTEN & SIDEBAR --}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10 relative z-20">
            <div class="flex flex-col lg:flex-row gap-8">

                {{-- KONTEN KIRI --}}
                <div class="lg:w-2/3">
                    <article
                        class="bg-white rounded-[2rem] shadow-sm border border-slate-200 p-8 sm:p-12 overflow-hidden">
                        <header>
                            <h2 class="text-2xl font-black text-slate-800 mb-6 border-b border-slate-100 pb-4">
                                Informasi & Panduan Ujian
                            </h2>
                        </header>
                        <div class="prose prose-indigo prose-lg max-w-none text-slate-600">
                            {!! $exam->content !!}
                        </div>
                    </article>
                </div>

                {{-- SIDEBAR KANAN --}}
                <div class="lg:w-1/3">
                    <div
                        class="bg-white rounded-[2rem] shadow-xl shadow-indigo-500/5 border border-slate-200 p-8 sticky top-28">
                        <h3 class="font-black text-slate-800 text-lg mb-6">Detail Ujian</h3>
                        <div class="space-y-4 mb-8">
                            <div
                                class="flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-100">
                                <div class="flex items-center gap-3 text-slate-600 font-bold"><i
                                        class="fas fa-list-ol text-indigo-500 text-xl w-6"></i> Jumlah Soal</div>
                                <div class="font-black text-slate-800">{{ $exam->questions_count }}</div>
                            </div>
                            <div
                                class="flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-100">
                                <div class="flex items-center gap-3 text-slate-600 font-bold"><i
                                        class="fas fa-stopwatch text-emerald-500 text-xl w-6"></i> Durasi</div>
                                <div class="font-black text-slate-800">{{ $exam->duration_minutes }} Menit</div>
                            </div>
                        </div>

                        {{-- TOMBOL MATI UNTUK PREVIEW --}}
                        <button disabled
                            class="w-full bg-slate-200 text-slate-400 font-bold py-4 px-4 rounded-xl text-sm text-center cursor-not-allowed border-2 border-dashed border-slate-300">
                            Tombol Dinonaktifkan (Preview)
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- SCRIPT LATEX --}}
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll('.ql-formula').forEach(el => {
                const exp = el.getAttribute('data-value');
                if (exp) {
                    const decoded = exp.replace(/&gt;/g, '>').replace(/&lt;/g, '<').replace(/&amp;/g, '&');
                    try { window.katex.render(decoded, el, { throwOnError: false }); }
                    catch (e) { console.error("KaTeX Error:", e); }
                }
            });

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
</x-app-layout>