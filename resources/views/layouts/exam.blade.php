<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'CBT Ujian') }} - Sedang Ujian</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=nunito:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/flowbite@4.0.1/dist/flowbite.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Nunito', sans-serif;
        }

        [x-cloak] {
            display: none !important;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* 1. Memaksa Text Alignment untuk Paragraf & Teks Biasa */
        .prose-custom .ql-align-center {
            text-align: center !important;
        }

        .prose-custom .ql-align-right {
            text-align: right !important;
        }

        .prose-custom .ql-align-justify {
            text-align: justify !important;
        }

        /* 2. Fix List (UL/OL): Agar titik/angka ikut bergeser ke tengah bersama teks */
        .prose-custom li.ql-align-center,
        .prose-custom li.ql-align-right {
            list-style-position: inside !important;
        }

        /* 3. Fix List (UL/OL): Menghapus padding kiri bawaan Tailwind agar benar-benar simetris di tengah */
        .prose-custom ul:has(> li.ql-align-center),
        .prose-custom ol:has(> li.ql-align-center) {
            padding-left: 0 !important;
        }

        /* 4. Fix Gambar (Images): Memaksa gambar yang berada di dalam baris 'center' atau memiliki class 'center' untuk ke tengah */
        .prose-custom .ql-align-center img,
        .prose-custom img.ql-align-center {
            display: block !important;
            margin-left: auto !important;
            margin-right: auto !important;
        }

        /* Tambahan opsional: Jika ada gambar yang diratakan kanan */
        .prose-custom .ql-align-right img,
        .prose-custom img.ql-align-right {
            display: block !important;
            margin-left: auto !important;
            margin-right: 0 !important;
        }

        /* Mencegah siswa menyeleksi teks soal (Anti-Copy) */
        .no-select {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
    </style>
    @stack('styles')
</head>

<body class="font-sans antialiased text-slate-800 bg-slate-100 overflow-hidden no-select"
    x-data="{ showPalette: false }" @resize.window="if(window.innerWidth >= 1024) showPalette = true">

    <div class="flex flex-col h-screen w-full">

        <header
            class="bg-white shadow-sm border-b border-slate-200 h-16 shrink-0 flex items-center justify-between px-4 lg:px-8 relative z-30">

            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-600 text-white rounded-lg flex items-center justify-center font-bold">
                    <i class="fas fa-laptop-code"></i>
                </div>
                <div class="hidden sm:block">
                    <h1 class="font-bold text-slate-800 leading-tight">Ujian Akhir Semester</h1>
                    <p class="text-xs text-slate-500 font-semibold">{{ $subject_name ?? 'Mata Pelajaran' }}</p>
                </div>
            </div>

            <div
                class="absolute left-1/2 transform -translate-x-1/2 flex items-center gap-2 bg-rose-50 border border-rose-200 text-rose-600 px-4 py-1.5 rounded-full font-bold shadow-sm">
                <i class="far fa-clock animate-pulse"></i>
                <span id="exam-timer" class="tracking-widest">{{ $time_left ?? '01:30:00' }}</span>
            </div>

            <div class="flex items-center gap-4">
                <div class="text-right hidden sm:block">
                    <div class="font-bold text-sm">{{ Auth::user()->name ?? 'Nama Siswa' }}</div>
                    <div class="text-xs text-slate-500">{{ Auth::user()->nisn ?? 'NISN/NIS' }}</div>
                </div>
                <div
                    class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-black border-2 border-indigo-200">
                    {{ substr(Auth::user()->name ?? 'S', 0, 1) }}
                </div>

                <button @click="showPalette = !showPalette"
                    class="lg:hidden text-slate-500 hover:text-indigo-600 focus:outline-none p-2 bg-slate-100 rounded-lg">
                    <i class="fas fa-th-large"></i>
                </button>
            </div>
        </header>

        <div class="flex-1 flex overflow-hidden relative">

            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 pb-24">
                <div class="max-w-4xl mx-auto">
                    {{ $slot }}
                </div>
            </main>

            <div x-show="showPalette" @click="showPalette = false" x-transition.opacity
                class="fixed inset-0 z-40 bg-slate-900/50 lg:hidden" x-cloak>
            </div>

            <aside :class="showPalette ? 'translate-x-0' : 'translate-x-full lg:translate-x-0'"
                class="fixed lg:static inset-y-0 right-0 z-50 w-80 bg-white border-l border-slate-200 shadow-2xl lg:shadow-none transition-transform duration-300 ease-in-out flex flex-col h-[calc(100vh-4rem)] lg:h-auto mt-16 lg:mt-0">

                <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                    <h3 class="font-bold text-slate-700"><i class="fas fa-th mr-2 text-indigo-500"></i> Navigasi Soal
                    </h3>
                    <button @click="showPalette = false" class="lg:hidden text-slate-400 hover:text-rose-500">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <div class="p-4 overflow-y-auto flex-1">
                    {{ $palette ?? '' }}
                </div>

                <div class="p-4 border-t border-slate-200 bg-slate-50">
                    <h4 class="text-xs font-bold text-slate-500 mb-3 uppercase tracking-wider">Keterangan</h4>
                    <div class="grid grid-cols-2 gap-2 text-xs font-semibold">
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 rounded bg-emerald-500"></div> Dijawab
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 rounded bg-white border border-slate-300"></div> Belum
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 rounded bg-amber-400"></div> Ragu-ragu
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 rounded border-2 border-indigo-500"></div> Posisi Saat Ini
                        </div>
                    </div>

                    <button type="button"
                        class="mt-6 w-full py-3 px-4 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl shadow-lg shadow-rose-200 transition text-sm">
                        <i class="fas fa-flag-checkered mr-2"></i> Selesai Ujian
                    </button>
                </div>
            </aside>

        </div>
    </div>

    @stack('scripts')
</body>

</html>