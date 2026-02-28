<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'CBT Modern') }} - Ujian Online Masa Kini</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=nunito:400,600,700,800,900" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Nunito', 'sans-serif'] },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'float-delayed': 'float 6s ease-in-out 3s infinite',
                        'blob': 'blob 7s infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-20px)' },
                        },
                        blob: {
                            '0%': { transform: 'translate(0px, 0px) scale(1)' },
                            '33%': { transform: 'translate(30px, -50px) scale(1.1)' },
                            '66%': { transform: 'translate(-20px, 20px) scale(0.9)' },
                            '100%': { transform: 'translate(0px, 0px) scale(1)' },
                        }
                    }
                }
            }
        }
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        /* Typewriter Cursor */
        .typewriter-cursor {
            display: inline-block;
            width: 3px;
            height: 1.2em;
            background-color: #4f46e5;
            /* indigo-600 */
            vertical-align: text-bottom;
            animation: blink 1s step-end infinite;
        }

        @keyframes blink {
            50% {
                opacity: 0;
            }
        }
    </style>
</head>

<body
    class="font-sans antialiased bg-slate-50 text-slate-800 overflow-x-hidden relative selection:bg-indigo-500 selection:text-white">

    <div class="fixed inset-0 w-full h-full pointer-events-none overflow-hidden z-0">
        <div
            class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-indigo-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob">
        </div>
        <div
            class="absolute top-[20%] right-[-5%] w-96 h-96 bg-cyan-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000">
        </div>
        <div
            class="absolute bottom-[-20%] left-[20%] w-[30rem] h-[30rem] bg-purple-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-4000">
        </div>
    </div>

    <nav class="relative z-50 w-full glass-panel border-b-0 shadow-sm">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-indigo-200">
                        <i class="fas fa-graduation-cap text-xl"></i>
                    </div>
                    <span class="font-black text-2xl tracking-tight text-slate-800">
                        CBT<span class="text-indigo-600">Pro</span>
                    </span>
                </div>

                @if (Route::has('login'))
                <div class="flex items-center gap-4">
                    @auth
                    <a href="{{ url('/dashboard') }}" class="font-bold text-slate-600 hover:text-indigo-600 transition">
                        Dashboard Saya
                    </a>
                    @else
                    <a href="{{ route('login') }}"
                        class="font-bold text-slate-600 hover:text-indigo-600 transition hidden sm:block">
                        Masuk
                    </a>
                    @if (Route::has('register'))
                    <a href="{{ route('register') }}"
                        class="bg-slate-900 hover:bg-black text-white px-6 py-2.5 rounded-full font-bold shadow-lg shadow-slate-200 transition transform hover:-translate-y-0.5">
                        Daftar Sekarang
                    </a>
                    @endif
                    @endauth
                </div>
                @endif
            </div>
        </div>
    </nav>

    <main
        class="relative z-10 max-w-7xl mx-auto px-6 lg:px-8 pt-20 pb-32 flex flex-col lg:flex-row items-center gap-16 min-h-[calc(100vh-80px)]">

        <div class="flex-1 text-center lg:text-left pt-10 lg:pt-0">
            <div
                class="inline-block px-4 py-1.5 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-600 font-bold text-xs uppercase tracking-widest mb-6">
                <i class="fas fa-rocket mr-2"></i> Sistem Ujian Generasi Baru
            </div>

            <h1 class="text-5xl lg:text-7xl font-black text-slate-900 leading-[1.1] mb-6 tracking-tight">
                Ujian Online <br>
                Lebih Cerdas, <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-cyan-500"
                    x-data="typewriter(['Lebih Aman.', 'Lebih Interaktif.', 'Tanpa Hambatan.'])" x-init="start()">
                    <span x-text="text"></span><span class="typewriter-cursor"></span>
                </span>
            </h1>

            <p class="text-lg text-slate-500 mb-10 max-w-2xl mx-auto lg:mx-0 font-medium leading-relaxed">
                Platform Computer Based Test (CBT) yang dirancang untuk mendukung berbagai tipe soal kompleks,
                dilengkapi sistem anti-kecurangan cerdas, dan antarmuka yang disukai siswa.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                @auth
                <a href="{{ url('/dashboard') }}"
                    class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-black shadow-xl shadow-indigo-200 flex items-center justify-center gap-3 transition transform hover:-translate-y-1">
                    Buka Dashboard <i class="fas fa-arrow-right"></i>
                </a>
                @else
                <a href="{{ route('login') }}"
                    class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-black shadow-xl shadow-indigo-200 flex items-center justify-center gap-3 transition transform hover:-translate-y-1">
                    Mulai Ujian <i class="fas fa-play"></i>
                </a>
                <a href="#fitur"
                    class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-white text-slate-700 border-2 border-slate-200 hover:border-slate-300 font-bold flex items-center justify-center gap-3 transition">
                    Pelajari Fitur
                </a>
                @endauth
            </div>
        </div>

        <div class="flex-1 w-full relative max-w-lg lg:max-w-none">
            <div class="absolute inset-0 bg-gradient-to-tr from-indigo-100 to-cyan-50 rounded-full blur-3xl opacity-70">
            </div>

            <div class="relative z-10 w-full aspect-square md:aspect-[4/3] flex items-center justify-center">

                <div
                    class="absolute w-[80%] h-[70%] bg-white rounded-[2rem] shadow-2xl border border-slate-100 overflow-hidden animate-float p-6 flex flex-col z-20">
                    <div class="flex gap-2 mb-6">
                        <div class="w-3 h-3 rounded-full bg-rose-400"></div>
                        <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                        <div class="w-3 h-3 rounded-full bg-emerald-400"></div>
                    </div>
                    <div class="w-1/3 h-4 bg-slate-100 rounded-full mb-4"></div>
                    <div class="w-full h-8 bg-slate-50 rounded-xl mb-4"></div>
                    <div class="w-5/6 h-4 bg-slate-100 rounded-full mb-2"></div>
                    <div class="w-4/6 h-4 bg-slate-100 rounded-full mb-8"></div>

                    <div class="grid grid-cols-2 gap-4 mt-auto">
                        <div class="h-10 bg-indigo-50 rounded-xl"></div>
                        <div class="h-10 bg-indigo-600 rounded-xl"></div>
                    </div>
                </div>

                <div
                    class="absolute left-0 bottom-[20%] w-48 bg-white/90 backdrop-blur-md p-4 rounded-2xl shadow-xl border border-white z-30 animate-float-delayed">
                    <div class="flex items-center gap-3 mb-2">
                        <div
                            class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-500 flex items-center justify-center text-xs">
                            <i class="fas fa-check"></i></div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Status</p>
                            <p class="text-sm font-black text-slate-700">Ujian Selesai</p>
                        </div>
                    </div>
                </div>

                <div
                    class="absolute right-[-5%] top-[10%] w-32 bg-white/90 backdrop-blur-md p-4 rounded-2xl shadow-xl border border-white z-30 animate-float">
                    <p class="text-[10px] font-bold text-slate-400 uppercase text-center mb-1">Skor Akhir</p>
                    <p class="text-3xl font-black text-indigo-600 text-center">95.5</p>
                </div>

            </div>
        </div>
    </main>

    <script>
        function typewriter(words) {
            return {
                words: words,
                text: '',
                wordIndex: 0,
                charIndex: 0,
                isDeleting: false,
                typeSpeed: 100,
                deleteSpeed: 50,
                delayBetweenWords: 2000,

                start() {
                    this.type();
                },

                type() {
                    const currentWord = this.words[this.wordIndex];

                    if (this.isDeleting) {
                        this.text = currentWord.substring(0, this.charIndex - 1);
                        this.charIndex--;
                    } else {
                        this.text = currentWord.substring(0, this.charIndex + 1);
                        this.charIndex++;
                    }

                    let speed = this.isDeleting ? this.deleteSpeed : this.typeSpeed;

                    // Jika selesai mengetik 1 kata
                    if (!this.isDeleting && this.text === currentWord) {
                        speed = this.delayBetweenWords;
                        this.isDeleting = true;
                    }
                    // Jika selesai menghapus
                    else if (this.isDeleting && this.text === '') {
                        this.isDeleting = false;
                        this.wordIndex = (this.wordIndex + 1) % this.words.length;
                        speed = 500;
                    }

                    setTimeout(() => this.type(), speed);
                }
            }
        }
    </script>
</body>

</html>