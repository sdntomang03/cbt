<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $pageTitle ?? 'CBT Pro - Platform Tryout Online' }}</title>
    <meta name="description"
        content="{{ $metaDescription ?? 'Platform simulasi ujian dan tryout online terbaik dengan format CBT.' }}">

    @if(isset($metaKeywords))
    <meta name="keywords" content="{{ $metaKeywords }}">
    @endif

    @if(request()->hasAny(['page', 'level', 'subject']))
    <meta name="robots" content="noindex, follow">
    <link rel="canonical" href="{{ url()->current() }}" />
    @else
    <link rel="canonical" href="{{ $canonicalUrl ?? url()->current() }}" />
    @endif

    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $pageTitle ?? 'CBT Pro' }}">
    <meta property="og:description" content="{{ $metaDescription ?? 'Platform simulasi ujian dan tryout online.' }}">
    <meta property="og:url" content="{{ url()->current() }}">
    @if(isset($metaImage))
    <meta property="og:image" content="{{ asset('storage/' . $metaImage) }}">
    @endif

    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>

    <link href="https://fonts.bunny.net/css?family=nunito:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    <link rel="preload" as="style" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
        onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </noscript>

    @if(request()->is('tryout/detail/*') || request()->is('tryout/run/*') ||
    request()->is('tryout/result/*'))
    <link rel="preload" as="style" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css"
        onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css">
    </noscript>
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/contrib/auto-render.min.js"></script>
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Nunito', sans-serif;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body
    class="text-slate-800 antialiased bg-slate-50 min-h-screen flex flex-col selection:bg-indigo-500 selection:text-white">

    <nav x-data="{ mobileMenuOpen: false }"
        class="bg-white/80 backdrop-blur-md border-b border-slate-200 shadow-sm sticky top-0 z-50 transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20">

                <div class="flex items-center">
                    <a href="/" aria-label="Beranda CBT Pro" class="flex items-center gap-3 group">
                        <div
                            class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-indigo-200 group-hover:scale-105 transition-transform duration-300">
                            <i class="fas fa-graduation-cap text-2xl"></i>
                        </div>
                        <span class="font-black text-2xl tracking-tight text-slate-800 hidden sm:block">
                            CBT<span class="text-indigo-600">Pro</span>
                        </span>
                    </a>
                </div>

                <div class="hidden sm:flex sm:items-center sm:gap-8">
                    <a href="/" class="text-sm font-bold text-slate-500 hover:text-indigo-600 transition">Beranda</a>
                    <a href="{{ route('public.exams.index') }}"
                        class="text-sm font-black text-indigo-600 border-b-2 border-indigo-600 py-7">Tryout</a>

                    @auth
                    <a href="{{ route('dashboard') }}"
                        class="bg-indigo-50 text-indigo-600 border border-indigo-100 px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-indigo-100 hover:scale-105 transition-all">
                        <i class="fas fa-chart-pie mr-1"></i> Dashboard
                    </a>
                    @else
                    <a href="{{ route('login') }}"
                        class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl text-sm font-bold hover:bg-indigo-700 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                        Masuk <i class="fas fa-sign-in-alt ml-1"></i>
                    </a>
                    @endauth
                </div>

                <div class="-mr-2 flex items-center sm:hidden">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" aria-label="Buka atau tutup menu navigasi"
                        class="inline-flex items-center justify-center p-3 rounded-xl text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition focus:outline-none">
                        <i class="fas fa-bars text-2xl" x-show="!mobileMenuOpen"></i>
                        <i class="fas fa-times text-2xl" x-show="mobileMenuOpen" x-cloak></i>
                    </button>
                </div>
            </div>
        </div>

        <div x-show="mobileMenuOpen" x-collapse x-cloak class="sm:hidden border-t border-slate-100 bg-white">
            <div class="pt-3 pb-4 space-y-2 px-4 shadow-xl">
                <a href="/"
                    class="block px-4 py-3 rounded-xl text-base font-bold text-slate-600 hover:bg-slate-50 hover:text-indigo-600 transition">Beranda</a>
                <a href="{{ route('public.exams.index') }}"
                    class="block px-4 py-3 rounded-xl text-base font-black text-indigo-700 bg-indigo-50 transition">Tryout
                    Publik</a>

                <div class="border-t border-slate-100 my-2 pt-2"></div>

                @auth
                <a href="{{ route('dashboard') }}"
                    class="block text-center bg-indigo-50 text-indigo-600 border border-indigo-100 px-4 py-3 rounded-xl text-base font-bold hover:bg-indigo-100 transition">Dashboard</a>
                @else
                <a href="{{ route('login') }}"
                    class="block text-center bg-indigo-600 text-white px-4 py-3 rounded-xl text-base font-bold hover:bg-indigo-700 transition">Masuk
                    ke Sistem</a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="flex-grow w-full">
        {{ $slot }}
    </main>

    <footer class="bg-white border-t border-slate-200 mt-auto">
        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">

                <div class="flex items-center gap-2 grayscale opacity-70">
                    <i class="fas fa-graduation-cap text-slate-800 text-xl"></i>
                    <span class="font-black text-lg tracking-tight text-slate-800">CBTPro</span>
                </div>

                <p class="text-sm font-bold text-slate-500 text-center">
                    &copy; 2026 SDN Tomang 03. All rights reserved.
                </p>


            </div>
        </div>
    </footer>

</body>

</html>