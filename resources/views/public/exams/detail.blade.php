<x-public-layout :pageTitle="$exam->title . ' - CBT Pro'"
    :metaDescription="$exam->meta_description ?? 'Simulasi ujian online di CBT Pro.'"
    :metaKeywords="$exam->meta_keywords ?? null" :metaImage="$exam->thumbnail ?? null">
    <div class="bg-slate-50 min-h-screen pb-20">

        {{-- HERO SECTION (Thumbnail & Judul) --}}
        <div class="w-full bg-slate-900 relative overflow-hidden pt-20 pb-24">
            @if($exam->thumbnail)
            <div class="absolute inset-0 bg-cover bg-center opacity-20 filter blur-sm"
                style="background-image: url('{{ asset('storage/' . $exam->thumbnail) }}')"></div>
            @else
            <div class="absolute inset-0 bg-gradient-to-r from-indigo-800 to-slate-900 opacity-90"></div>
            @endif

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
                    {{ $exam->description ?? 'Uji kemampuan dan ukur pemahaman Anda dengan simulasi ujian ini.' }}
                </p>
            </div>
        </div>

        {{-- KONTEN UTAMA & SIDEBAR --}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10 relative z-20">
            <div class="flex flex-col lg:flex-row gap-8">

                {{-- KIRI: Konten Artikel (Materi/Panduan) --}}
                <div class="lg:w-2/3">
                    <article
                        class="bg-white rounded-[2rem] shadow-sm border border-slate-200 p-8 sm:p-12 overflow-hidden">

                        <header>
                            @if($exam->thumbnail)
                            <img src="{{ asset('storage/' . $exam->thumbnail) }}"
                                alt="Banner informasi ujian {{ $exam->title }}" loading="lazy"
                                class="w-full h-auto rounded-2xl mb-8 sm:mb-10 object-cover shadow-sm border border-slate-100">
                            @endif

                            <h2 class="text-2xl font-black text-slate-800 mb-6 border-b border-slate-100 pb-4">
                                Informasi & Panduan Ujian
                            </h2>
                        </header>

                        <div class="prose prose-indigo prose-lg max-w-none text-slate-600">
                            @if($exam->content)
                            {!! $exam->content !!}
                            @else
                            <p>Tidak ada panduan khusus untuk ujian ini. Pastikan koneksi internet Anda stabil sebelum
                                memulai pengerjaan.</p>
                            <ul>
                                <li>Berdoalah sebelum mengerjakan.</li>
                                <li>Perhatikan batas waktu yang tersedia.</li>
                                <li>Jika menggunakan Sensor Layar, dilarang berpindah <em>tab</em> atau mengecilkan
                                    <em>browser</em>.
                                </li>
                            </ul>
                            @endif
                        </div>

                    </article>
                </div>

                {{-- KANAN: Sidebar Statistik & Tombol Pintar --}}
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

                        {{-- ============================================== --}}
                        {{-- LOGIKA TOMBOL PINTAR (SMART BUTTONS) --}}
                        {{-- ============================================== --}}
                        @php
                        $sessionState = session('public_exam_state_' . $exam->id);
                        $isCompleted = $sessionState && $sessionState['status'] === 'completed';
                        @endphp

                        @if($isCompleted)
                        <div class="space-y-3">
                            <div
                                class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm font-bold text-center mb-4 flex items-center justify-center gap-2">
                                <i class="fas fa-check-circle"></i> Anda sudah menyelesaikan ujian ini
                            </div>

                            <div class="flex gap-3">
                                <a href="{{ route('public.exams.result', $exam) }}"
                                    class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-3.5 px-2 rounded-xl text-sm text-center transition-all flex items-center justify-center gap-1.5 shadow-md">
                                    <i class="fas fa-chart-bar"></i> Hasil
                                </a>

                                <a href="{{ route('public.exams.ranking', $exam) }}"
                                    class="flex-1 bg-amber-500 hover:bg-amber-600 text-white font-bold py-3.5 px-2 rounded-xl text-sm text-center transition-all flex items-center justify-center gap-1.5 shadow-md">
                                    <i class="fas fa-trophy"></i> Ranking
                                </a>
                            </div>

                            <form action="{{ route('public.exams.restart', $exam) }}" method="POST" class="mt-3">
                                @csrf
                                <button type="submit"
                                    class="w-full bg-slate-900 hover:bg-indigo-600 text-white font-bold py-3.5 px-4 rounded-xl text-sm text-center transition-all flex items-center justify-center gap-2 shadow-md">
                                    <i class="fas fa-redo"></i> Ulangi Ujian
                                </button>
                            </form>
                        </div>
                        @else
                        <a href="{{ route('public.exams.verify', $exam) }}"
                            class="w-full bg-slate-900 hover:bg-indigo-600 text-white font-bold py-4 px-4 rounded-xl text-sm text-center transition-all shadow-md hover:shadow-xl hover:-translate-y-0.5 flex items-center justify-center gap-2 group">
                            Mulai Kerjakan <i
                                class="fas fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
                        </a>
                        @endif

                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // 1. Render elemen formula bawaan Quill (span dengan class ql-formula dan atribut data-value)
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

            // 2. Auto-render jika Admin mengetik kode LaTeX secara manual di teks (menggunakan $$ atau $)
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