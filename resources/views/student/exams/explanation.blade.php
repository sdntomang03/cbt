<x-app-layout>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/contrib/auto-render.min.js"></script>

    <style>
        header,
        nav {
            display: none !important;
        }

        body {
            background-color: #f1f5f9;
            overflow: hidden;
            font-family: 'Nunito', sans-serif;
            user-select: none;
        }

        .no-select {
            user-select: none;
            -webkit-user-select: none;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
            border: 2px solid #f1f5f9;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* ── Lightbox ── */
        #lightbox {
            position: fixed;
            inset: 0;
            z-index: 99999;
            background: rgba(0, 0, 0, 0.92);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            transition: opacity .2s;
            touch-action: none;
        }

        #lightbox.open {
            opacity: 1;
            pointer-events: all;
        }

        #lightbox-img-wrap {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            overflow: hidden;
            cursor: grab;
        }

        #lightbox-img-wrap.grabbing {
            cursor: grabbing;
        }

        #lightbox-img {
            max-width: 90vw;
            max-height: 90vh;
            border-radius: 1rem;
            user-select: none;
            pointer-events: none;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.6);
            transform-origin: center center;
            transition: transform .05s;
            will-change: transform;
        }

        #lightbox-close {
            position: absolute;
            top: 1.2rem;
            right: 1.5rem;
            z-index: 10;
            background: rgba(255, 255, 255, .15);
            border: none;
            color: white;
            font-size: 1.5rem;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background .15s;
        }

        #lightbox-close:hover {
            background: rgba(255, 255, 255, .3);
        }

        #lightbox-hint {
            position: absolute;
            bottom: 1.5rem;
            left: 50%;
            transform: translateX(-50%);
            color: rgba(255, 255, 255, .4);
            font-size: .75rem;
            letter-spacing: .05em;
            white-space: nowrap;
            pointer-events: none;
        }

        #lightbox-zoom-bar {
            position: absolute;
            bottom: 3.5rem;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: .5rem;
        }

        #lightbox-zoom-bar button {
            background: rgba(255, 255, 255, .15);
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            font-size: 1.2rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #lightbox-zoom-bar button:hover {
            background: rgba(255, 255, 255, .3);
        }

        #explanation-viewport img {
            cursor: zoom-in;
            border-radius: .5rem;
            transition: opacity .15s, box-shadow .15s;
            max-width: 100%;
        }

        #explanation-viewport img:hover {
            opacity: .9;
            box-shadow: 0 0 0 3px #4f46e5;
        }
    </style>

    <div class="fixed inset-0 flex flex-col h-screen bg-[#f1f5f9]">

        {{-- HEADER --}}
        <div
            class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-8 z-[100] shadow-sm select-none shrink-0">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                    <i class="fas fa-book-open"></i>
                </div>
                <div>
                    <h1 class="font-black text-slate-800 text-sm tracking-widest uppercase leading-none">Pembahasan
                        Ujian</h1>
                    <p class="font-bold text-slate-500 text-xs mt-1 truncate max-w-[200px] md:max-w-md">{{
                        $examSession->exam->title }}</p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div
                    class="bg-emerald-50 border border-emerald-100 text-emerald-700 px-4 py-1.5 rounded-xl text-center hidden sm:block">
                    <span class="text-[10px] uppercase tracking-widest font-black opacity-70 mr-2">Nilai Akhir:</span>
                    <span class="text-lg font-black">{{ $participant->score }}</span>
                </div>

                <a href="{{ route('student.dashboard') }}"
                    class="relative bg-slate-900 hover:bg-slate-800 text-white px-5 py-2 rounded-xl font-bold transition shadow-lg flex items-center gap-2 text-sm">
                    <i class="fas fa-home text-xs"></i>
                    <span class="hidden sm:inline">Dashboard</span>
                </a>
            </div>
        </div>

        {{-- BODY SCROLLABLE --}}
        <div id="explanation-viewport" class="flex-1 overflow-y-auto custom-scrollbar p-4 sm:p-10 pb-28">
            <div class="max-w-4xl mx-auto space-y-8">

                {{-- Banner Nilai Mobile --}}
                <div
                    class="sm:hidden bg-emerald-50 border border-emerald-200 text-emerald-700 p-4 rounded-[2rem] flex items-center justify-between shadow-sm">
                    <span class="text-xs uppercase tracking-widest font-black opacity-70">Nilai Akhir Kamu</span>
                    <span class="text-3xl font-black">{{ $participant->score }}</span>
                </div>

                @foreach($examSession->exam->questions as $index => $q)
                <div
                    class="bg-white rounded-[2rem] p-6 sm:p-10 shadow-sm border border-slate-100 relative overflow-hidden">
                    <div
                        class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500">
                    </div>

                    {{-- Header Soal --}}
                    <div class="flex flex-wrap items-center gap-3 mb-6">
                        <span class="bg-indigo-600 text-white px-5 py-2 rounded-2xl font-black shadow-lg text-sm">
                            NO. <span class="text-lg">{{ $index + 1 }}</span>
                        </span>
                        <span
                            class="text-[10px] font-black text-slate-400 uppercase tracking-widest bg-slate-100 border border-slate-200 px-3 py-1.5 rounded-full">
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
                        class="prose prose-indigo prose-lg text-lg max-w-none text-slate-700 leading-relaxed mb-8 no-select __se__katex_container">
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
                        $isStudentAnswer = false;
                        if ($studentAns !== null) {
                        $isStudentAnswer = is_array($studentAns) ? in_array($opt->id, $studentAns) : ($studentAns ==
                        $opt->id);
                        }

                        $bgClass = 'bg-slate-50 border-slate-100 opacity-70';
                        $iconClass = 'fas fa-circle text-slate-300';

                        if ($opt->is_correct) {
                        $bgClass = 'bg-emerald-50 border-emerald-400 shadow-sm';
                        $iconClass = 'fas fa-check-circle text-emerald-500 text-xl';
                        } elseif ($isStudentAnswer && !$opt->is_correct) {
                        $bgClass = 'bg-rose-50 border-rose-400 shadow-sm';
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
                            $matchedAnswers = [];
                            if (!empty($studentAns)) {
                            $matchedAnswers = is_string($studentAns) ? json_decode($studentAns, true) : (array)
                            $studentAns;
                            }
                            @endphp

                            @foreach($q->matches ?? [] as $m)
                            @php
                            $studentTargetId = $matchedAnswers[$m->id] ?? null;
                            $studentTargetText = '- Tidak Dijawab -';
                            $isMatchCorrect = false;

                            if($studentTargetId) {
                            if($studentTargetId == $m->id) {
                            $isMatchCorrect = true;
                            $studentTargetText = $m->target_text;
                            } else {
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
                                        Benar:</span>
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
                    <div class="space-y-5 mb-8 pl-0 sm:pl-4 border-l-2 border-slate-100">
                        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative mt-4">
                            <span
                                class="text-[10px] bg-indigo-100 text-indigo-700 px-3 py-1.5 rounded-full font-black uppercase tracking-widest absolute -top-3 left-4 border border-indigo-200 shadow-sm">
                                Jawaban Kamu
                            </span>
                            <div
                                class="text-slate-800 font-bold px-1 mt-2 whitespace-pre-wrap __se__katex_container text-base">
                                {{ $studentAns ?: '- Kosong (Tidak dijawab) -' }}</div>
                        </div>

                        <div class="bg-emerald-50/60 p-5 rounded-2xl border border-emerald-100 relative mt-6">
                            <span
                                class="text-[10px] bg-emerald-100 text-emerald-700 px-3 py-1.5 rounded-full font-black uppercase tracking-widest absolute -top-3 left-4 border border-emerald-200 shadow-sm">
                                Kunci Jawaban Benar
                            </span>
                            <div class="space-y-1.5 mt-2">
                                @forelse($q->options as $opt)
                                <div
                                    class="text-emerald-800 font-black flex items-center gap-2 text-sm __se__katex_container">
                                    <i class="fas fa-check text-emerald-500 text-xs mt-0.5"></i> {!! $opt->option_text
                                    !!}
                                </div>
                                @empty
                                <div class="text-emerald-600 text-sm italic">Kunci jawaban belum diatur oleh guru.</div>
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
                            <h4 class="font-black text-indigo-900 tracking-wide text-sm uppercase">Penjelasan &
                                Pembahasan</h4>
                        </div>
                        <div
                            class="prose prose-indigo max-w-none text-slate-700 text-sm md:text-base leading-relaxed overflow-x-auto __se__katex_container">
                            {!! $q->explanation !!}
                        </div>
                    </div>
                    @endif

                </div>
                @endforeach

                {{-- Footer Area (Ruang kosong di bawah agar nyaman di-scroll) --}}
                <div class="h-10 text-center text-slate-400 text-xs font-bold uppercase tracking-widest">
                    --- Akhir dari Pembahasan ---
                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPTS UTILITIES (KATEX & LIGHTBOX) --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // --- 1. RENDER KATEX ---
            const container = document.getElementById('explanation-viewport');
            if (!container) return;

            if (typeof window.katex !== 'undefined') {
                container.querySelectorAll('.__se__katex, .ql-formula').forEach(el => {
                    let exp = el.getAttribute('data-exp') || el.getAttribute('data-value');
                    if (exp) {
                        let decodedExp = exp.replace(/&gt;/g, '>').replace(/&lt;/g, '<').replace(/&amp;/g, '&')
                                            .replace(/&quot;/g, '"').replace(/&#39;/g, "'").replace(/&nbsp;/g, ' ')
                                            .replace(/\u00A0/g, ' ').replace(/<br\s*\/?>/gi, '\n');
                        try { window.katex.render(decodedExp, el, { throwOnError: false, displayMode: el.style.display === 'block' || el.tagName === 'DIV' }); } catch(e) {}
                    }
                });
            }

            if (typeof renderMathInElement === 'function') {
                renderMathInElement(container, {
                    delimiters: [
                        {left:'$$',right:'$$',display:true}, {left:'$',right:'$',display:false},
                        {left:'\\(',right:'\\)',display:false}, {left:'\\[',right:'\\]',display:true}
                    ], throwOnError: false
                });
            }
        });

        // --- 2. LIGHTBOX ZOOM ---
        (function () {
            let scale = 1, minScale = 0.5, maxScale = 5, tx = 0, ty = 0, isDragging = false, lx = 0, ly = 0, lastPinch = null;
            const gi = () => document.getElementById('lightbox-img');
            const gw = () => document.getElementById('lightbox-img-wrap');
            const apply = () => { const img = gi(); if (img) img.style.transform = `translate(${tx}px,${ty}px) scale(${scale})`; };
            const reset = () => { scale=1; tx=0; ty=0; apply(); };

            window.openLightbox = src => { const lb=document.getElementById('lightbox'),img=gi(); if(!lb||!img) return; reset(); img.src=src; lb.classList.add('open'); document.addEventListener('keydown',lbKey); };
            window.hideLightbox = () => { const lb=document.getElementById('lightbox'),img=gi(); if(!lb||!img) return; lb.classList.remove('open'); img.src=''; reset(); document.removeEventListener('keydown',lbKey); };
            window.closeLightbox = e => { if(e.target===document.getElementById('lightbox')) hideLightbox(); };
            window.zoomLightbox = d => { if(d===0){reset();return;} scale=Math.min(maxScale,Math.max(minScale,scale+d)); apply(); };
            function lbKey(e) { if(e.key==='Escape') hideLightbox(); if(e.key==='+'||e.key==='=') zoomLightbox(0.3); if(e.key==='-') zoomLightbox(-0.3); if(e.key==='0') zoomLightbox(0); }

            document.addEventListener('DOMContentLoaded', () => {
                const wrap=gw(), img=gi(), vp=document.getElementById('explanation-viewport');
                if(vp) vp.addEventListener('click', e => { if(e.target.tagName==='IMG') openLightbox(e.target.src); });
                if(!wrap||!img) return;
                wrap.addEventListener('wheel',e=>{ e.preventDefault(); scale=Math.min(maxScale,Math.max(minScale,scale+(e.deltaY<0?.2:-.2))); apply(); },{passive:false});
                wrap.addEventListener('mousedown',e=>{ if(e.button!==0) return; isDragging=true; lx=e.clientX; ly=e.clientY; wrap.classList.add('grabbing'); });
                document.addEventListener('mousemove',e=>{ if(!isDragging) return; tx+=e.clientX-lx; ty+=e.clientY-ly; lx=e.clientX; ly=e.clientY; apply(); });
                document.addEventListener('mouseup',()=>{ isDragging=false; wrap.classList.remove('grabbing'); });
                wrap.addEventListener('touchstart',e=>{ if(e.touches.length===1){isDragging=true;lx=e.touches[0].clientX;ly=e.touches[0].clientY;} else if(e.touches.length===2){isDragging=false;lastPinch=dist(e.touches);} },{passive:true});
                wrap.addEventListener('touchmove',e=>{ e.preventDefault(); if(e.touches.length===1&&isDragging){tx+=e.touches[0].clientX-lx;ty+=e.touches[0].clientY-ly;lx=e.touches[0].clientX;ly=e.touches[0].clientY;apply();} else if(e.touches.length===2){const d2=dist(e.touches);if(lastPinch){scale=Math.min(maxScale,Math.max(minScale,scale*(d2/lastPinch)));apply();}lastPinch=d2;} },{passive:false});
                wrap.addEventListener('touchend',e=>{ if(e.touches.length<2) lastPinch=null; if(e.touches.length===0) isDragging=false; },{passive:true});
                let lastTap=0; wrap.addEventListener('touchend',e=>{ const n=Date.now(); if(n-lastTap<300) reset(); lastTap=n; },{passive:true});
            });
            function dist(t) { const dx=t[0].clientX-t[1].clientX,dy=t[0].clientY-t[1].clientY; return Math.sqrt(dx*dx+dy*dy); }
        })();
    </script>

    {{-- HTML LIGHTBOX --}}
    <div id="lightbox" onclick="closeLightbox(event)">
        <button id="lightbox-close" onclick="hideLightbox()"><i class="fas fa-times"></i></button>
        <div id="lightbox-img-wrap"><img id="lightbox-img" src="" alt="Zoomed Image"></div>
        <div id="lightbox-zoom-bar">
            <button onclick="zoomLightbox(-0.5)"><i class="fas fa-search-minus"></i></button>
            <button onclick="zoomLightbox(0)"><i class="fas fa-expand"></i></button>
            <button onclick="zoomLightbox(0.5)"><i class="fas fa-search-plus"></i></button>
        </div>
        <span id="lightbox-hint">Pinch/scroll zoom · Geser · Esc untuk tutup</span>
    </div>
</x-app-layout>
