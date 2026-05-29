<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pembahasan Ujian') }}
        </h2>
        {{-- CSS KaTeX --}}
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/katex.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            {{-- HEADER INFO UJIAN --}}
            <div
                class="bg-white rounded-3xl p-8 shadow-sm border border-slate-200 mb-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h1 class="text-2xl font-black text-slate-800">Pembahasan Ujian</h1>
                    <p class="text-slate-500 mt-1 font-bold">{{ $examSession->exam->title }}</p>
                </div>
                <div
                    class="bg-emerald-50 border border-emerald-100 text-emerald-700 px-6 py-3 rounded-2xl text-center shrink-0">
                    <span class="block text-xs uppercase tracking-widest font-bold opacity-70">Nilai Akhir</span>
                    <span class="block text-3xl font-black">{{ $participant->score }}</span>
                </div>
            </div>

            {{-- DAFTAR SOAL & PEMBAHASAN --}}
            <div class="space-y-8" id="explanation-container">
                @foreach($examSession->exam->questions as $index => $q)
                <div
                    class="bg-white rounded-3xl p-6 sm:p-10 shadow-sm border border-slate-200 relative overflow-hidden">

                    {{-- Garis gradasi atas --}}
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-indigo-500 to-purple-500"></div>

                    {{-- Header Soal --}}
                    <div class="flex items-center gap-3 mb-6">
                        <span class="bg-indigo-600 text-white px-4 py-1.5 rounded-xl font-black shadow-md text-sm">
                            NO. {{ $index + 1 }}
                        </span>
                        <span
                            class="text-[10px] font-black text-slate-400 uppercase tracking-widest bg-slate-100 border border-slate-200 px-3 py-1 rounded-full">
                            @if($q->type === 'single_choice') Pilihan Ganda
                            @elseif($q->type === 'complex_choice') PG Kompleks
                            @elseif($q->type === 'true_false') Benar / Salah
                            @elseif($q->type === 'matching') Menjodohkan
                            @elseif($q->type === 'essay') Isian Singkat
                            @else {{ str_replace('_', ' ', $q->type) }} @endif
                        </span>
                    </div>

                    {{-- Narasi Soal --}}
                    <div
                        class="prose prose-indigo max-w-none text-slate-700 mb-8 overflow-x-auto __se__katex_container">
                        {!! $q->content !!}
                    </div>

                    @php
                    // Ambil jawaban aktual siswa dari controller
                    $studentAns = $studentAnswers[$q->id] ?? null;
                    @endphp

                    {{-- 1. PREVIEW: PILIHAN GANDA / KOMPLEKS / BENAR SALAH --}}
                    @if(in_array($q->type, ['single_choice', 'complex_choice', 'true_false']))
                    <div class="space-y-3 mb-8 pl-0 sm:pl-4 border-l-2 border-slate-100">
                        @foreach($q->options as $opt)
                        @php
                        // Cek apakah opsi ini dipilih siswa
                        $isStudentAnswer = false;
                        if ($studentAns !== null) {
                        $isStudentAnswer = is_array($studentAns) ? in_array($opt->id, $studentAns) : ($studentAns ==
                        $opt->id);
                        }

                        // Logika Pewarnaan Opsi
                        $bgClass = 'bg-slate-50 border-slate-100 opacity-70';
                        $iconClass = 'fas fa-circle text-slate-300';

                        if ($opt->is_correct) {
                        $bgClass = 'bg-emerald-50 border-emerald-400';
                        $iconClass = 'fas fa-check-circle text-emerald-500 text-xl';
                        } elseif ($isStudentAnswer && !$opt->is_correct) {
                        $bgClass = 'bg-rose-50 border-rose-400';
                        $iconClass = 'fas fa-times-circle text-rose-500 text-xl';
                        }
                        @endphp

                        <div class="p-4 rounded-2xl border-2 transition-all flex items-start gap-4 {{ $bgClass }}">
                            <div class="mt-1 shrink-0"><i class="{{ $iconClass }}"></i></div>
                            <div
                                class="flex-1 prose prose-sm max-w-none text-slate-700 overflow-x-auto __se__katex_container">
                                {!! $opt->option_text !!}</div>

                            @if($isStudentAnswer)
                            <div class="shrink-0 mt-0.5">
                                <span
                                    class="text-[10px] bg-indigo-100 text-indigo-700 px-3 py-1.5 rounded-full font-black tracking-widest uppercase shadow-sm">Jawaban
                                    Kamu</span>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>

                    {{-- 2. PREVIEW: MENJODOHKAN (MATCHING) --}}
                    @elseif($q->type === 'matching')
                    <div class="space-y-3 mb-8 pl-0 sm:pl-4 border-l-2 border-slate-100">
                        <div class="grid grid-cols-1 gap-3">
                            @php
                            // Decode JSON string dari database (ex: {"1":1,"2":2}) menjadi Array PHP
                            $matchedAnswers = [];
                            if (!empty($studentAns)) {
                            $matchedAnswers = is_string($studentAns) ? json_decode($studentAns, true) : (array)
                            $studentAns;
                            }
                            @endphp

                            @foreach($q->matches ?? [] as $m)
                            @php
                            // Ambil ID target yang dipasangkan siswa untuk Premis ini
                            $studentTargetId = $matchedAnswers[$m->id] ?? null;

                            $studentTargetText = '- Tidak Dijawab -';
                            $isMatchCorrect = false;

                            if($studentTargetId) {
                            if($studentTargetId == $m->id) {
                            $isMatchCorrect = true;
                            $studentTargetText = $m->target_text;
                            } else {
                            // Cari tahu teks salah apa yang diklik oleh siswa
                            $fallbackTarget = collect($q->matches)->firstWhere('id', $studentTargetId);
                            $studentTargetText = $fallbackTarget ? $fallbackTarget->target_text : '- Pilihan Tidak Valid
                            -';
                            }
                            }
                            @endphp

                            <div
                                class="bg-slate-50 p-4 rounded-2xl border border-slate-200 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <span
                                        class="text-[10px] font-bold uppercase tracking-wider text-indigo-500 block mb-1">Pasangan
                                        Seharusnya:</span>
                                    <div class="text-sm font-bold text-slate-700 __se__katex_container">
                                        {!! strip_tags($m->premise_text) !!}
                                        <i class="fas fa-arrow-right mx-2 text-slate-400"></i>
                                        {!! strip_tags($m->target_text) !!}
                                    </div>
                                </div>
                                <div class="border-t md:border-t-0 md:border-l border-slate-200 pt-2 md:pt-0 md:pl-4">
                                    <span
                                        class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Jawaban
                                        Siswa:</span>
                                    <div
                                        class="text-sm font-bold __se__katex_container {{ $isMatchCorrect ? 'text-emerald-600' : 'text-rose-600' }}">
                                        <i
                                            class="fas {{ $isMatchCorrect ? 'fa-check-circle text-emerald-500' : 'fa-times-circle text-rose-500' }} mr-1.5"></i>
                                        {!! strip_tags($studentTargetText) !!}
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- 3. PREVIEW: ESSAY / ISIAN SINGKAT --}}
                    @elseif($q->type === 'essay')
                    <div class="space-y-4 mb-8 pl-0 sm:pl-4 border-l-2 border-slate-100">
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 shadow-sm relative mt-4">
                            <span
                                class="text-[10px] bg-indigo-100 text-indigo-700 px-3 py-1.5 rounded-full font-black uppercase tracking-widest absolute -top-3 left-4 border border-indigo-200 shadow-sm">
                                Jawaban Kamu
                            </span>
                            <div class="text-slate-800 font-bold px-1 mt-2 whitespace-pre-wrap __se__katex_container">{{
                                $studentAns ?: '- Kosong (Tidak dijawab) -' }}</div>
                        </div>

                        <div class="bg-emerald-50/60 p-4 rounded-2xl border border-emerald-100 relative mt-6">
                            <span
                                class="text-[10px] bg-emerald-100 text-emerald-700 px-3 py-1.5 rounded-full font-black uppercase tracking-widest absolute -top-3 left-4 border border-emerald-200 shadow-sm">
                                Kunci Jawaban Benar
                            </span>
                            <div class="space-y-1 mt-2">
                                @forelse($q->options as $opt)
                                <div
                                    class="text-emerald-800 font-black flex items-center gap-2 text-sm __se__katex_container">
                                    <i class="fas fa-key text-emerald-500 text-xs mt-0.5"></i> {!! $opt->option_text !!}
                                </div>
                                @empty
                                <div class="text-emerald-600 text-sm italic">Kata kunci belum diatur oleh guru.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- PEMBAHASAN GURU (JIKA ADA) --}}
                    @if($q->explanation)
                    <div
                        class="bg-gradient-to-br from-indigo-50 to-blue-50 border border-indigo-100 rounded-2xl p-6 relative mt-4">
                        <div class="flex items-center gap-2 mb-4">
                            <i class="fas fa-lightbulb text-amber-500 text-lg shadow-amber-200"></i>
                            <h4 class="font-black text-indigo-900 tracking-wide text-sm uppercase">Pembahasan Detail
                            </h4>
                        </div>
                        <div
                            class="prose prose-indigo max-w-none text-slate-700 text-sm md:text-base leading-relaxed overflow-x-auto __se__katex_container">
                            {!! $q->explanation !!}
                        </div>
                    </div>
                    @else
                    <div class="bg-slate-50 border border-slate-200 border-dashed rounded-2xl p-4 text-center mt-4">
                        <span class="text-slate-400 text-sm italic">Tidak ada pembahasan khusus untuk soal ini.</span>
                    </div>
                    @endif

                </div>
                @endforeach
            </div>

            {{-- TOMBOL KEMBALI --}}
            <div class="mt-10 text-center">
                <a href="{{ route('student.dashboard') }}"
                    class="inline-flex items-center gap-2 bg-slate-800 text-white px-8 py-3 rounded-xl font-bold hover:bg-slate-700 transition-colors shadow-lg">
                    <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                </a>
            </div>

        </div>
    </div>

    {{-- SCRIPT KATEX AUTO-RENDER (Untuk menterjemahkan rumus saat DOM Loaded) --}}
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/katex.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/contrib/auto-render.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const container = document.getElementById('explanation-container');
            if (!container) return;

            // 1. Render rumus manual ($...$ atau $$...$$) di seluruh container
            if (typeof renderMathInElement !== 'undefined') {
                renderMathInElement(container, {
                    delimiters: [
                        {left: '$$', right: '$$', display: true},
                        {left: '$', right: '$', display: false},
                        {left: '\\(', right: '\\)', display: false},
                        {left: '\\[', right: '\\]', display: true}
                    ],
                    throwOnError: false
                });
            }

            // 2. Render rumus buatan tombol Math Quill Editor (__se__katex)
            if (typeof katex !== 'undefined') {
                container.querySelectorAll('.__se__katex, .ql-formula').forEach(el => {
                    let exp = el.getAttribute('data-exp') || el.getAttribute('data-value');
                    if (exp) {
                        // Bersihkan string HTML Entities
                        let decodedExp = exp.replace(/&gt;/g, '>').replace(/&lt;/g, '<').replace(/&amp;/g, '&')
                                            .replace(/&quot;/g, '"').replace(/&#39;/g, "'").replace(/&nbsp;/g, ' ')
                                            .replace(/\u00A0/g, ' ').replace(/<br\s*\/?>/gi, '\n');
                        try {
                            katex.render(decodedExp, el, {
                                throwOnError: false,
                                displayMode: el.style.display === 'block' || el.tagName === 'DIV'
                            });
                        } catch (e) {
                            console.error("KaTeX Render Error:", e);
                        }
                    }
                });
            }
        });
    </script>
</x-app-layout>
