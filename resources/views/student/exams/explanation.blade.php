<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pembahasan Ujian') }}
        </h2>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/katex.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            <div
                class="bg-white rounded-3xl p-8 shadow-sm border border-slate-200 mb-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h1 class="text-2xl font-black text-slate-800">Pembahasan Ujian</h1>
                    <p class="text-slate-500 mt-1">{{ $examSession->exam->title }}</p>
                </div>
                <div
                    class="bg-emerald-50 border border-emerald-100 text-emerald-700 px-6 py-3 rounded-2xl text-center shrink-0">
                    <span class="block text-xs uppercase tracking-widest font-bold opacity-70">Nilai Akhir</span>
                    <span class="block text-3xl font-black">{{ $participant->score }}</span>
                </div>
            </div>

            <div class="space-y-8" id="explanation-container">
                @foreach($examSession->exam->questions as $index => $q)
                <div
                    class="bg-white rounded-3xl p-6 sm:p-10 shadow-sm border border-slate-200 relative overflow-hidden">

                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-indigo-500 to-purple-500"></div>

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

                    {{-- Konten Narasi Soal --}}
                    <div
                        class="prose prose-indigo max-w-none text-slate-700 mb-8 overflow-x-auto __se__katex_container">
                        {!! $q->content !!}
                    </div>

                    @php
                    // Ambil jawaban siswa untuk soal ini dari database/array controller
                    $studentAns = $studentAnswers[$q->id] ?? null;
                    @endphp

                    {{-- 1. PREVIEW: PILIHAN GANDA / PILIHAN KOMPLEKS / BENAR SALAH --}}
                    @if(in_array($q->type, ['single_choice', 'complex_choice', 'true_false']))
                    <div class="space-y-3 mb-8 pl-0 sm:pl-4 border-l-2 border-slate-100">
                        @foreach($q->options as $opt)
                        @php
                        $isStudentAnswer = is_array($studentAns) ? in_array($opt->id, $studentAns) : ($studentAns ==
                        $opt->id);

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
                            <div class="flex-1 prose prose-sm max-w-none text-slate-700 overflow-x-auto">{!!
                                $opt->option_text !!}</div>
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
                        <h5 class="text-xs font-black text-slate-400 uppercase tracking-wider mb-2">Pasangan Benar vs
                            Jawaban Siswa:</h5>
                        <div class="grid grid-cols-1 gap-3">
                            @foreach($q->matches ?? [] as $m)
                            @php
                            // Mengambil data target yang dipilih siswa untuk premis ini
                            // Di DB biasanya disimpan ter-serialize/array json: {"premis_id": "target_id"}
                            $studentTargetId = null;
                            if ($studentAns && (is_array($studentAns) || is_object($studentAns))) {
                            $studentAnsArray = is_string($studentAns) ? json_decode($studentAns, true) : (array)
                            $studentAns;
                            $studentTargetId = $studentAnsArray[$m->id] ?? null;
                            }

                            // Cari teks dari target yang dicocokkan siswa
                            $studentTargetText = '- Tidak Dijawab -';
                            $isMatchCorrect = false;

                            if($studentTargetId) {
                            if($studentTargetId == $m->id) {
                            $isMatchCorrect = true;
                            $studentTargetText = $m->target_text;
                            } else {
                            // Cari tahu teks salah apa yang dipilih siswa
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
                                        class="text-[10px] font-bold uppercase tracking-wider text-indigo-500 block mb-1">Kunci
                                        Benar:</span>
                                    <div class="text-sm font-bold text-slate-700">{!! strip_tags($m->premise_text) !!}
                                        <i class="fas fa-arrow-right mx-2 text-slate-400"></i> {!!
                                        strip_tags($m->target_text) !!}</div>
                                </div>
                                <div class="border-t md:border-t-0 md:border-l border-slate-200 pt-2 md:pt-0 md:pl-4">
                                    <span
                                        class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Pilihan
                                        Siswa:</span>
                                    <div
                                        class="text-sm font-bold {{ $isMatchCorrect ? 'text-emerald-600' : 'text-rose-600' }}">
                                        <i
                                            class="fas {{ $isMatchCorrect ? 'fa-check-circle text-emerald-500' : 'fa-times-circle text-rose-500' }} mr-1.5"></i>
                                        {!! strip_tags($studentTargetText) !!}
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- 3. PREVIEW: ESSAY / ISIAN --}}
                    @elseif($q->type === 'essay')
                    <div class="space-y-4 mb-8 pl-0 sm:pl-4 border-l-2 border-slate-100">
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200">
                            <span
                                class="text-[10px] bg-indigo-50 text-indigo-600 px-2.5 py-1 rounded-md font-black uppercase tracking-wider block w-max mb-2">Jawaban
                                Siswa</span>
                            <div class="text-slate-800 font-bold px-1 italic">
                                "{{ $studentAns ?? '- Kosong (Siswa tidak mengisi jawaban) -' }}"
                            </div>
                        </div>

                        <div class="bg-emerald-50/60 p-4 rounded-2xl border border-emerald-100">
                            <span
                                class="text-[10px] bg-emerald-100 text-emerald-700 px-2.5 py-1 rounded-md font-black uppercase tracking-wider block w-max mb-2">Kata
                                Kunci / Kunci Jawaban</span>
                            <div class="space-y-1">
                                @foreach($q->options as $opt)
                                <div class="text-emerald-800 font-black flex items-center gap-2 text-sm">
                                    <i class="fas fa-key text-emerald-500 text-xs"></i> {!! $opt->option_text !!}
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Pembahasan Detail --}}
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

            <div class="mt-10 text-center">
                <a href="{{ route('student.dashboard') }}"
                    class="inline-flex items-center gap-2 bg-slate-800 text-white px-8 py-3 rounded-xl font-bold hover:bg-slate-700 transition-colors">
                    <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                </a>
            </div>

        </div>
    </div>

    {{-- KUNCI UTAMA: SKRIP AUTO-RENDER KATEX UNTUK TEKS KODE MANUAL --}}
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/katex.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/contrib/auto-render.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // 1. Compile rumus manual ($...$ atau $$...$$)
            if (typeof renderMathInElement !== 'undefined') {
                renderMathInElement(document.getElementById('explanation-container'), {
                    delimiters: [
                        {left: '$$', right: '$$', display: true},
                        {left: '$', right: '$', display: false},
                        {left: '\\(', right: '\\)', display: false},
                        {left: '\\[', right: '\\]', display: true}
                    ],
                    throwOnError: false
                });
            }

            // 2. Compile rumus dari sistem Quill Editor / SunEditor (__se__katex)
            if (typeof katex !== 'undefined') {
                document.querySelectorAll('.__se__katex').forEach(el => {
                    let exp = el.getAttribute('data-exp');
                    if (exp) {
                        let decodedExp = exp.replace(/&gt;/g, '>').replace(/&lt;/g, '<').replace(/&amp;/g, '&').replace(/&quot;/g, '"').replace(/&#39;/g, "'").replace(/&nbsp;/g, ' ').replace(/\u00A0/g, ' ').replace(/<br\s*\/?>/gi, '\n');
                        try {
                            katex.render(decodedExp, el, { throwOnError: false, displayMode: el.style.display === 'block' || el.tagName === 'DIV' });
                        } catch (e) {
                            console.error(e);
                        }
                    }
                });
            }
        });
    </script>
</x-app-layout>
