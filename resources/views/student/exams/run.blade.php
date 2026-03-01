<x-app-layout>
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

        [x-cloak] {
            display: none !important;
        }
    </style>

    {{-- ======================================================================= --}}
    {{-- LOGIKA SERVER SIDE: BLOKIR JIKA DIKUNCI / SELESAI --}}
    {{-- ======================================================================= --}}

    @if($pivot->is_locked)
    {{-- TAMPILAN TERKUNCI PERMANEN --}}
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

    {{-- TAMBAHKAN BAGIAN INI: TAMPILAN JIKA UJIAN SUDAH SELESAI --}}
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
    {{-- TAMPILAN NORMAL (HANYA DIRENDER JIKA TIDAK DIKUNCI) --}}
    <script>
        window.initialExamState = {
                count: {{ (int) $pivot->violation_count }},
                isLocked: false // Karena masuk blok else, pasti false
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
        <i class="fas fa-shield-alt text-6xl text-emerald-400 mb-6"></i>
        <h1 class="text-3xl font-black mb-2">Mode Ujian Aman</h1>
        <p class="text-slate-400 mb-8 max-w-lg">Ujian ini mewajibkan mode Layar Penuh. Dilarang berpindah tab.</p>
        <button @click="$store.examState.startSecureExam()"
            class="bg-emerald-500 hover:bg-emerald-600 text-white px-10 py-4 rounded-2xl font-black text-lg shadow-xl shadow-emerald-500/20 transition transform hover:scale-105">
            MULAI UJIAN SEKARANG
        </button>
    </div>

    <div class="fixed inset-0 flex flex-col h-screen bg-[#f1f5f9]" x-data="examRunner(
                {{ json_encode($questions) }},
                {{ $timeLeftSeconds }},
                {{ json_encode($existingAnswers) }},
                {{ json_encode($flags ?? []) }},
                {{ auth()->id() }},
                {{ json_encode([ 'random_question' => $exam->random_question ?? false, 'random_answer' => $exam->random_answer ?? false ]) }}
            )" x-show="$store.examState.started && !$store.examState.isLocked" x-cloak>

        <div
            class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-8 z-[100] shadow-sm select-none relative">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg"><i
                        class="fas fa-graduation-cap"></i></div>
                <h1 class="font-black text-slate-800 text-sm tracking-widest uppercase">{{ $exam->title }}</h1>
            </div>
            <div class="bg-slate-900 text-white px-6 py-2 rounded-xl font-mono font-bold text-xl flex items-center gap-3 shadow-lg"
                :class="timeLeft < 300 ? 'bg-rose-600 animate-pulse' : ''">
                <i class="fas fa-clock text-sm opacity-50"></i> <span x-text="formatTime(timeLeft)"></span>
            </div>
            <button type="button" @click.prevent="finishExam()"
                class="relative z-[101] bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-2 rounded-xl font-bold transition shadow-lg flex items-center gap-2 cursor-pointer">
                <span x-show="!isSubmitting">Selesai</span> <span x-show="isSubmitting"><i
                        class="fas fa-spinner fa-spin"></i></span> <i x-show="!isSubmitting"
                    class="fas fa-check-circle"></i>
            </button>
        </div>

        <div class="flex-1 flex overflow-hidden">
            <div id="question-viewport" class="flex-1 overflow-y-auto custom-scrollbar p-6 sm:p-12 pb-32"
                @scroll="repositionLines()">
                <div class="max-w-4xl mx-auto">
                    <template x-for="(q, index) in questions" :key="q.id">
                        <div x-show="currentIndex === index" x-transition:enter="transition duration-300 ease-out"
                            x-transition:enter-start="opacity-0 translate-y-4"
                            x-transition:enter-end="opacity-100 translate-y-0">
                            <div class="flex justify-between items-center mb-8">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="bg-indigo-600 text-white px-6 py-2.5 rounded-2xl font-black shadow-lg">NO.
                                        <span x-text="index + 1" class="text-xl"></span></span>
                                    <span
                                        class="text-[10px] font-black text-slate-400 uppercase tracking-widest bg-slate-100 border border-slate-200 px-4 py-1.5 rounded-full"
                                        x-text="formatType(q.type)"></span>
                                </div>
                                <button @click="toggleFlag(q.id)"
                                    class="px-5 py-2.5 rounded-xl font-bold text-sm border-2 transition-all flex items-center gap-2"
                                    :class="flags.includes(q.id) ? 'bg-amber-400 text-white border-amber-400' : 'bg-white text-slate-400 border-slate-200'">
                                    <i class="fas fa-bookmark"></i> <span
                                        x-text="flags.includes(q.id) ? 'Ditandai' : 'Ragu-ragu'"></span>
                                </button>
                            </div>
                            <div
                                class="bg-white rounded-[2.5rem] p-10 shadow-sm border border-slate-100 mb-8 relative overflow-hidden">
                                <div
                                    class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500">
                                </div>
                                <div class="prose prose-indigo prose-lg max-w-none font-bold text-slate-700 leading-relaxed no-select"
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
                                        <div class="match-column"><template x-for="m in q.matches" :key="'p-'+m.id">
                                                <div class="match-item premise" :id="'premise-' + m.id"
                                                    @click="clickMatch(q.id, m.id, 'premise')"
                                                    :class="matchState.activePremise === m.id ? 'selected' : ''"><span
                                                        x-html="m.premise_text"></span>
                                                    <div class="match-dot dot-right"></div>
                                                </div>
                                            </template></div>
                                        <div class="match-column"><template x-for="target in shuffledTargets[q.id]"
                                                :key="'t-'+target.id">
                                                <div class="match-item target" :id="'target-' + target.id"
                                                    @click="clickMatch(q.id, target.id, 'target')"
                                                    :class="matchState.activeTarget === target.id ? 'selected' : ''">
                                                    <span x-text="target.text"></span>
                                                    <div class="match-dot dot-left"></div>
                                                </div>
                                            </template></div>
                                    </div>
                                </template>
                                <template x-if="q.type === 'essay'">
                                    <x-exam.essay :q="'q'" />
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
            <div
                class="w-80 bg-white border-l border-slate-200 hidden lg:flex flex-col z-50 shadow-[-5px_0_30px_rgba(0,0,0,0.02)]">
                <div class="p-6 bg-white border-b border-slate-100">
                    <h3 class="font-black text-slate-800 text-lg">Navigasi Soal</h3>
                </div>
                <div class="flex-1 overflow-y-auto p-6 custom-scrollbar bg-slate-50/50">
                    <div class="grid grid-cols-4 gap-3">
                        <template x-for="(q, index) in questions" :key="q.id">
                            <button @click="currentIndex = index"
                                class="aspect-square rounded-xl font-black text-sm transition-all border-2 flex items-center justify-center relative"
                                :class="{ 'bg-indigo-600 text-white border-indigo-600 scale-110 z-10': currentIndex === index, 'bg-white text-indigo-600 border-indigo-200': hasAnswer(q.id) && currentIndex !== index, 'bg-amber-100 text-amber-600 border-amber-300': flags.includes(q.id) && currentIndex !== index, 'bg-white text-slate-300 border-slate-100': !hasAnswer(q.id) && !flags.includes(q.id) && currentIndex !== index }"><span
                                    x-text="index + 1"></span></button>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="h-24 bg-white border-t border-slate-200 flex items-center justify-between px-8 sm:px-12 z-[100] shadow-[0_-5px_30px_rgba(0,0,0,0.03)]">
            <button @click="prevQuestion()" :disabled="currentIndex === 0"
                class="px-8 py-3.5 rounded-2xl font-black bg-slate-100 text-slate-500 disabled:opacity-30 disabled:cursor-not-allowed hover:bg-slate-200 transition-all flex items-center gap-3"><i
                    class="fas fa-arrow-left"></i> SEBELUMNYA</button>
            <button @click="nextQuestion()"
                class="px-10 py-3.5 rounded-2xl font-black text-white shadow-xl transition-all flex items-center gap-3 hover:-translate-y-1"
                :class="currentIndex === questions.length - 1 ? 'bg-emerald-500 hover:bg-emerald-600' : 'bg-indigo-600 hover:bg-indigo-700'"><span
                    x-text="currentIndex === questions.length - 1 ? 'SELESAI & KUMPULKAN' : 'SELANJUTNYA'"></span><i
                    class="fas"
                    :class="currentIndex === questions.length - 1 ? 'fa-check-double' : 'fa-arrow-right'"></i></button>
        </div>
        <form id="finish-form" action="{{ route('student.exam.finish', $exam->id) }}" method="POST"
            style="display: none;">@csrf</form>
    </div>

    {{-- SCRIPT HANYA DIJALANKAN JIKA TIDAK TERKUNCI --}}
    {{-- SCRIPT HANYA DIJALANKAN JIKA TIDAK TERKUNCI --}}
    <script>
        // Variabel global pengaman antar-komponen
        window.isExitingExam = false;
        window.isSystemPopup = false; // Flag khusus saat pop-up kita sendiri sedang terbuka

        document.addEventListener('alpine:init', () => {

                // 1. Store Pengaman
                Alpine.store('examState', {
                    started: false,
                    showWarning: false,
                    isLocked: false,
                    violationCount: 0,
                    maxViolations: 3,
                    isRequesting: false,

                    init() {
                        if (window.initialExamState) {
                            this.violationCount = window.initialExamState.count;
                            this.isLocked = window.initialExamState.isLocked;
                        }
                    },

                    startSecureExam() {
                        if (this.isLocked) return;
                        const elem = document.documentElement;
                        if (elem.requestFullscreen) {
                            elem.requestFullscreen().then(() => {
                                this.started = true;
                                this.monitorFocus();
                            }).catch(err => {
                                alert("Mohon izinkan akses Fullscreen untuk memulai ujian.");
                            });
                        } else {
                            // Fallback jika browser tidak dukung fullscreen API
                            this.started = true;
                            this.monitorFocus();
                        }
                    },

                    monitorFocus() {
                        // Event saat keluar fullscreen
                        document.addEventListener('fullscreenchange', () => {
                            if (!document.fullscreenElement && this.started && !this.isLocked) {
                                this.evaluateViolation();
                            }
                        });

                        // Event saat pindah tab/aplikasi
                        window.addEventListener('blur', () => {
                            if (this.started && !this.isLocked) {
                                this.evaluateViolation();
                            }
                        });

                        document.addEventListener('contextmenu', e => e.preventDefault());
                        document.addEventListener('keydown', e => {
                            if((e.ctrlKey||e.metaKey) && ['c','v','u','i'].includes(e.key)) e.preventDefault();
                        });
                    },

                    evaluateViolation() {
                        // JIKA user sedang proses exit (submit) ATAU pop-up SweetAlert sistem sedang terbuka, abaikan!
                        if (window.isExitingExam || window.isSystemPopup) return;

                        // Jeda sedikit untuk memastikan ini bukan blur palsu karena render browser
                        setTimeout(() => {
                            if (window.isExitingExam || window.isSystemPopup) return;
                            this.triggerViolation();
                        }, 200);
                    },

                    triggerViolation() {
                        if (this.showWarning || this.isLocked || this.isRequesting) return;

                        this.isRequesting = true;
                        this.violationCount++; // Optimistic UI

                        if (this.violationCount >= this.maxViolations) {
                            this.isLocked = true;
                            this.started = false;
                            this.showWarning = false;
                        } else {
                            this.showWarning = true;
                        }

                        axios.post('{{ route("student.exam.violation") }}', {
                            exam_id: '{{ $exam->id }}'
                        })
                        .then(res => {
                            this.violationCount = res.data.violation_count;
                            if (res.data.is_locked) {
                                this.isLocked = true;
                                this.started = false;
                                this.showWarning = false;
                            }
                        })
                        .catch(err => console.error(err))
                        .finally(() => { this.isRequesting = false; });
                    },

                    resumeExam() {
                        if (this.isLocked) return;
                        const elem = document.documentElement;

                        // Set sistem popup agar request fullscreen tidak mentrigger violation
                        window.isSystemPopup = true;

                        if (elem.requestFullscreen) {
                            elem.requestFullscreen().then(() => {
                                setTimeout(() => window.isSystemPopup = false, 500);
                            }).catch(() => {
                                window.isSystemPopup = false;
                            });
                        } else {
                            window.isSystemPopup = false;
                        }

                        this.showWarning = false;
                    }
                });

                // 2. Runner Ujian
                Alpine.data('examRunner', (questions, initialTime, existingAnswers, initialFlags, userId, config) => ({
                    questions: JSON.parse(JSON.stringify(questions)),
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

                    init() {
                        if (this.config.random_question) this.shuffleQuestions();
                        if (this.config.random_answer) this.shuffleOptions();
                        this.prepareMatchingTargets();

                        this.$watch('$store.examState.started', (val) => {
                            if(val) {
                                this.startTimer();
                                this.$nextTick(() => { this.renderMath(); this.drawLines(); });
                                window.addEventListener('resize', () => this.repositionLines());
                            }
                        });
                        this.$watch('currentIndex', () => { this.clearLines(); this.$nextTick(() => { this.renderMath(); this.drawLines(); }); });
                        window.onbeforeunload = () => {
                            if(!window.isExitingExam) return "Ujian sedang berlangsung!";
                        };
                    },

                    renderMath() {
                        // 1. RENDER RUMUS DARI TOMBOL MATH SUNEDITOR (Tag span data-exp)
                        if (typeof window.katex !== 'undefined') {
                            document.querySelectorAll('.__se__katex').forEach(el => {
                                let exp = el.getAttribute('data-exp');
                                if (exp) {
                                    // --- PROSES PENCUCIAN KARAKTER ---
                                    let decodedExp = exp
                                        .replace(/&gt;/g, '>')
                                        .replace(/&lt;/g, '<')
                                        .replace(/&amp;/g, '&')
                                        .replace(/&quot;/g, '"')
                                        .replace(/&#39;/g, "'")
                                        .replace(/&nbsp;/g, ' ')
                                        .replace(/\u00A0/g, ' ')
                                        .replace(/<br\s*\/?>/gi, '\n');

                                    try {
                                        window.katex.render(decodedExp, el, {
                                            throwOnError: false,
                                            displayMode: el.style.display === 'block' || el.tagName === 'DIV'
                                        });
                                    } catch (e) {
                                        console.error("Gagal render KaTeX:", e);
                                    }
                                }
                            });
                        }

                        // 2. RENDER RUMUS KETIKAN MANUAL (Auto-Render $$...$$)
                        if (typeof renderMathInElement === 'function') {
                            const area = document.getElementById('question-viewport');
                            if(area) {
                                renderMathInElement(area, {
                                    delimiters: [
                                        {left: '$$', right: '$$', display: true},
                                        {left: '$', right: '$', display: false},
                                        {left: '\\(', right: '\\)', display: false},
                                        {left: '\\[', right: '\\]', display: true}
                                    ],
                                    throwOnError : false
                                });
                            }
                        }
                    },
                    seededRandom(seed) { let t = seed += 0x6D2B79F5; t = Math.imul(t ^ t >>> 15, t | 1); t ^= t + Math.imul(t ^ t >>> 7, t | 61); return ((t ^ t >>> 14) >>> 0) / 4294967296; },
                    shuffleArray(array, seedSuffix) { let m = array.length, t, i, seed = this.userId + seedSuffix; while (m) { let r = this.seededRandom(seed + m); i = Math.floor(r * m--); t = array[m]; array[m] = array[i]; array[i] = t; } return array; },
                    shuffleQuestions() { this.questions = this.shuffleArray(this.questions, '_EXAM_ORDER_' + '{{ $exam->id }}'); },
                    shuffleOptions() { this.questions.forEach(q => { if(['single_choice','complex_choice', 'true_false', 'true_false_multi'].includes(q.type) && q.options) { q.options = this.shuffleArray(q.options, '_OPT_'+q.id); } }); },
                    prepareMatchingTargets() { this.questions.forEach(q => { if (q.type === 'matching' && q.matches) { let targets = q.matches.map(m => ({ id: m.id, text: m.target_text })); if (this.config.random_answer) { targets = this.shuffleArray(targets, '_MATCH_' + q.id); } else { targets = this.shuffleArray(targets, '_MATCH_DEFAULT_' + q.id); } this.shuffledTargets[q.id] = targets; } }); },
                    startTimer() {
                        this.timerInterval = setInterval(() => {
                            if (this.timeLeft > 0) {
                                this.timeLeft--;

                                // HEARTBEAT: Cek status diam-diam setiap 5 detik
                                if (this.timeLeft % 5 === 0) {
                                    axios.get(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                                        .then(res => {
                                            // Membaca balasan JSON dari Controller
                                            if (res.data && (res.data.status === 'completed' || res.data.is_locked === true)) {
                                                this.triggerForceEnd();
                                            }
                                        }).catch((err) => {
                                            if (err.response && (err.response.status === 403 || err.response.status === 401)) {
                                                this.triggerForceEnd();
                                            }
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
                        window.isExitingExam = true;
                        window.isSystemPopup = true;
                        clearInterval(this.timerInterval);

                        Swal.fire({
                            title: 'Akses Ditutup!',
                            text: 'Sesi ujian Anda telah diakhiri oleh Pengawas atau waktu telah habis.',
                            icon: 'warning',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        }).then(() => {
                            // Me-reload halaman agar Blade menampilkan pesan UJIAN TELAH BERAKHIR
                            window.location.reload();
                        });
                    },
                    formatTime(s) { return `${String(Math.floor(s/3600)).padStart(2,'0')}:${String(Math.floor((s%3600)/60)).padStart(2,'0')}:${String(s%60).padStart(2,'0')}`; },
                    formatType(t) { const m={'single_choice':'Pilihan Ganda','complex_choice':'Pilihan Kompleks','matching':'Menjodohkan','true_false':'Benar/Salah','essay':'Essay'}; return m[t] || 'Soal'; },
                    nextQuestion() { if(this.currentIndex < this.questions.length-1) this.currentIndex++; else this.finishExam(); },
                    prevQuestion() { if(this.currentIndex > 0) this.currentIndex--; },
                    hasAnswer(qId) { const a=this.answers[qId]; return a && (Array.isArray(a)?a.length>0:(typeof a==='object'?Object.keys(a).length>0:a!=="")); },
                    selectAnswer(qId, optId) { this.answers[qId]=optId; this.saveAnswer(qId,optId); },
                    toggleMultipleAnswer(qId, optId) { if(!Array.isArray(this.answers[qId])) this.answers[qId]=[]; const idx=this.answers[qId].indexOf(optId); if(idx===-1) this.answers[qId].push(optId); else this.answers[qId].splice(idx,1); this.saveAnswer(qId,this.answers[qId]); },
                    isOptionSelected(qId, optId) { return Array.isArray(this.answers[qId]) && this.answers[qId].includes(optId); },
                    saveSubAnswer(qId, optId, val) { if(typeof this.answers[qId]!=='object'||Array.isArray(this.answers[qId])) this.answers[qId]={}; this.answers[qId][optId]=val; this.saveAnswer(qId,this.answers[qId]); },
                    getSubValue(qId, optId) { return (this.answers[qId]&&this.answers[qId][optId]) ? this.answers[qId][optId] : null; },
                    clickMatch(qId, id, type) { if (type === 'premise') this.matchState.activePremise = id; else this.matchState.activeTarget = id; if (this.matchState.activePremise && this.matchState.activeTarget) { if (typeof this.answers[qId] !== 'object' || Array.isArray(this.answers[qId])) this.answers[qId] = {}; this.answers[qId][this.matchState.activePremise] = this.matchState.activeTarget; this.saveAnswer(qId, this.answers[qId]); this.matchState = { activePremise: null, activeTarget: null }; this.clearLines(); this.$nextTick(() => this.drawLines()); } },
                    drawLines() { this.clearLines(); const q = this.questions[this.currentIndex]; if (!q || q.type !== 'matching' || !this.answers[q.id]) return; const colors = ['#4f46e5', '#ec4899', '#10b981', '#f59e0b', '#06b6d4']; let i = 0; Object.entries(this.answers[q.id]).forEach(([p, t]) => { const s = document.getElementById('premise-'+p), e = document.getElementById('target-'+t); if(s && e && s.offsetParent && e.offsetParent) { this.lines.push(new LeaderLine(s, e, { color: colors[i++%colors.length], size: 3, path: 'fluid', startSocket: 'right', endSocket: 'left', endPlug: 'arrow3' })); } }); },
                    clearLines() { this.lines.forEach(l=>l.remove()); this.lines=[]; },
                    repositionLines() { if(this.lines.length) window.requestAnimationFrame(()=>this.lines.forEach(l=>l.position())); },
                    toggleFlag(qId) { const idx=this.flags.indexOf(qId); if(idx===-1) this.flags.push(qId); else this.flags.splice(idx,1); this.saveAnswer(qId, this.answers[qId]); },

                    saveAnswer(qId, val) {
                        if(val===undefined || window.isExitingExam) return;
                        this.answers[qId]=val;
                        axios.post('{{ route("student.exam.save") }}', {exam_id:'{{$exam->id}}', question_id:qId, answer:val, is_doubtful:this.flags.includes(qId)}).catch(e=>console.error(e));
                    },

                    finishExam() {
                        if(this.isSubmitting) return;

                        // PASTIKAN popup tidak memicu pelanggaran saat muncul
                        window.isSystemPopup = true;

                        try { const q = this.questions[this.currentIndex]; if(this.answers[q.id]) this.saveAnswer(q.id, this.answers[q.id]); } catch(e){}

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'Kumpulkan Ujian?',
                                text: "Yakin ingin selesai?",
                                icon: 'question',
                                showCancelButton: true,
                                confirmButtonText: 'Ya, Selesai',
                                cancelButtonText: 'Batal',
                                confirmButtonColor: '#10b981'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    this.forceSubmit();
                                } else {
                                    // Matikan flag popup jika batal
                                    setTimeout(() => window.isSystemPopup = false, 200);
                                }
                            });
                        } else {
                            if (confirm("Kumpulkan Ujian?")) {
                                this.forceSubmit();
                            } else {
                                window.isSystemPopup = false;
                            }
                        }
                    },

                    forceSubmit() {
                        this.isSubmitting = true;
                        window.isExitingExam = true;

                        this.clearLines();
                        clearInterval(this.timerInterval);

                        // Matikan store agar berhenti memantau
                        if(Alpine.store('examState')) {
                            Alpine.store('examState').started = false;
                        }

                        window.onbeforeunload = null;
                        const f = document.getElementById('finish-form');

                        if(f) {
                            f.submit();
                        } else {
                            alert('Form error');
                            this.isSubmitting = false;
                            window.isExitingExam = false;
                            window.isSystemPopup = false;
                        }
                    }
                }));
            });
    </script>
    @endif
</x-app-layout>