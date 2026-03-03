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

        {{-- HEADER UJIAN --}}
        <div class="bg-white h-20 shadow-sm border-b border-slate-200 flex items-center justify-between px-6 shrink-0">
            <div class="flex items-center gap-4">
                <div
                    class="w-12 h-12 bg-indigo-600 text-white rounded-xl flex items-center justify-center text-xl shadow-lg rotate-3">
                    <i class="fas fa-calculator -rotate-3"></i>
                </div>
                <div>
                    <h1 class="font-black text-lg text-slate-800 uppercase tracking-widest">Tes Matematika</h1>
                    <p class="text-xs font-bold text-slate-400">Soal <span x-text="currentIndex + 1"></span> dari {{
                        $exam->total_questions }}</p>
                </div>
            </div>

            <div class="bg-slate-900 text-white px-6 py-2.5 rounded-2xl font-mono font-bold text-2xl shadow-lg flex items-center gap-3 transition-colors"
                :class="timeLeft <= 60 ? 'bg-rose-600 animate-pulse' : ''">
                <i class="fas fa-stopwatch text-sm opacity-50"></i>
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
                class="bg-white rounded-[3rem] shadow-2xl shadow-indigo-100 border border-slate-100 text-center w-full max-w-3xl relative z-10 min-h-[450px] flex items-center justify-center overflow-hidden">

                <template x-for="(q, index) in questions" :key="q.id">
                    <div x-show="currentIndex === index" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 transform scale-95 translate-y-4"
                        x-transition:enter-end="opacity-100 transform scale-100 translate-y-0"
                        class="w-full p-10 md:p-16" x-cloak>

                        <div class="text-indigo-500 font-black tracking-widest text-sm mb-6 uppercase">
                            Hitunglah hasil dari operasi di bawah ini:
                        </div>

                        <div
                            class="text-7xl md:text-8xl font-black text-slate-800 flex items-center justify-center gap-6 md:gap-10 mb-12">
                            <span x-text="q.num1"></span>
                            <span x-html="getOperatorIcon(q.operator)"
                                class="text-indigo-500 text-6xl md:text-7xl bg-indigo-50 w-24 h-24 md:w-32 md:h-32 rounded-full flex items-center justify-center shadow-inner"></span>
                            <span x-text="q.num2"></span>
                            <span class="text-slate-300">=</span>
                        </div>

                        <div>
                            <input type="number" :id="'input-' + index" x-model="answers[q.id]"
                                @keydown.enter="nextQuestion()" placeholder="?"
                                class="w-48 md:w-64 text-center text-5xl md:text-6xl font-black text-indigo-700 bg-slate-50 border-4 border-slate-200 focus:border-indigo-500 focus:ring-0 rounded-3xl py-6 transition-all shadow-inner placeholder-slate-300">

                            <p class="text-slate-400 font-bold text-sm mt-4">
                                <i class="fas fa-keyboard"></i> Ketik jawaban lalu tekan <strong>ENTER</strong>
                            </p>
                        </div>
                    </div>
                </template>

            </div>
        </div>

        {{-- NAVIGASI BAWAH --}}
        <div
            class="bg-white h-24 shadow-[0_-5px_30px_rgba(0,0,0,0.03)] border-t border-slate-200 flex items-center justify-between px-6 sm:px-12 shrink-0 relative z-20">
            <button @click="prevQuestion()" :disabled="currentIndex === 0"
                class="px-8 py-4 rounded-2xl font-black bg-slate-100 text-slate-500 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-200 transition-colors flex items-center gap-3">
                <i class="fas fa-arrow-left"></i> KEMBALI
            </button>

            <div class="hidden md:block flex-1 max-w-md mx-8">
                <div class="h-3 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-indigo-500 transition-all duration-500"
                        :style="`width: ${((currentIndex + 1) / questions.length) * 100}%`"></div>
                </div>
            </div>

            <button @click="nextQuestion()"
                class="px-10 py-4 rounded-2xl font-black text-white shadow-xl transition-transform hover:-translate-y-1 flex items-center gap-3"
                :class="currentIndex === questions.length - 1 ? 'bg-emerald-500 shadow-emerald-200 hover:bg-emerald-600' : 'bg-indigo-600 shadow-indigo-200 hover:bg-indigo-700'">
                <span x-text="currentIndex === questions.length - 1 ? 'SELESAI & KUMPULKAN' : 'LANJUT'"></span>
                <i class="fas"
                    :class="currentIndex === questions.length - 1 ? 'fa-check-double' : 'fa-arrow-right'"></i>
            </button>
        </div>

        <form id="math-submit-form" action="{{ route('student.math.submit', $exam->id) }}" method="POST" class="hidden">
            @csrf
            <input type="hidden" name="answers" :value="JSON.stringify(answers)">
        </form>

    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('mathExamRunner', (questions, initialTime) => ({
                questions: questions,
                currentIndex: 0,
                timeLeft: parseInt(initialTime),
                answers: {},
                timerInterval: null,

                init() {
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
                    // Mencegah lanjut jika input kosong
                    const currentQ = this.questions[this.currentIndex];
                    if (this.answers[currentQ.id] === undefined || this.answers[currentQ.id] === '') {
                        this.focusInput();
                        return; // Hentikan fungsi
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