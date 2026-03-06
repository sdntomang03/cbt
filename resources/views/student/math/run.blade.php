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
            <div class="absolute inset-0 pointer-events-none opacity-[0.03]">
                <i class="fas fa-shield-alt absolute top-20 left-10 text-9xl"></i>
                <i class="fas fa-balance-scale absolute bottom-10 right-10 text-9xl"></i>
            </div>

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
                class="bg-white h-20 shadow-sm border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 shrink-0">
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

            {{-- AREA SOAL (FLEKSIBEL DI TENGAH) --}}
            <div class="flex-1 flex flex-col items-center justify-center p-4 sm:p-6 relative">

                <div class="absolute inset-0 overflow-hidden pointer-events-none opacity-[0.03]">
                    <i class="fas fa-plus absolute top-10 left-10 text-9xl"></i>
                    <i class="fas fa-divide absolute bottom-10 right-10 text-9xl"></i>
                    <i class="fas fa-times absolute top-1/4 right-1/4 text-8xl"></i>
                    <i class="fas fa-minus absolute bottom-1/4 left-1/4 text-8xl"></i>
                </div>

                <div
                    class="bg-white rounded-[2rem] sm:rounded-[3rem] shadow-2xl shadow-indigo-100 border border-slate-100 text-center w-full max-w-3xl relative z-10 min-h-[350px] md:min-h-[450px] flex items-center justify-center overflow-hidden">

                    <template x-for="(q, index) in questions" :key="q.id">
                        <div x-show="currentIndex === index" x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 transform scale-95 translate-y-4"
                            x-transition:enter-end="opacity-100 transform scale-100 translate-y-0"
                            class="w-full p-6 sm:p-10 md:p-16" x-cloak>

                            <div class="text-indigo-500 font-black tracking-widest text-xs sm:text-sm mb-6 uppercase">
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
                                    @keydown.enter="nextQuestion()" placeholder="?"
                                    class="w-32 sm:w-48 md:w-64 text-center text-4xl sm:text-5xl md:text-6xl font-black text-indigo-700 bg-slate-50 border-4 border-slate-200 focus:border-indigo-500 focus:ring-0 rounded-2xl sm:rounded-3xl py-4 sm:py-6 transition-all shadow-inner placeholder-slate-300">

                                <p class="text-slate-400 font-bold text-xs sm:text-sm mt-4 hidden sm:block">
                                    <i class="fas fa-keyboard"></i> Ketik jawaban lalu tekan <strong>ENTER</strong>
                                </p>
                            </div>
                        </div>
                    </template>

                </div>
            </div>

            {{-- NAVIGASI BAWAH (MOBILE & DESKTOP) --}}
            <div
                class="bg-white h-auto sm:h-24 py-4 sm:py-0 shadow-[0_-10px_40px_rgba(0,0,0,0.05)] border-t border-slate-200 flex flex-row items-center justify-between px-4 sm:px-12 shrink-0 relative z-20 gap-3 sm:gap-0">

                {{-- Tombol Kembali --}}
                <button @click="prevQuestion()" :disabled="currentIndex === 0"
                    class="flex-1 sm:flex-none w-1/2 sm:w-auto px-4 sm:px-8 py-3.5 sm:py-4 rounded-xl sm:rounded-2xl font-black bg-slate-100 text-slate-500 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-200 transition-colors flex items-center justify-center gap-2 sm:gap-3 text-xs sm:text-base tracking-wide">
                    <i class="fas fa-arrow-left"></i>
                    <span>KEMBALI</span>
                </button>

                {{-- Progress Bar (Hanya muncul di Layar Besar) --}}
                <div class="hidden md:block flex-1 max-w-md mx-8">
                    <div class="h-3 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-indigo-500 transition-all duration-500"
                            :style="`width: ${((currentIndex + 1) / questions.length) * 100}%`"></div>
                    </div>
                </div>

                {{-- Tombol Lanjut / Selesai --}}
                <button @click="nextQuestion()"
                    class="flex-1 sm:flex-none w-1/2 sm:w-auto px-4 sm:px-10 py-3.5 sm:py-4 rounded-xl sm:rounded-2xl font-black text-white shadow-xl transition-transform hover:-translate-y-1 flex items-center justify-center gap-2 sm:gap-3 text-xs sm:text-base tracking-wide"
                    :class="currentIndex === questions.length - 1 ? 'bg-emerald-500 shadow-emerald-200 hover:bg-emerald-600' : 'bg-indigo-600 shadow-indigo-200 hover:bg-indigo-700'">

                    {{-- Teks berubah sesuai index dan ukuran layar --}}
                    <span x-show="currentIndex !== questions.length - 1">LANJUT</span>
                    <span x-show="currentIndex === questions.length - 1" class="hidden sm:inline">SELESAI &
                        KUMPULKAN</span>
                    <span x-show="currentIndex === questions.length - 1" class="sm:hidden">SELESAI</span>

                    <i class="fas"
                        :class="currentIndex === questions.length - 1 ? 'fa-check-double' : 'fa-arrow-right'"></i>
                </button>
            </div>
        </div>

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
                timerInterval: null,

                init() {
                    // Tunggu user klik tombol mulai
                },

                // FUNGSI MEMULAI UJIAN DAN MEMAKSA FULLSCREEN
                startExam() {
                    this.hasStarted = true;

                    // Request Fullscreen pada elemen document (layar penuh)
                    let elem = document.documentElement;
                    if (elem.requestFullscreen) {
                        elem.requestFullscreen().catch(err => console.warn("Fullscreen diblokir oleh browser:", err));
                    } else if (elem.webkitRequestFullscreen) { /* Safari */
                        elem.webkitRequestFullscreen();
                    } else if (elem.msRequestFullscreen) { /* IE11 */
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

                            // Jika ujian habis waktu, otomatis keluar dari fullscreen sebelum disubmit
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

                nextQuestion() {
                    const currentQ = this.questions[this.currentIndex];
                    if (this.answers[currentQ.id] === undefined || this.answers[currentQ.id] === '') {
                        this.focusInput();
                        return;
                    }

                    if (this.currentIndex < this.questions.length - 1) {
                        this.currentIndex++;
                        this.focusInput();
                    } else {
                        this.finishExam();
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
                        textMsg = `Ada ${unanswered} soal yang belum dijawab. Yakin ingin mengumpulkan?`;
                    }

                    // Opsional: Keluar dari fullscreen saat SweetAlert muncul agar tombol popup mudah diklik
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
                            // Jika batal kumpul (cek lagi), masuk fullscreen lagi
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
</x-app-layout>