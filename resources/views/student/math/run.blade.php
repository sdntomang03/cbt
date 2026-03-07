<x-app-layout>
    <style>
        /* Sembunyikan elemen bawaan layout jika perlu */
        header,
        nav {
            display: none !important;
        }

        body {
            background-color: #f8fafc;
            font-family: 'Nunito', sans-serif;
            user-select: none;
        }

        [x-cloak] {
            display: none !important;
        }

        /* Menghilangkan panah atas/bawah di input number */
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type=number] {
            -moz-appearance: textfield;
        }
    </style>

    <div class="h-screen flex flex-col" x-data="mathExamRunner({{ json_encode($questions) }}, {{ $timeLeftSeconds }})">

        {{-- ========================================== --}}
        {{-- LAYAR PERSIAPAN (JANJI KEJUJURAN) --}}
        {{-- ========================================== --}}
        <div x-show="!hasStarted" class="flex-1 flex items-center justify-center p-4 sm:p-6 relative overflow-hidden"
            x-transition>

            {{-- Ornamen Background --}}
            <div class="absolute inset-0 pointer-events-none opacity-[0.03]">
                <i class="fas fa-shield-alt absolute top-20 left-10 text-9xl"></i>
                <i class="fas fa-balance-scale absolute bottom-10 right-10 text-9xl"></i>
            </div>

            {{-- Card Kejujuran --}}
            <div
                class="bg-white p-8 md:p-12 rounded-[2.5rem] shadow-2xl shadow-indigo-100/50 border border-slate-100 max-w-lg w-full text-center relative z-10 transform transition-all">
                <div
                    class="w-24 h-24 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center text-5xl mx-auto mb-6 shadow-inner">
                    <i class="fas fa-file-signature"></i>
                </div>

                <h2 class="text-2xl md:text-3xl font-black text-slate-800 mb-4 uppercase tracking-widest">Janji
                    Kejujuran</h2>

                <p class="text-slate-500 mb-8 leading-relaxed text-sm md:text-base">
                    Saya berjanji akan mengerjakan ujian ini dengan <strong
                        class="text-indigo-600 font-bold">jujur</strong>,
                    tanpa menggunakan alat bantu hitung (kalkulator, hp, dsb) dan tanpa meminta bantuan dari siapapun.
                </p>

                <button @click="startExam()"
                    class="w-full py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-black rounded-2xl shadow-lg shadow-indigo-200 transition-transform active:scale-95 uppercase tracking-widest flex items-center justify-center gap-3">
                    <i class="fas fa-check-circle text-xl"></i> Saya Berjanji & Mulai
                </button>
            </div>
        </div>

        {{-- ========================================== --}}
        {{-- LAYAR UJIAN (HEADER, SOAL, NAVIGASI) --}}
        {{-- ========================================== --}}
        <div x-show="hasStarted" x-cloak class="flex flex-col h-full w-full bg-[#f8fafc]">

            {{-- HEADER UJIAN --}}
            <div
                class="bg-white h-20 shadow-sm border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 shrink-0 relative z-30">
                <div class="flex items-center gap-3 md:gap-4">
                    <div
                        class="w-10 h-10 md:w-12 md:h-12 bg-indigo-600 text-white rounded-xl flex items-center justify-center text-lg md:text-xl shadow-lg rotate-3">
                        <i class="fas fa-calculator -rotate-3"></i>
                    </div>
                    <div>
                        <h1
                            class="font-black text-sm md:text-lg text-slate-800 uppercase tracking-widest hidden sm:block">
                            Tes Matematika</h1>
                        <h1 class="font-black text-sm text-slate-800 uppercase tracking-widest sm:hidden">Tes MTK</h1>
                        <p class="text-[10px] md:text-xs font-bold text-slate-400">Soal <span
                                x-text="currentIndex + 1"></span> dari {{ $exam->total_questions }}</p>
                    </div>
                </div>

                <div class="bg-slate-900 text-white px-4 md:px-6 py-2 md:py-2.5 rounded-xl md:rounded-2xl font-mono font-bold text-lg md:text-2xl shadow-lg flex items-center gap-2 md:gap-3 transition-colors"
                    :class="timeLeft <= 60 ? 'bg-rose-600 animate-pulse' : ''">
                    <i class="fas fa-stopwatch text-xs md:text-sm opacity-50"></i>
                    <span x-text="formatTime(timeLeft)"></span>
                </div>
            </div>

            {{-- AREA TENGAH: SOAL & NAVIGASI --}}
            <div class="flex-1 flex flex-col items-center justify-center p-4 sm:p-6 relative overflow-y-auto">

                {{-- Ornamen Background --}}
                <div class="absolute inset-0 overflow-hidden pointer-events-none opacity-[0.03]">
                    <i class="fas fa-plus absolute top-10 left-10 text-9xl"></i>
                    <i class="fas fa-divide absolute bottom-10 right-10 text-9xl"></i>
                    <i class="fas fa-times absolute top-1/4 right-1/4 text-8xl"></i>
                    <i class="fas fa-minus absolute bottom-1/4 left-1/4 text-8xl"></i>
                </div>

                {{-- Wrapper Container untuk Soal & Navigasi --}}
                <div class="w-full max-w-3xl flex flex-col gap-4 sm:gap-6 relative z-10">

                    {{-- KOTAK SOAL --}}
                    <div
                        class="bg-white rounded-[2rem] sm:rounded-[3rem] shadow-2xl shadow-indigo-100/50 border border-slate-100 text-center w-full min-h-[300px] md:min-h-[400px] flex items-center justify-center overflow-hidden">

                        <template x-for="(q, index) in questions" :key="q.id">
                            <div x-show="currentIndex === index" x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 transform scale-95 translate-y-4"
                                x-transition:enter-end="opacity-100 transform scale-100 translate-y-0"
                                class="w-full p-6 sm:p-10 md:p-12" x-cloak>

                                {{-- Notifikasi jika soal ini dilewati --}}
                                <div x-show="skipped.includes(q.id) && (answers[q.id] === undefined || answers[q.id] === '')"
                                    class="inline-block bg-orange-100 text-orange-600 font-bold text-[10px] px-3 py-1 rounded-full uppercase tracking-wider mb-4 animate-pulse">
                                    <i class="fas fa-exclamation-triangle"></i> Soal Dilewati
                                </div>

                                <div
                                    class="text-indigo-500 font-black tracking-widest text-xs sm:text-sm mb-6 uppercase">
                                    Hitunglah hasil dari operasi di bawah ini:
                                </div>

                                <div
                                    class="text-5xl sm:text-7xl md:text-8xl font-black text-slate-800 flex items-center justify-center gap-4 sm:gap-6 md:gap-10 mb-8 sm:mb-12">
                                    <span x-text="q.num1"></span>
                                    <span x-html="getOperatorIcon(q.operator)"
                                        class="text-indigo-500 text-4xl sm:text-6xl md:text-7xl bg-indigo-50 w-16 h-16 sm:w-24 sm:h-24 md:w-32 md:h-32 rounded-full flex items-center justify-center shadow-inner"></span>
                                    <span x-text="q.num2"></span>
                                    <span class="text-slate-300">=</span>
                                </div>

                                <div>
                                    <input type="number" :id="'input-' + index" x-model="answers[q.id]"
                                        @keydown.enter="nextQuestion()" placeholder="?" @input="removeSkipped(q.id)"
                                        :class="skipped.includes(q.id) && (answers[q.id] === undefined || answers[q.id] === '') ? 'border-orange-400 focus:border-orange-500 bg-orange-50/30' : 'border-slate-200 focus:border-indigo-500 bg-slate-50'"
                                        class="w-32 sm:w-48 md:w-64 text-center text-4xl sm:text-5xl md:text-6xl font-black text-indigo-700 border-4 focus:ring-0 rounded-2xl sm:rounded-3xl py-4 sm:py-6 transition-all shadow-inner placeholder-slate-300">

                                    <p class="text-slate-400 font-bold text-xs sm:text-sm mt-4 hidden sm:block">
                                        <i class="fas fa-keyboard"></i> Ketik jawaban lalu tekan <strong>ENTER</strong>
                                    </p>
                                </div>
                            </div>
                        </template>

                    </div>

                    {{-- NAVIGASI BAWAH (Menempel dengan soal) --}}
                    <div
                        class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-5 shadow-xl shadow-slate-200/50 border border-slate-100 w-full flex flex-col gap-5 sm:gap-6">

                        {{-- 1. Baris Tombol (Horizontal) --}}
                        <div class="flex flex-row items-center justify-between gap-3 sm:gap-4 w-full">

                            {{-- Tombol Kembali --}}
                            <button @click="prevQuestion()" :disabled="currentIndex === 0"
                                class="flex items-center justify-center px-5 sm:px-6 py-3.5 sm:py-4 rounded-xl font-black bg-slate-100 text-slate-500 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-200 transition-colors tracking-wide shrink-0">

                                {{-- Tampilan Mobile (Hanya Ikon <<) --}} <div class="sm:hidden flex items-center">
                                    <i class="fas fa-angle-double-left text-lg"></i>
                        </div>

                        {{-- Tampilan PC (Ikon + Teks) --}}
                        <div class="hidden sm:flex items-center gap-2">
                            <i class="fas fa-arrow-left"></i>
                            <span class="text-sm">KEMBALI</span>
                        </div>
                        </button>

                        {{-- Tombol Lewati (Di Tengah) --}}
                        <button @click="skipQuestion()" x-show="currentIndex < questions.length - 1"
                            class="flex-1 flex items-center justify-center gap-2 px-2 sm:px-8 py-3.5 sm:py-4 rounded-xl font-black bg-orange-100 text-orange-600 hover:bg-orange-200 transition-colors text-xs sm:text-sm tracking-widest uppercase">
                            <span>LEWATI</span>
                            <i class="fas fa-forward hidden sm:block"></i>
                        </button>

                        {{-- Spacer Kosong (Menjaga tata letak saat tombol "Lewati" hilang) --}}
                        <div x-show="currentIndex === questions.length - 1" class="flex-1"></div>

                        {{-- Tombol Lanjut / Selesai --}}
                        <button @click="nextQuestion()"
                            class="flex items-center justify-center px-5 sm:px-8 py-3.5 sm:py-4 rounded-xl font-black text-white shadow-lg transition-transform hover:-translate-y-1 tracking-wide shrink-0"
                            :class="currentIndex === questions.length - 1 ? 'bg-emerald-500 shadow-emerald-200 hover:bg-emerald-600' : 'bg-indigo-600 shadow-indigo-200 hover:bg-indigo-700'">

                            {{-- JIKA BUKAN SOAL TERAKHIR --}}
                            <div x-show="currentIndex !== questions.length - 1" class="w-full">
                                {{-- Tampilan Mobile (Hanya Ikon >>) --}}
                                <div class="sm:hidden flex items-center justify-center">
                                    <i class="fas fa-angle-double-right text-lg"></i>
                                </div>
                                {{-- Tampilan PC (Teks + Ikon) --}}
                                <div class="hidden sm:flex items-center justify-center gap-2">
                                    <span class="text-sm">LANJUT</span>
                                    <i class="fas fa-arrow-right"></i>
                                </div>
                            </div>

                            {{-- JIKA SOAL TERAKHIR --}}
                            <div x-show="currentIndex === questions.length - 1" class="flex items-center gap-2">
                                <span class="sm:hidden text-xs">SELESAI</span>
                                <span class="hidden sm:block text-sm">KUMPULKAN</span>
                                <i class="fas fa-check-double"></i>
                            </div>
                        </button>

                    </div>

                    {{-- 2. Baris Progress Bar --}}
                    <div class="w-full flex flex-col gap-1.5 px-1 pb-1">
                        <div
                            class="flex justify-between items-center text-[10px] sm:text-xs font-bold text-slate-400 px-1">
                            <span>Progress Mengerjakan</span>
                            <span class="text-indigo-600"
                                x-text="Math.round(((currentIndex + 1) / questions.length) * 100) + '%'"></span>
                        </div>

                        {{-- Bar Indikator --}}
                        <div class="h-2.5 sm:h-3 bg-slate-100 rounded-full overflow-hidden relative">
                            <div class="absolute top-0 left-0 h-full bg-gradient-to-r from-indigo-500 to-indigo-400 rounded-full transition-all duration-700 ease-out shadow-inner"
                                :style="`width: ${((currentIndex + 1) / questions.length) * 100}%`">
                                <div
                                    class="absolute top-0 right-0 bottom-0 left-0 w-full h-full bg-white opacity-20 animate-pulse">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    {{-- Form Submit Hidden --}}
    <form id="math-submit-form" action="{{ route('student.math.submit', $exam->id) }}" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="answers" :value="JSON.stringify(answers)">
    </form>

    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('mathExamRunner', (questions, initialTime) => ({
                hasStarted: false,
                questions: questions,
                currentIndex: 0,
                timeLeft: parseInt(initialTime),
                answers: {},
                skipped: [], // Array untuk menyimpan ID soal yang dilewati
                timerInterval: null,

                init() {},

                startExam() {
                    this.hasStarted = true;

                    let elem = document.documentElement;
                    if (elem.requestFullscreen) {
                        elem.requestFullscreen().catch(err => console.warn("Fullscreen diblokir:", err));
                    } else if (elem.webkitRequestFullscreen) {
                        elem.webkitRequestFullscreen();
                    } else if (elem.msRequestFullscreen) {
                        elem.msRequestFullscreen();
                    }

                    this.$nextTick(() => { this.focusInput(); });
                    this.startTimer();
                },

                getOperatorIcon(op) {
                    if(op === '+') return '+';
                    if(op === '-') return '&minus;';
                    if(op === 'x') return '&times;';
                    if(op === ':') return '&divide;';
                    return op;
                },

                startTimer() {
                    this.timerInterval = setInterval(() => {
                        if (this.timeLeft > 0) {
                            this.timeLeft--;
                        } else {
                            clearInterval(this.timerInterval);

                            if (document.exitFullscreen) {
                                document.exitFullscreen().catch(()=>{});
                            }

                            Swal.fire({
                                title: 'Waktu Habis!',
                                text: 'Sistem sedang menyimpan jawaban Anda...',
                                icon: 'warning',
                                timer: 2000,
                                showConfirmButton: false,
                                allowOutsideClick: false,
                            }).then(() => {
                                this.forceSubmit();
                            });
                        }
                    }, 1000);
                },

                formatTime(s) {
                    const m = Math.floor(s / 60);
                    const sec = s % 60;
                    return `${String(m).padStart(2, '0')}:${String(sec).padStart(2, '0')}`;
                },

                focusInput() {
                    setTimeout(() => {
                        const input = document.getElementById('input-' + this.currentIndex);
                        if (input) input.focus();
                    }, 50);
                },

                // Fungsi jika menjawab
                nextQuestion() {
                    const currentQ = this.questions[this.currentIndex];
                    // Harus ada jawaban untuk tombol lanjut
                    if (this.answers[currentQ.id] === undefined || this.answers[currentQ.id] === '') {
                        this.focusInput();
                        // Beri animasi getar / peringatan (opsional)
                        const input = document.getElementById('input-' + this.currentIndex);
                        if(input) {
                            input.classList.add('animate-shake');
                            setTimeout(() => input.classList.remove('animate-shake'), 500);
                        }
                        return;
                    }

                    this.removeSkipped(currentQ.id); // Hapus dari daftar skipped jika sudah dijawab

                    if (this.currentIndex < this.questions.length - 1) {
                        this.currentIndex++;
                        this.focusInput();
                    } else {
                        this.finishExam();
                    }
                },

                // Fungsi tombol khusus lewati
                skipQuestion() {
                    const currentQ = this.questions[this.currentIndex];
                    // Tambahkan ke array skipped jika belum ada dan jawaban kosong
                    if (!this.skipped.includes(currentQ.id) && (this.answers[currentQ.id] === undefined || this.answers[currentQ.id] === '')) {
                        this.skipped.push(currentQ.id);
                    }

                    if (this.currentIndex < this.questions.length - 1) {
                        this.currentIndex++;
                        this.focusInput();
                    }
                },

                // Hapus tanda lewati jika mulai mengetik
                removeSkipped(id) {
                    const index = this.skipped.indexOf(id);
                    if (index > -1) {
                        this.skipped.splice(index, 1);
                    }
                },

                prevQuestion() {
                    if (this.currentIndex > 0) {
                        this.currentIndex--;
                        this.focusInput();
                    }
                },

                finishExam() {
                    let unanswered = 0;
                    this.questions.forEach(q => {
                        if (this.answers[q.id] === undefined || this.answers[q.id] === '') {
                            unanswered++;
                        }
                    });

                    let textMsg = "Pastikan semua soal sudah dihitung dengan teliti!";
                    if (unanswered > 0) {
                        textMsg = `Ada ${unanswered} soal yang dilewati/belum dijawab. Yakin ingin mengumpulkan?`;
                    }

                    if (document.exitFullscreen) {
                        document.exitFullscreen().catch(()=>{});
                    }

                    Swal.fire({
                        title: 'Kumpulkan Ujian?',
                        text: textMsg,
                        icon: unanswered > 0 ? 'warning' : 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#10b981',
                        cancelButtonColor: '#cbd5e1',
                        confirmButtonText: 'Ya, Kumpulkan',
                        cancelButtonText: 'Cek Kembali',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.forceSubmit();
                        } else {
                            let elem = document.documentElement;
                            if (elem.requestFullscreen) {
                                elem.requestFullscreen().catch(()=>{});
                            }
                            this.focusInput();
                        }
                    });
                },

                forceSubmit() {
                    clearInterval(this.timerInterval);
                    document.getElementById('math-submit-form').submit();
                }
            }));
        });
    </script>

    {{-- Tambahkan animasi shake kecil untuk validasi error di CSS tailwind (opsional, via style) --}}
    <style>
        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-5px);
            }

            75% {
                transform: translateX(5px);
            }
        }

        .animate-shake {
            animation: shake 0.2s ease-in-out 0s 2;
        }
    </style>
</x-app-layout>