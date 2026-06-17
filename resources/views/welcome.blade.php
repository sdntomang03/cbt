<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- SEO OPTIMIZATION TAGS --}}
    <title>CBT Pro - Platform Try Out TKA & Ulangan Harian SD Online</title>
    <meta name="description"
        content="Platform ujian online (CBT) gratis untuk simulasi Try Out TKA SD dan ulangan harian. Tingkatkan literasi dan numerasi siswa Sekolah Dasar dengan mudah.">
    <meta name="keywords"
        content="TKA SD, Try Out TKA, Ulangan Harian SD, Ujian Online SD, CBT Sekolah Dasar, Soal Numerasi SD, Literasi SD">
    <meta name="robots" content="index, follow">

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

        .typewriter-cursor {
            display: inline-block;
            width: 3px;
            height: 1.2em;
            background-color: #4f46e5;
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

    {{-- Latar Belakang Animasi --}}
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

    {{-- Navigasi --}}
    <nav x-data="{ mobileMenuOpen: false }" class="relative z-50 w-full glass-panel border-b-0 shadow-sm">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">

                <div class="flex items-center gap-8">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-indigo-200">
                            <i class="fas fa-graduation-cap text-xl"></i>
                        </div>
                        <span class="font-black text-2xl tracking-tight text-slate-800">
                            CBT<span class="text-indigo-600">Pro</span>
                        </span>
                    </div>

                    <div class="hidden md:flex items-center gap-6 border-l-2 border-slate-100 pl-8">
                        <a href="{{ route('hitung.index') }}"
                            class="font-bold text-slate-600 hover:text-indigo-600 transition flex items-center gap-2 group">
                            <span
                                class="text-xl group-hover:scale-110 group-hover:-rotate-3 transition-transform duration-300">🧮</span>
                            Kawan Hitung
                        </a>
                        <a href="{{ route('baca.index') }}"
                            class="font-bold text-slate-600 hover:text-indigo-600 transition flex items-center gap-2 group">
                            <span
                                class="text-xl group-hover:scale-110 group-hover:-rotate-3 transition-transform duration-300">📖</span>
                            Kawan Baca
                        </a>
                    </div>
                </div>

                <div class="hidden md:flex items-center gap-4">
                    @auth
                    <a href="{{ url('/dashboard') }}"
                        class="font-bold text-slate-600 hover:text-indigo-600 transition">Dashboard Saya</a>
                    @else
                    <a href="{{ route('login') }}"
                        class="font-bold text-slate-600 hover:text-indigo-600 transition">Masuk</a>
                    @if (Route::has('register'))
                    <a href="{{ route('register') }}"
                        class="bg-slate-900 hover:bg-black text-white px-6 py-2.5 rounded-full font-bold shadow-lg shadow-slate-200 transition transform hover:-translate-y-0.5">Daftar
                        Sekarang</a>
                    @endif
                    @endauth
                </div>

                <div class="flex md:hidden items-center">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" aria-label="Buka atau tutup menu navigasi"
                        class="text-slate-600 hover:text-indigo-600 focus:outline-none p-2">
                        <i class="fas fa-bars text-2xl" x-show="!mobileMenuOpen"></i>
                        <i class="fas fa-times text-2xl" x-show="mobileMenuOpen" x-cloak></i>
                    </button>
                </div>
            </div>
        </div>

        <div x-show="mobileMenuOpen" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2" x-cloak
            class="md:hidden absolute top-20 left-0 w-full bg-white/95 backdrop-blur-md border-t border-slate-100 shadow-xl rounded-b-2xl pb-4">
            <div class="flex flex-col px-6 pt-4 pb-2 space-y-4">
                <a href="{{ route('hitung.index') }}"
                    class="font-bold text-slate-700 hover:text-indigo-600 flex items-center gap-3 bg-slate-50 p-3 rounded-xl"><span
                        class="text-2xl">🧮</span> Kawan Hitung</a>
                <a href="{{ route('baca.index') }}"
                    class="font-bold text-slate-700 hover:text-indigo-600 flex items-center gap-3 bg-slate-50 p-3 rounded-xl"><span
                        class="text-2xl">📖</span> Kawan Baca</a>
                <hr class="border-slate-100">
                @auth
                <a href="{{ url('/dashboard') }}"
                    class="font-bold text-center bg-indigo-50 text-indigo-600 py-3 rounded-xl">Dashboard Saya</a>
                @else
                <a href="{{ route('login') }}" class="font-bold text-center text-slate-600 py-2">Masuk</a>
                @if (Route::has('register'))
                <a href="{{ route('register') }}"
                    class="bg-indigo-600 text-white py-3 rounded-xl font-bold text-center shadow-lg shadow-indigo-200">Daftar
                    Sekarang</a>
                @endif
                @endauth
            </div>
        </div>
    </nav>

    {{-- KONTEN UTAMA HERO (SEO H1) --}}
    <main
        class="relative z-10 max-w-7xl mx-auto px-6 lg:px-8 pt-10 pb-20 flex flex-col lg:flex-row items-center gap-16 min-h-[calc(100vh-80px)]">

        <div class="flex-1 text-center lg:text-left pt-10 lg:pt-0">
            <div
                class="inline-block px-4 py-1.5 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-600 font-bold text-xs uppercase tracking-widest mb-6">
                <i class="fas fa-rocket mr-2"></i> Evaluasi Belajar Siswa SD
            </div>

            {{-- Tag H1 yang Kuat untuk SEO --}}
            <h1 class="text-5xl lg:text-7xl font-black text-slate-900 leading-[1.1] mb-6 tracking-tight">
                Simulasi TKA & <br>
                Ulangan Harian SD <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-cyan-500"
                    x-data="typewriter(['Berbasis CBT.', 'Sesuai Kurikulum.', 'Lebih Interaktif.'])" x-init="start()">
                    <span x-text="text"></span><span class="typewriter-cursor"></span>
                </span>
            </h1>

            <p class="text-lg text-slate-500 mb-10 max-w-2xl mx-auto lg:mx-0 font-medium leading-relaxed">
                Persiapkan siswa Sekolah Dasar menghadapi <strong>Tes Kemampuan Akademik (TKA)</strong> dan
                <strong>ulangan harian</strong> melalui platform ujian online interaktif. Dilengkapi modul literasi dan
                numerasi untuk memperkuat pondasi belajar anak.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 mt-8">
                <a href="{{ route('public.exams.index') }}"
                    class="group w-full sm:w-auto px-8 py-4 rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-bold shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 flex items-center justify-center gap-3 transition-all duration-300 transform hover:-translate-y-1">
                    Ikuti Try Out TKA
                    <i class="fas fa-play text-sm transition-transform duration-300 group-hover:scale-125"></i>
                </a>

                @auth
                <a href="{{ url('/dashboard') }}"
                    class="group w-full sm:w-auto px-8 py-4 rounded-2xl bg-white text-slate-700 hover:text-indigo-700 border-2 border-slate-200 hover:border-indigo-200 hover:bg-indigo-50 font-bold flex items-center justify-center gap-3 transition-all duration-300 transform hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-200/50">
                    Buka Dashboard
                    <i class="fas fa-arrow-right transition-transform duration-300 group-hover:translate-x-1.5"></i>
                </a>
                @else
                <a href="{{ route('login') }}"
                    class="group w-full sm:w-auto px-8 py-4 rounded-2xl bg-white text-slate-700 hover:text-indigo-700 border-2 border-slate-200 hover:border-indigo-200 hover:bg-indigo-50 font-bold flex items-center justify-center gap-3 transition-all duration-300 transform hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-200/50">
                    Login Akun Siswa
                    <i
                        class="fas fa-user-plus text-slate-400 group-hover:text-indigo-500 transition-colors duration-300"></i>
                </a>
                @endauth
            </div>
        </div>

        {{-- Hero Illustration --}}
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
                            <i class="fas fa-check"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Status TKA</p>
                            <p class="text-sm font-black text-slate-700">Tuntas</p>
                        </div>
                    </div>
                </div>
                <div
                    class="absolute right-[-5%] top-[10%] w-32 bg-white/90 backdrop-blur-md p-4 rounded-2xl shadow-xl border border-white z-30 animate-float">
                    <p class="text-[10px] font-bold text-slate-400 uppercase text-center mb-1">Skor Ulangan</p>
                    <p class="text-3xl font-black text-indigo-600 text-center">95.5</p>
                </div>
            </div>
        </div>
    </main>

    {{-- FITUR UNGGULAN (SEO H2 & H3) --}}
    <section id="fitur" class="py-24 bg-white relative z-10">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">

            <div class="text-center mb-16">
                <h2 class="text-3xl lg:text-4xl font-black text-slate-900 mb-4">Modul Penunjang Sukses TKA SD</h2>
                <p class="text-slate-500 max-w-2xl mx-auto text-lg">Platform kami mengintegrasikan ujian dengan latihan
                    dasar untuk menajamkan kemampuan logika, analisis, dan membaca siswa Sekolah Dasar.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                <div
                    class="bg-slate-50 p-10 rounded-[2.5rem] border border-slate-100 hover:shadow-2xl transition group">
                    <div
                        class="w-16 h-16 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-3xl mb-8 group-hover:-rotate-6 transition">
                        🧮</div>
                    <h3 class="text-2xl font-black text-slate-900 mb-4">Kawan Hitung (Numerasi SD)</h3>
                    <p class="text-slate-500 mb-8 font-medium leading-relaxed">
                        Kecepatan berhitung adalah kunci sukses mengerjakan soal numerasi pada TKA. Latih kemampuan
                        aritmatika dasar siswa SD secara rutin dengan metode yang seru, interaktif, dan tidak
                        membosankan!
                    </p>
                    <a href="{{ route('hitung.index') }}"
                        class="inline-flex items-center gap-3 bg-white border-2 border-blue-100 hover:border-blue-500 text-blue-600 px-6 py-3 rounded-xl font-black transition">
                        Latihan Numerasi <i class="fas fa-chevron-right text-xs"></i>
                    </a>
                </div>

                <div
                    class="bg-slate-50 p-10 rounded-[2.5rem] border border-slate-100 hover:shadow-2xl transition group">
                    <div
                        class="w-16 h-16 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center text-3xl mb-8 group-hover:rotate-6 transition">
                        📖</div>
                    <h3 class="text-2xl font-black text-slate-900 mb-4">Kawan Baca (Literasi SD)</h3>
                    <p class="text-slate-500 mb-8 font-medium leading-relaxed">
                        Soal cerita pada ulangan harian membutuhkan tingkat pemahaman bacaan yang kuat. Tingkatkan
                        kemampuan literasi siswa agar mampu menangkap inti informasi dari teks literasi dengan cepat dan
                        tepat.
                    </p>
                    <a href="{{ route('baca.index') }}"
                        class="inline-flex items-center gap-3 bg-white border-2 border-amber-100 hover:border-amber-500 text-amber-600 px-6 py-3 rounded-xl font-black transition">
                        Latihan Literasi <i class="fas fa-chevron-right text-xs"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

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
                start() { this.type(); },
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
                    if (!this.isDeleting && this.text === currentWord) {
                        speed = this.delayBetweenWords;
                        this.isDeleting = true;
                    }
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

    @if(session('success') || session('error'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" {{-- Otomatis hilang dalam
        5 detik --}} x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-10" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-10"
        class="fixed bottom-6 right-6 z-[100] max-w-sm w-full bg-white shadow-2xl rounded-2xl border-l-4 p-4 flex items-start gap-4 {{ session('success') ? 'border-emerald-500' : 'border-rose-500' }}"
        x-cloak>

        {{-- Ikon --}}
        <div class="flex-shrink-0 mt-0.5">
            @if(session('success'))
            <i class="fas fa-check-circle text-emerald-500 text-xl"></i>
            @else
            <i class="fas fa-exclamation-circle text-rose-500 text-xl"></i>
            @endif
        </div>

        {{-- Pesan Teks --}}
        <div class="flex-1">
            <h3 class="text-sm font-bold text-slate-800">
                {{ session('success') ? 'Berhasil!' : 'Akses Ditolak!' }}
            </h3>
            <p class="text-sm text-slate-500 mt-1 leading-snug">
                {{ session('success') ?? session('error') }}
            </p>
        </div>

        {{-- Tombol Tutup Manual --}}
        <button @click="show = false" class="text-slate-400 hover:text-slate-600 transition-colors p-1">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif
</body>

</html>