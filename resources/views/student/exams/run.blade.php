<x-cbt-layout>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/contrib/auto-render.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/leader-line-new@1.1.9/leader-line.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .match-container {
            display: flex;
            justify-content: space-between;
            gap: 80px;
            position: relative;
            padding: 20px;
            min-height: 400px;
        }

        .match-column {
            display: flex;
            flex-direction: column;
            gap: 15px;
            width: 45%;
        }

        .match-item {
            padding: 1.2rem;
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 1rem;
            cursor: pointer;
            font-weight: 700;
            transition: all 0.2s;
            position: relative;
            z-index: 10;
        }

        .match-item:hover {
            border-color: #cbd5e1;
        }

        .match-item.selected {
            border-color: #4f46e5;
            background: #eef2ff;
            box-shadow: 0 0 15px rgba(79, 70, 229, 0.2);
        }

        .match-dot {
            width: 12px;
            height: 12px;
            background: #94a3b8;
            border-radius: 50%;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            transition: background 0.2s;
        }

        .match-item.selected .match-dot {
            background: #4f46e5;
        }

        .dot-right {
            right: -6px;
        }

        .dot-left {
            left: -6px;
        }

        .overlay-base {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .swal2-container {
            z-index: 20000 !important;
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

        #question-viewport img {
            cursor: zoom-in;
            border-radius: .5rem;
            transition: opacity .15s, box-shadow .15s;
            max-width: 100%;
        }

        #question-viewport img:hover {
            opacity: .9;
            box-shadow: 0 0 0 3px #4f46e5;
        }

        /* ── Mobile Nav Toggle ── */
        #mobile-nav-overlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 150;
            background: rgba(0, 0, 0, 0.5);
        }

        #mobile-nav-overlay.open {
            display: block;
        }

        #mobile-nav-panel {
            position: fixed;
            right: 0;
            top: 0;
            bottom: 0;
            z-index: 151;
            width: 300px;
            max-width: 85vw;
            background: white;
            display: flex;
            flex-direction: column;
            transform: translateX(100%);
            transition: transform .3s cubic-bezier(.4, 0, .2, 1);
            box-shadow: -10px 0 40px rgba(0, 0, 0, 0.15);
        }

        #mobile-nav-panel.open {
            transform: translateX(0);
        }

        @media (min-width: 1024px) {
            #mobile-nav-btn {
                display: none !important;
            }

            #mobile-nav-overlay,
            #mobile-nav-panel {
                display: none !important;
            }
        }

        [x-cloak] {
            display: none !important;
        }
    </style>

    {{-- ======================================================================= --}}
    {{-- LOGIKA SERVER SIDE: BLOKIR JIKA DIKUNCI / SELESAI --}}
    {{-- ======================================================================= --}}

    @if($pivot->is_locked)
    <div
        class="fixed inset-0 bg-slate-900 z-[10000] p-10 text-white flex flex-col items-center justify-center text-center">
        <div class="bg-rose-600 w-24 h-24 rounded-full flex items-center justify-center mb-6 shadow-2xl">
            <i class="fas fa-lock text-4xl"></i>
        </div>
        <h1 class="text-4xl font-black mb-4 uppercase tracking-wider">UJIAN TERKUNCI</h1>
        <p class="text-slate-300 max-w-xl text-lg mb-10 leading-relaxed">
            Anda telah melanggar aturan keamanan sebanyak <strong>3 kali</strong>. <br>
            Sistem telah mengunci akses ujian Anda secara permanen.
        </p>
        <div class="bg-white/10 px-6 py-4 rounded-xl border border-white/20 text-sm mb-8">
            Silakan lapor ke <strong>Pengawas Ujian</strong> untuk membuka kunci.
        </div>
        <div class="flex gap-4">
            <button onclick="location.reload()"
                class="px-8 py-3.5 rounded-xl font-bold bg-emerald-500 text-white hover:bg-emerald-600 shadow-lg transition hover:scale-105">
                <i class="fas fa-sync-alt mr-2"></i> Refresh Status
            </button>
            <a href="{{ route('student.dashboard') }}"
                class="px-8 py-3.5 rounded-xl font-bold bg-slate-700 text-white hover:bg-slate-600 shadow-lg transition">
                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>

    @elseif($pivot->status === 'completed')
    <div
        class="fixed inset-0 bg-slate-900 z-[10000] p-10 text-white flex flex-col items-center justify-center text-center">
        <div class="bg-indigo-600 w-24 h-24 rounded-full flex items-center justify-center mb-6 shadow-2xl">
            <i class="fas fa-check-double text-4xl"></i>
        </div>
        <h1 class="text-4xl font-black mb-4 uppercase tracking-wider">UJIAN TELAH BERAKHIR</h1>
        <p class="text-slate-300 max-w-xl text-lg mb-10 leading-relaxed">
            Sesi ujian ini telah diselesaikan (waktu habis atau dihentikan secara paksa oleh Pengawas). Anda tidak dapat
            lagi melanjutkan ujian atau mengubah jawaban.
        </p>
        <div class="flex gap-4">
            <a href="{{ route('student.dashboard') }}"
                class="px-8 py-3.5 rounded-xl font-bold bg-emerald-500 text-white hover:bg-emerald-600 shadow-lg transition hover:scale-105">
                <i class="fas fa-home mr-2"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>

    @else
    {{-- TAMPILAN NORMAL UJIAN --}}
    <script>
        window.initialExamState = {
            count: {{ (int) $pivot->violation_count }},
            isLocked: false,
            config: @json($config)
        };
    </script>

    <div x-data x-show="$store.examState.showWarning" x-cloak x-transition.opacity class="overlay-base bg-black/90">
        <div
            class="bg-white text-slate-800 p-8 rounded-[2rem] max-w-lg w-full mx-4 shadow-2xl relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-2 bg-rose-500"></div>
            <i class="fas fa-exclamation-triangle text-6xl text-rose-500 mb-4 animate-pulse"></i>
            <h2 class="text-3xl font-black mb-1 uppercase text-slate-800">Pelanggaran Terdeteksi!</h2>
            <div
                class="inline-block bg-rose-100 text-rose-600 px-4 py-1 rounded-full font-bold text-sm mb-4 border border-rose-200">
                Peringatan ke-<span x-text="$store.examState.violationCount"></span> dari <span
                    x-text="$store.examState.maxViolations"></span>
            </div>
            <p class="text-slate-500 mb-8 leading-relaxed">
                Sistem mendeteksi Anda keluar dari mode layar penuh.<br>
                <strong class="text-rose-600">Jika mencapai 3x peringatan, ujian akan otomatis DIKUNCI.</strong>
            </p>
            <button @click="$store.examState.resumeExam()"
                class="w-full bg-slate-900 hover:bg-black text-white py-4 rounded-xl font-bold transition shadow-lg flex items-center justify-center gap-2">
                <span>SAYA MENGERTI & KEMBALI</span>
            </button>
        </div>
    </div>

    <div x-data x-show="$store.examState.isLocked" x-cloak class="overlay-base bg-slate-900 z-[10000] p-10 text-white">
        <div class="bg-rose-600 w-24 h-24 rounded-full flex items-center justify-center mb-6 shadow-2xl animate-pulse">
            <i class="fas fa-lock text-4xl"></i>
        </div>
        <h1 class="text-4xl font-black mb-4 uppercase tracking-wider">UJIAN TERKUNCI</h1>
        <p class="text-slate-300 max-w-xl text-lg mb-10 leading-relaxed">
            Anda baru saja melanggar aturan keamanan ke-3 kalinya. <br>
            Akses telah ditutup.
        </p>
        <button onclick="location.reload()"
            class="px-8 py-3.5 rounded-xl font-bold bg-emerald-500 text-white hover:bg-emerald-600 shadow-lg transition hover:scale-105">
            <i class="fas fa-sync-alt mr-2"></i> Refresh Halaman
        </button>
    </div>

    <div x-data x-show="!$store.examState.started && !$store.examState.isLocked" x-cloak
        class="overlay-base bg-slate-900 z-[200] p-10 text-white">
        <i class="fas text-6xl mb-6"
            :class="$store.examState.enableViolation ? 'fa-shield-alt text-emerald-400' : 'fa-laptop-house text-indigo-400'"></i>
        <h1 class="text-3xl font-black mb-2"
            x-text="$store.examState.enableViolation ? 'Mode Ujian Aman' : 'Mode Ujian Santai'"></h1>
        <p class="text-slate-400 mb-8 max-w-lg"
            x-text="$store.examState.enableViolation ? 'Ujian ini mewajibkan mode Layar Penuh. Dilarang berpindah tab.' : 'Ujian ini tidak mendeteksi perpindahan tab. Selamat mengerjakan.'">
        </p>
        <button @click="$store.examState.startSecureExam()"
            class="text-white px-10 py-4 rounded-2xl font-black text-lg shadow-xl transition transform hover:scale-105"
            :class="$store.examState.enableViolation ? 'bg-emerald-500 hover:bg-emerald-600 shadow-emerald-500/20' : 'bg-indigo-500 hover:bg-indigo-600 shadow-indigo-500/20'">
            MULAI UJIAN SEKARANG
        </button>
    </div>

    {{-- ================================================================= --}}
    {{-- APLIKASI UTAMA (AJAX VERSION) --}}
    {{-- ================================================================= --}}
    <div class="fixed inset-0 flex flex-col h-screen bg-[#f1f5f9]" x-data='examRunner(
    @json($questionIds),
    {{ $timeLeftSeconds }},
    @json($existingAnswers),
    @json($flags ?? []),
    {{ auth()->id() }},
    @json($config),
    {{ $exam->id }}
)'>

        <div
            class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-8 z-[100] shadow-sm select-none relative">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div>
                    <h1 class="hidden lg:block font-black text-slate-800 text-sm tracking-widest uppercase">{{
                        $exam->title }}</h1>
                    <p class="font-bold text-slate-700 text-sm leading-none mt-0.5">{{ auth()->user()->name }}</p>
                </div>
            </div>
            <div class="bg-slate-900 text-white px-3 py-1.5 lg:px-6 lg:py-2 rounded-xl font-mono font-bold text-sm lg:text-xl flex items-center gap-1.5 lg:gap-3 shadow-lg"
                :class="timeLeft < 300 ? 'bg-rose-600 animate-pulse' : ''">
                <i class="fas fa-clock text-xs lg:text-sm opacity-50"></i>
                <span x-text="formatTime(timeLeft)"></span>
            </div>
            <button type="button" onclick="toggleMobileNav()" id="mobile-nav-btn"
                class="lg:hidden relative bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-2 rounded-xl font-bold transition flex items-center gap-2 border border-slate-200">
                <i class="fas fa-th text-sm"></i>
                <span class="text-xs font-black">Daftar Soal</span>
            </button>
            <button type="button" @click.prevent="finishExam()"
                class="hidden lg:flex relative z-[101] bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-2 rounded-xl font-bold transition shadow-lg items-center gap-2 cursor-pointer">
                <span x-show="!isSubmitting">Selesai</span>
                <span x-show="isSubmitting"><i class="fas fa-spinner fa-spin"></i></span>
                <i x-show="!isSubmitting" class="fas fa-check-circle"></i>
            </button>
        </div>

        <div class="flex-1 flex overflow-hidden">
            <div id="question-viewport" class="flex-1 overflow-y-auto custom-scrollbar p-6 sm:p-12 pb-32"
                @scroll="repositionLines()">

                <div class="max-w-4xl mx-auto min-h-[400px] relative">

                    {{-- LOADING OVERLAY AJAX --}}
                    <div x-show="isLoading"
                        class="absolute inset-0 z-50 flex items-center justify-center bg-[#f1f5f9]/80 backdrop-blur-sm rounded-[2.5rem]">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-spinner fa-spin text-4xl text-indigo-600 mb-3"></i>
                            <span class="font-bold text-slate-500">Memuat soal...</span>
                        </div>
                    </div>

                    {{-- SINGLE QUESTION RENDER --}}
                    <template x-if="q !== null">
                        <div x-show="!isLoading" x-transition:enter="transition duration-300 ease-out"
                            x-transition:enter-start="opacity-0 translate-y-4"
                            x-transition:enter-end="opacity-100 translate-y-0">

                            <div class="flex flex-wrap justify-between items-center gap-4 mb-8">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="bg-indigo-600 text-white px-6 py-2.5 rounded-2xl font-black shadow-lg">NO.
                                        <span x-text="currentIndex + 1" class="text-xl"></span>
                                    </span>
                                    <span
                                        class="text-[10px] font-black text-slate-400 uppercase tracking-widest bg-slate-100 border border-slate-200 px-4 py-1.5 rounded-full"
                                        x-text="formatType(q.type)"></span>
                                </div>

                                <div
                                    class="flex items-center bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden shrink-0">
                                    <button @click="zoomLevel = Math.max(0.8, zoomLevel - 0.1)"
                                        class="px-4 py-2 hover:bg-indigo-50 text-slate-600 hover:text-indigo-600 font-black text-sm border-r border-slate-200 transition-colors">A-</button>
                                    <button @click="zoomLevel = 1"
                                        class="px-4 py-2 hover:bg-indigo-50 text-slate-600 hover:text-indigo-600 font-black text-sm border-r border-slate-200 transition-colors">A</button>
                                    <button @click="zoomLevel = Math.min(1.5, zoomLevel + 0.1)"
                                        class="px-4 py-2 hover:bg-indigo-50 text-slate-600 hover:text-indigo-600 font-black text-sm transition-colors">A+</button>
                                </div>

                                <button @click="toggleFlag(q.id)"
                                    class="px-5 py-2.5 rounded-xl font-bold text-sm border-2 transition-all flex items-center gap-2"
                                    :class="flags.includes(q.id) ? 'bg-amber-400 text-white border-amber-400' : 'bg-white text-slate-400 border-slate-200'">
                                    <i class="fas fa-bookmark"></i> <span
                                        x-text="flags.includes(q.id) ? 'Ditandai' : 'Ragu-ragu'"></span>
                                </button>
                            </div>

                            <div :style="`zoom: ${zoomLevel};`" class="origin-top">
                                <div
                                    class="bg-white rounded-[2.5rem] p-10 shadow-sm border border-slate-100 mb-8 relative overflow-hidden">
                                    <div
                                        class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500">
                                    </div>
                                    <div class="prose prose-indigo prose-lg text-lg max-w-none text-slate-700 leading-relaxed no-select __se__katex_container"
                                        x-html="q.content"></div>
                                </div>

                                <div class="space-y-4">
                                    <template x-if="q.type === 'single_choice'">
                                        <x-exam.single-choice :q="'q'" />
                                    </template>
                                    <template x-if="['multiple_choice', 'complex_choice'].includes(q.type)">
                                        <x-exam.complex-choice :q="'q'" />
                                    </template>
                                    <template x-if="['true_false', 'true_false_multi'].includes(q.type)">
                                        <x-exam.true-false :q="'q'" />
                                    </template>

                                    <template x-if="q.type === 'matching'">
                                        <div class="match-container">
                                            <div class="match-column">
                                                <template x-for="m in q.matches" :key="'p-'+m.id">
                                                    <div class="match-item premise" :id="'premise-' + m.id"
                                                        @click="clickMatch(q.id, m.id, 'premise')"
                                                        :class="matchState.activePremise === m.id ? 'selected' : ''">
                                                        <span x-html="m.premise_text"></span>
                                                        <div class="match-dot dot-right"></div>
                                                    </div>
                                                </template>
                                            </div>
                                            <div class="match-column">
                                                <template x-for="target in shuffledTargets[q.id]" :key="'t-'+target.id">
                                                    <div class="match-item target" :id="'target-' + target.id"
                                                        @click="clickMatch(q.id, target.id, 'target')"
                                                        :class="matchState.activeTarget === target.id ? 'selected' : ''">
                                                        <span x-html="target.text"></span>
                                                        <div class="match-dot dot-left"></div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </template>

                                    <template x-if="q.type === 'essay'">
                                        <x-exam.essay :q="'q'" />
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- SIDEBAR DAFTAR SOAL --}}
            <div
                class="w-72 bg-white border-l border-slate-200 hidden lg:flex flex-col z-50 shadow-[-5px_0_30px_rgba(0,0,0,0.02)]">
                <div class="p-5 bg-white border-b border-slate-100">
                    <h3 class="font-black text-slate-800 text-base">Navigasi Soal</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Terjawab:
                        <span class="font-bold text-indigo-600"
                            x-text="questionIds.filter(id => hasAnswer(id)).length + ' / ' + questionIds.length"></span>
                    </p>
                </div>

                <div class="flex-1 overflow-y-auto p-4 custom-scrollbar bg-slate-50/50">
                    <div class="grid grid-cols-5 gap-2">
                        <template x-for="(qId, index) in questionIds" :key="qId">
                            <button @click="gotoQuestion(index)"
                                class="aspect-square rounded-lg font-black text-xs transition-all border-2 flex items-center justify-center relative"
                                :class="{
                                    'bg-indigo-600 text-white border-indigo-600 scale-110 z-10 shadow-md': currentIndex === index,
                                    'bg-amber-100 text-amber-600 border-amber-400': flags.includes(qId) && currentIndex !== index,
                                    'bg-blue-500 text-white border-blue-500': hasAnswer(qId) && !flags.includes(qId) && currentIndex !== index,
                                    'bg-white text-slate-400 border-slate-200': !hasAnswer(qId) && !flags.includes(qId) && currentIndex !== index
                                }">
                                <span x-text="index + 1"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <div class="p-4 border-t border-slate-100 bg-white space-y-2">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Keterangan</p>
                    <div class="flex items-center gap-2 text-xs text-slate-600">
                        <div class="w-5 h-5 rounded-md bg-indigo-600 flex-shrink-0"></div><span>Sedang dilihat</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-slate-600">
                        <div class="w-5 h-5 rounded-md bg-blue-500 flex-shrink-0"></div><span>Sudah dijawab</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-slate-600">
                        <div class="w-5 h-5 rounded-md bg-amber-100 border-2 border-amber-400 flex-shrink-0"></div>
                        <span>Ragu-ragu</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-slate-600">
                        <div class="w-5 h-5 rounded-md bg-white border-2 border-slate-200 flex-shrink-0"></div>
                        <span>Belum dijawab</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- BOTTOM NAV --}}
        <div
            class="h-16 bg-white/80 backdrop-blur border-t border-slate-100 flex items-center justify-between px-4 sm:px-8 z-[100] shadow-[0_-4px_20px_rgba(0,0,0,0.04)]">
            <button @click="prevQuestion()" :disabled="currentIndex === 0"
                class="flex items-center gap-2 px-4 py-2 rounded-xl font-bold text-sm bg-slate-100 text-slate-500 disabled:opacity-30 disabled:cursor-not-allowed hover:bg-slate-200 transition-all">
                <i class="fas fa-chevron-left text-xs"></i><span class="hidden sm:inline">Sebelumnya</span>
            </button>
            <div class="flex flex-col items-center gap-1">
                <span class="text-xs font-bold text-slate-400 tracking-wider uppercase">Soal</span>
                <div class="flex items-center gap-1.5">
                    <span class="text-lg font-black text-slate-800" x-text="currentIndex + 1"></span>
                    <span class="text-slate-300 font-light">/</span>
                    <span class="text-sm font-bold text-slate-400" x-text="questionIds.length"></span>
                </div>
            </div>
            <button @click="nextQuestion()"
                class="flex items-center gap-2 px-4 py-2 rounded-xl font-bold text-sm text-white transition-all shadow-md hover:shadow-lg hover:-translate-y-0.5"
                :class="currentIndex === questionIds.length - 1 ? 'bg-emerald-500 hover:bg-emerald-600 shadow-emerald-200' : 'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-200'">
                <span x-text="currentIndex === questionIds.length - 1 ? 'Selesai' : 'Selanjutnya'"></span>
                <i class="fas text-xs"
                    :class="currentIndex === questionIds.length - 1 ? 'fa-check-double' : 'fa-chevron-right'"></i>
            </button>
        </div>

        {{-- MOBILE NAV OVERLAY & PANEL --}}
        <div id="mobile-nav-overlay" onclick="toggleMobileNav()"></div>
        <div id="mobile-nav-panel">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-black text-slate-800 text-lg">Navigasi Soal</h3>
                <button onclick="toggleMobileNav()"
                    class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition"><i
                        class="fas fa-times"></i></button>
            </div>
            <div class="px-5 py-3 border-b border-slate-100 flex flex-wrap gap-x-4 gap-y-1.5">
                <div class="flex items-center gap-1.5 text-xs text-slate-500">
                    <div class="w-4 h-4 rounded bg-blue-500"></div> Dijawab
                </div>
                <div class="flex items-center gap-1.5 text-xs text-slate-500">
                    <div class="w-4 h-4 rounded bg-amber-100 border border-amber-400"></div> Ragu-ragu
                </div>
                <div class="flex items-center gap-1.5 text-xs text-slate-500">
                    <div class="w-4 h-4 rounded bg-white border-2 border-slate-200"></div> Belum dijawab
                </div>
            </div>
            <div class="flex-1 overflow-y-auto p-5">
                <div id="mobile-nav-grid" class="grid grid-cols-5 gap-2"></div>
            </div>
            <div class="p-5 border-t border-slate-100 bg-slate-50">
                <div class="flex justify-between text-sm mb-3">
                    <span class="text-slate-500 font-semibold">Terjawab</span>
                    <span id="mobile-nav-count" class="font-black text-indigo-600">0 / 0</span>
                </div>
                <button onclick="toggleMobileNav(); window._examRunner?.finishExam();"
                    class="w-full bg-emerald-500 hover:bg-emerald-600 text-white py-3 rounded-xl font-black transition flex items-center justify-center gap-2">
                    <i class="fas fa-check-circle"></i> Kumpulkan Ujian
                </button>
            </div>
        </div>

        <form id="finish-form" action="{{ route('student.exam.finish', $exam->id) }}" method="POST"
            style="display: none;">@csrf</form>
    </div>

    {{-- SCRIPTS --}}
    <script>
        window.isExitingExam = false;
        window.isSystemPopup = false;

        document.addEventListener('alpine:init', () => {

            // 1. Store Pengaman
            Alpine.store('examState', {
                started: false,
                showWarning: false,
                isLocked: false,
                violationCount: 0,
                maxViolations: 3,
                enableViolation: true,
                isRequesting: false,

                init() {
                    if (window.initialExamState) {
                        this.violationCount = window.initialExamState.count;
                        this.isLocked = window.initialExamState.isLocked;
                        this.enableViolation = window.initialExamState.config.enable_violation ?? true;
                        this.maxViolations = window.initialExamState.config.max_tolerances ?? 3;
                    }
                },

                startSecureExam() {
                    if (this.isLocked) return;
                    const elem = document.documentElement;
                    const beginExam = () => {
                        this.started = true;
                        if (this.enableViolation) this.monitorFocus();
                    };

                    if (elem.requestFullscreen) {
                        elem.requestFullscreen().then(() => beginExam()).catch(err => {
                            alert("Mohon izinkan akses Fullscreen (Layar Penuh) untuk memulai ujian.");
                        });
                    } else {
                        beginExam();
                    }
                },

                monitorFocus() {
                    document.addEventListener('fullscreenchange', () => {
                        if (!document.fullscreenElement && this.started && !this.isLocked) this.evaluateViolation();
                    });
                    window.addEventListener('blur', () => {
                        if (this.started && !this.isLocked) this.evaluateViolation();
                    });
                    document.addEventListener('contextmenu', e => e.preventDefault());
                    document.addEventListener('keydown', e => {
                        if((e.ctrlKey||e.metaKey) && ['c','v','u','i'].includes(e.key)) e.preventDefault();
                    });
                },

                evaluateViolation() {
                    if (window.isExitingExam || window.isSystemPopup) return;
                    setTimeout(() => {
                        if (window.isExitingExam || window.isSystemPopup) return;
                        this.triggerViolation();
                    }, 200);
                },

                triggerViolation() {
                    if (this.showWarning || this.isLocked || this.isRequesting) return;
                    this.isRequesting = true;
                    this.violationCount++;

                    if (this.violationCount >= this.maxViolations) {
                        this.isLocked = true; this.started = false; this.showWarning = false;
                    } else {
                        this.showWarning = true;
                    }

                    axios.post('{{ route("student.exam.violation") }}', { exam_id: '{{ $exam->id }}' })
                    .then(res => {
                        this.violationCount = res.data.violation_count;
                        this.maxViolations = res.data.max_tolerances;
                        if (res.data.is_locked) {
                            this.isLocked = true; this.started = false; this.showWarning = false;
                        }
                    })
                    .catch(err => console.error(err))
                    .finally(() => { this.isRequesting = false; });
                },

                resumeExam() {
                    if (this.isLocked) return;
                    window.isSystemPopup = true;
                    const elem = document.documentElement;
                    if (elem.requestFullscreen) {
                        elem.requestFullscreen().then(() => {
                            setTimeout(() => window.isSystemPopup = false, 500);
                        }).catch(() => { window.isSystemPopup = false; });
                    } else {
                        window.isSystemPopup = false;
                    }
                    this.showWarning = false;
                }
            });

            // 2. Runner Ujian (Versi AJAX)
            Alpine.data('examRunner', (questionIds, initialTime, existingAnswers, initialFlags, userId, config, examId) => ({
                questionIds: questionIds,
                q: null,
                examId: examId,
                isLoading: true,
                cachedQuestions: {},
                currentIndex: 0,
                timeLeft: parseInt(initialTime),
                answers: (Array.isArray(existingAnswers) && existingAnswers.length === 0) ? {} : existingAnswers,
                flags: initialFlags,
                matchState: { activePremise: null, activeTarget: null },
                lines: [],
                shuffledTargets: {},
                userId: userId,
                config: config || {},
                timerInterval: null,
                isSubmitting: false,
                zoomLevel: 1,

                init() {
                    if (this.config.random_question) {
                        this.questionIds = this.shuffleArray(this.questionIds, '_EXAM_ORDER_' + this.examId);
                    }

                    this.$watch('$store.examState.started', (val) => {
                        if(val) {
                            this.startTimer();
                            this.fetchQuestion(this.currentIndex);
                            window.addEventListener('resize', () => this.repositionLines());
                        }
                    });

                    window.onbeforeunload = () => {
                        if(!window.isExitingExam) return "Ujian sedang berlangsung!";
                    };
                    window._examRunner = this;
                },

                async fetchQuestion(index) {
                    this.isLoading = true;
                    this.clearLines();

                    let qId = this.questionIds[index];

                    if (this.cachedQuestions[qId]) {
                        this.q = this.cachedQuestions[qId];
                        this.finalizeRender();
                        return;
                    }

                    try {
                        const res = await axios.get(`{{ url('/student/exam') }}/${this.examId}/question/${qId}`);
                        let fetchedQuestion = res.data.question;

                        if (this.config.random_answer && ['single_choice','complex_choice', 'true_false', 'true_false_multi'].includes(fetchedQuestion.type) && fetchedQuestion.options) {
                            fetchedQuestion.options = this.shuffleArray(fetchedQuestion.options, '_OPT_' + fetchedQuestion.id);
                        }

                        this.cachedQuestions[qId] = fetchedQuestion;
                        this.q = fetchedQuestion;
                        this.finalizeRender();
                    } catch (error) {
                        console.error("Gagal memuat soal", error);
                        Swal.fire('Koneksi Terputus', 'Gagal memuat soal. Silakan periksa internet Anda.', 'error');
                    }
                },

                finalizeRender() {
                    if (this.q.type === 'matching') {
                        this.prepareMatchingTargets(this.q);
                    }
                    this.isLoading = false;
                    this.$nextTick(() => {
                        this.renderMath();
                        if(this.q.type === 'matching') this.drawLines();
                    });
                },

                renderMath() {
                    if (typeof window.katex !== 'undefined') {
                        document.querySelectorAll('.__se__katex').forEach(el => {
                            let exp = el.getAttribute('data-exp');
                            if (exp) {
                                let decodedExp = exp.replace(/&gt;/g, '>').replace(/&lt;/g, '<').replace(/&amp;/g, '&').replace(/&quot;/g, '"').replace(/&#39;/g, "'").replace(/&nbsp;/g, ' ').replace(/\u00A0/g, ' ').replace(/<br\s*\/?>/gi, '\n');
                                try { window.katex.render(decodedExp, el, { throwOnError: false, displayMode: el.style.display === 'block' || el.tagName === 'DIV' }); } catch (e) { }
                            }
                        });
                    }
                    if (typeof renderMathInElement === 'function') {
                        const area = document.getElementById('question-viewport');
                        if(area) {
                            renderMathInElement(area, {
                                delimiters: [
                                    {left: '$$', right: '$$', display: true},
                                    {left: '$', right: '$', display: false},
                                    {left: '\\(', right: '\\)', display: false},
                                    {left: '\\[', right: '\\]', display: true}
                                ], throwOnError : false
                            });
                        }
                    }
                },
                seededRandom(seed) { let t = seed += 0x6D2B79F5; t = Math.imul(t ^ t >>> 15, t | 1); t ^= t + Math.imul(t ^ t >>> 7, t | 61); return ((t ^ t >>> 14) >>> 0) / 4294967296; },
                shuffleArray(array, seedSuffix) { let m = array.length, t, i, seed = this.userId + seedSuffix; while (m) { let r = this.seededRandom(seed + m); i = Math.floor(r * m--); t = array[m]; array[m] = array[i]; array[i] = t; } return array; },

                prepareMatchingTargets(q) {
                    if (q.matches && !this.shuffledTargets[q.id]) {
                        let targets = q.matches.map(m => ({ id: m.id, text: m.target_text }));
                        if (this.config.random_answer) targets = this.shuffleArray(targets, '_MATCH_' + q.id);
                        else targets = this.shuffleArray(targets, '_MATCH_DEFAULT_' + q.id);
                        this.shuffledTargets[q.id] = targets;
                    }
                },

                startTimer() {
                    this.timerInterval = setInterval(() => {
                        if (this.timeLeft > 0) {
                            this.timeLeft--;
                            if (this.timeLeft % 30 === 0) {
                                axios.get('{{ route("student.exam.status", $exam) }}').then(res => {
                                    if (res.data.status === 'completed' || res.data.is_locked) this.triggerForceEnd();
                                });
                            }
                        } else {
                            clearInterval(this.timerInterval);
                            this.forceSubmit();
                        }
                    }, 1000);
                },

                triggerForceEnd() {
                    if(window.isExitingExam) return;
                    window.isExitingExam = true; window.isSystemPopup = true;
                    clearInterval(this.timerInterval);
                    Swal.fire({
                        title: 'Akses Ditutup!', text: 'Sesi ujian Anda telah diakhiri.', icon: 'warning',
                        allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false, timer: 3000, timerProgressBar: true
                    }).then(() => window.location.reload());
                },

                formatTime(s) { return `${String(Math.floor(s/3600)).padStart(2,'0')}:${String(Math.floor((s%3600)/60)).padStart(2,'0')}:${String(s%60).padStart(2,'0')}`; },
                formatType(t) { const m={'single_choice':'Pilihan Ganda','complex_choice':'Pilihan Kompleks','matching':'Menjodohkan','true_false':'Benar/Salah','essay':'Essay'}; return m[t] || 'Soal'; },

                nextQuestion() { if(this.currentIndex < this.questionIds.length-1) { this.currentIndex++; this.fetchQuestion(this.currentIndex); } else this.finishExam(); },
                prevQuestion() { if(this.currentIndex > 0) { this.currentIndex--; this.fetchQuestion(this.currentIndex); } },
                gotoQuestion(index) { this.currentIndex = index; this.fetchQuestion(index); },

                hasAnswer(qId) { const a=this.answers[qId]; return a && (Array.isArray(a)?a.length>0:(typeof a==='object'?Object.keys(a).length>0:a!=="")); },
                selectAnswer(qId, optId) { this.answers[qId]=optId; this.saveAnswer(qId,optId); },
                toggleMultipleAnswer(qId, optId) { if(!Array.isArray(this.answers[qId])) this.answers[qId]=[]; const idx=this.answers[qId].indexOf(optId); if(idx===-1) this.answers[qId].push(optId); else this.answers[qId].splice(idx,1); this.saveAnswer(qId,this.answers[qId]); },
                isOptionSelected(qId, optId) { return Array.isArray(this.answers[qId]) && this.answers[qId].includes(optId); },
                saveSubAnswer(qId, optId, val) { if(typeof this.answers[qId]!=='object'||Array.isArray(this.answers[qId])) this.answers[qId]={}; this.answers[qId][optId]=val; this.saveAnswer(qId,this.answers[qId]); },
                getSubValue(qId, optId) { return (this.answers[qId]&&this.answers[qId][optId]) ? this.answers[qId][optId] : null; },

                clickMatch(qId, id, type) {
                    if (type === 'premise') this.matchState.activePremise = id; else this.matchState.activeTarget = id;
                    if (this.matchState.activePremise && this.matchState.activeTarget) {
                        if (typeof this.answers[qId] !== 'object' || Array.isArray(this.answers[qId])) this.answers[qId] = {};
                        this.answers[qId][this.matchState.activePremise] = this.matchState.activeTarget;
                        this.saveAnswer(qId, this.answers[qId]);
                        this.matchState = { activePremise: null, activeTarget: null };
                        this.clearLines(); this.$nextTick(() => this.drawLines());
                    }
                },
                drawLines() {
                    this.clearLines();
                    const q = this.q;
                    if (!q || q.type !== 'matching' || !this.answers[q.id]) return;
                    const colors = ['#4f46e5', '#ec4899', '#10b981', '#f59e0b', '#06b6d4']; let i = 0;
                    Object.entries(this.answers[q.id]).forEach(([p, t]) => {
                        const s = document.getElementById('premise-'+p), e = document.getElementById('target-'+t);
                        if(s && e && s.offsetParent && e.offsetParent) {
                            this.lines.push(new LeaderLine(s, e, { color: colors[i++%colors.length], size: 3, path: 'straight', startSocket: 'right', endSocket: 'left', endPlug: 'arrow3' }));
                        }
                    });
                },
                clearLines() { this.lines.forEach(l=>l.remove()); this.lines=[]; },
                repositionLines() { if(this.lines.length) window.requestAnimationFrame(()=>this.lines.forEach(l=>l.position())); },

                toggleFlag(qId) {
                    const idx = this.flags.indexOf(qId);
                    if (idx === -1) this.flags.push(qId); else this.flags.splice(idx, 1);
                    axios.post('{{ route("student.exam.save") }}', { exam_id: '{{ $exam->id }}', question_id: qId, answer: this.answers[qId] ?? null, is_doubtful: this.flags.includes(qId) }).catch(e => console.error(e));
                },
                saveAnswer(qId, val) {
                    if (window.isExitingExam) return;
                    if (val !== undefined) this.answers[qId] = val;
                    axios.post('{{ route("student.exam.save") }}', { exam_id: '{{ $exam->id }}', question_id: qId, answer: this.answers[qId] ?? null, is_doubtful: this.flags.includes(qId) }).catch(e => console.error(e));
                },

                finishExam() {
                    if(this.isSubmitting) return;
                    window.isSystemPopup = true;
                    try { const q = this.q; if(this.answers[q.id]) this.saveAnswer(q.id, this.answers[q.id]); } catch(e){}

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Kumpulkan Ujian?', icon: 'question', allowOutsideClick: false, allowEscapeKey: false, showCancelButton: true, confirmButtonText: 'Ya, Kumpulkan', cancelButtonText: 'Batal', confirmButtonColor: '#10b981', cancelButtonColor: '#94a3b8',
                            html: `
                                <p class="text-slate-500 text-sm mb-4">Pastikan semua jawaban sudah terisi sebelum mengumpulkan.</p>
                                <div style="background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:0.75rem;padding:1rem;margin-bottom:0.5rem">
                                    <label style="display:flex;align-items:flex-start;gap:0.75rem;cursor:pointer;text-align:left">
                                        <input type="checkbox" id="swal-confirm-check" style="width:18px;height:18px;margin-top:2px;accent-color:#10b981;flex-shrink:0;cursor:pointer">
                                        <span style="font-size:0.85rem;font-weight:600;color:#334155;line-height:1.5">Saya yakin ingin mengakhiri ujian dan mengumpulkan semua jawaban saya.</span>
                                    </label>
                                </div>
                            `,
                            didOpen: () => {
                                const confirmBtn = Swal.getConfirmButton();
                                const checkbox   = document.getElementById('swal-confirm-check');
                                confirmBtn.disabled = true; confirmBtn.style.opacity = '0.4'; confirmBtn.style.cursor  = 'not-allowed';
                                checkbox.addEventListener('change', function () {
                                    confirmBtn.disabled = !this.checked; confirmBtn.style.opacity = this.checked ? '1' : '0.4'; confirmBtn.style.cursor  = this.checked ? 'pointer' : 'not-allowed';
                                });
                            },
                            preConfirm: () => {
                                const checkbox = document.getElementById('swal-confirm-check');
                                if (!checkbox || !checkbox.checked) { Swal.showValidationMessage('Centang kotak persetujuan terlebih dahulu.'); return false; }
                                return true;
                            }
                        }).then((result) => {
                            if (result.isConfirmed) this.forceSubmit();
                            else setTimeout(() => window.isSystemPopup = false, 200);
                        });
                    } else {
                        if (confirm("Kumpulkan Ujian?")) this.forceSubmit(); else window.isSystemPopup = false;
                    }
                },

                forceSubmit() {
                    this.isSubmitting = true;
                    window.isExitingExam = true; window.isSystemPopup = true;
                    this.clearLines(); clearInterval(this.timerInterval);
                    window.onbeforeunload = null;
                    const f = document.getElementById('finish-form');

                    if(f) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ title: 'Menyimpan Jawaban...', html: 'Mohon tunggu sebentar, jangan tutup halaman ini.', allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false, didOpen: () => { Swal.showLoading(); } });
                        }
                        f.submit();
                    } else {
                        alert('Terjadi kesalahan pada formulir. Gagal mengirim.');
                        this.isSubmitting = false; window.isExitingExam = false; window.isSystemPopup = false;
                    }
                }
            }));
        });

        // ── LIGHTBOX (TETAP SAMA SEPERTI ASLI) ──
        (function () {
            let scale = 1, minScale = 0.5, maxScale = 5, translateX = 0, translateY = 0, isDragging = false, lastX = 0, lastY = 0, lastPinchDist = null;
            function getImg()  { return document.getElementById('lightbox-img'); }
            function getWrap() { return document.getElementById('lightbox-img-wrap'); }
            function applyTransform() { const img = getImg(); if (!img) return; img.style.transform = `translate(${translateX}px, ${translateY}px) scale(${scale})`; }
            function resetTransform() { scale = 1; translateX = 0; translateY = 0; applyTransform(); }
            window.openLightbox = function(src) { const lb = document.getElementById('lightbox'); const img = getImg(); if (!lb || !img) return; resetTransform(); img.src = src; lb.classList.add('open'); document.addEventListener('keydown', lbKeyHandler); };
            window.hideLightbox = function() { const lb = document.getElementById('lightbox'); const img = getImg(); if (!lb || !img) return; lb.classList.remove('open'); img.src = ''; resetTransform(); document.removeEventListener('keydown', lbKeyHandler); };
            window.closeLightbox = function(e) { if (e.target === document.getElementById('lightbox')) hideLightbox(); };
            window.zoomLightbox = function(delta) { if (delta === 0) { resetTransform(); return; } scale = Math.min(maxScale, Math.max(minScale, scale + delta)); applyTransform(); };
            function lbKeyHandler(e) { if (e.key === 'Escape') hideLightbox(); if (e.key === '+' || e.key === '=') zoomLightbox(0.3); if (e.key === '-') zoomLightbox(-0.3); if (e.key === '0') zoomLightbox(0); }

            document.addEventListener('DOMContentLoaded', function () {
                const wrap = getWrap(), img = getImg(), viewport = document.getElementById('question-viewport');
                if (viewport) { viewport.addEventListener('click', function (e) { if (e.target.tagName === 'IMG') openLightbox(e.target.src); }); }
                if (!wrap || !img) return;
                wrap.addEventListener('wheel', function (e) { e.preventDefault(); const delta = e.deltaY < 0 ? 0.2 : -0.2; scale = Math.min(maxScale, Math.max(minScale, scale + delta)); applyTransform(); }, { passive: false });
                wrap.addEventListener('mousedown', function (e) { if (e.button !== 0) return; isDragging = true; lastX = e.clientX; lastY = e.clientY; wrap.classList.add('grabbing'); });
                document.addEventListener('mousemove', function (e) { if (!isDragging) return; translateX += e.clientX - lastX; translateY += e.clientY - lastY; lastX = e.clientX; lastY = e.clientY; applyTransform(); });
                document.addEventListener('mouseup', function () { isDragging = false; wrap.classList.remove('grabbing'); });
                wrap.addEventListener('touchstart', function (e) { if (e.touches.length === 1) { isDragging = true; lastX = e.touches[0].clientX; lastY = e.touches[0].clientY; } else if (e.touches.length === 2) { isDragging = false; lastPinchDist = getPinchDist(e.touches); } }, { passive: true });
                wrap.addEventListener('touchmove', function (e) { e.preventDefault(); if (e.touches.length === 1 && isDragging) { translateX += e.touches[0].clientX - lastX; translateY += e.touches[0].clientY - lastY; lastX = e.touches[0].clientX; lastY = e.touches[0].clientY; applyTransform(); } else if (e.touches.length === 2) { const dist = getPinchDist(e.touches); if (lastPinchDist !== null) { const ratio = dist / lastPinchDist; scale = Math.min(maxScale, Math.max(minScale, scale * ratio)); applyTransform(); } lastPinchDist = dist; } }, { passive: false });
                wrap.addEventListener('touchend', function (e) { if (e.touches.length < 2) lastPinchDist = null; if (e.touches.length === 0) isDragging = false; }, { passive: true });
                let lastTap = 0; wrap.addEventListener('touchend', function (e) { const now = Date.now(); if (now - lastTap < 300) resetTransform(); lastTap = now; }, { passive: true });
            });
            function getPinchDist(touches) { const dx = touches[0].clientX - touches[1].clientX; const dy = touches[0].clientY - touches[1].clientY; return Math.sqrt(dx * dx + dy * dy); }
        })();

        // ── MOBILE NAV (VERSI AJAX) ──
        function toggleMobileNav() {
            const overlay = document.getElementById('mobile-nav-overlay');
            const panel   = document.getElementById('mobile-nav-panel');
            if (panel.classList.contains('open')) {
                panel.classList.remove('open'); overlay.classList.remove('open');
            } else {
                renderMobileNav(); panel.classList.add('open'); overlay.classList.add('open');
            }
        }

        function renderMobileNav() {
            const runner = window._examRunner;
            if (!runner) return;

            const grid  = document.getElementById('mobile-nav-grid');
            const count = document.getElementById('mobile-nav-count');
            if (!grid) return;

            const questionIds = runner.questionIds;
            const answered  = questionIds.filter(id => runner.hasAnswer(id)).length;
            if (count) count.textContent = `${answered} / ${questionIds.length}`;

            grid.innerHTML = '';
            questionIds.forEach((qId, index) => {
                const btn = document.createElement('button');
                btn.textContent = index + 1;

                const isCurrent  = runner.currentIndex === index;
                const isFlagged  = runner.flags.includes(qId);
                const isAnswered = runner.hasAnswer(qId);

                let cls = 'aspect-square rounded-xl font-black text-xs border-2 flex items-center justify-center w-full transition-all ';
                if (isCurrent)        cls += 'bg-indigo-600 text-white border-indigo-600 scale-105 shadow-md';
                else if (isFlagged)   cls += 'bg-amber-100 text-amber-600 border-amber-400';
                else if (isAnswered)  cls += 'bg-blue-500 text-white border-blue-500';
                else                  cls += 'bg-white text-slate-400 border-slate-200';

                btn.className = cls;
                btn.onclick = () => { runner.gotoQuestion(index); toggleMobileNav(); };
                grid.appendChild(btn);
            });
        }
    </script>

    <div id="lightbox" onclick="closeLightbox(event)">
        <button id="lightbox-close" onclick="hideLightbox()"><i class="fas fa-times"></i></button>
        <div id="lightbox-img-wrap"><img id="lightbox-img" src="" alt="Preview"></div>
        <div id="lightbox-zoom-bar">
            <button onclick="zoomLightbox(-0.5)" title="Zoom Out"><i class="fas fa-search-minus"></i></button>
            <button onclick="zoomLightbox(0)" title="Reset"><i class="fas fa-expand"></i></button>
            <button onclick="zoomLightbox(0.5)" title="Zoom In"><i class="fas fa-search-plus"></i></button>
        </div>
        <span id="lightbox-hint">Pinch/scroll zoom · Geser · Tekan Esc untuk tutup</span>
    </div>
    @endif
</x-cbt-layout>
