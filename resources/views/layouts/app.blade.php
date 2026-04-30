<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'CBT Pro') }}</title>

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
    </style>
    @stack('styles')
</head>

<!-- Alpine JS: Setingan default. True (Terbuka) di Laptop, False (Tertutup) di HP -->

<body class="font-sans antialiased text-slate-800 bg-slate-50 overflow-hidden"
    x-data="{ sidebarOpen: window.innerWidth >= 1024 }" @resize.window="sidebarOpen = window.innerWidth >= 1024">

    <!-- WRAPPER UTAMA: Sistem 2 Kolom (Flex Row) -->
    <div class="flex h-screen w-full">

        <!-- Latar Gelap untuk HP saat Sidebar Terbuka -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity.duration.300ms
            class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm lg:hidden" x-cloak>
        </div>

        <!-- KOLOM 1: ASIDE (SIDEBAR) -->
        <!-- Logika: Di HP mainkan translate-x (geser), Di Laptop mainkan width (lebar) -->
        <aside :class="sidebarOpen ? 'translate-x-0 lg:w-72' : '-translate-x-full lg:translate-x-0 lg:w-0'"
            class="fixed inset-y-0 left-0 z-50 w-72 bg-white border-r border-slate-200 transition-all duration-300 ease-in-out lg:static lg:flex-shrink-0 overflow-hidden shadow-2xl lg:shadow-none flex flex-col">

            <!-- Dibungkus div w-72 agar teks menu tidak gepeng saat sidebar mengecil -->
            <div class="w-72 h-full flex flex-col">
                @include('layouts.navigation')
            </div>
        </aside>

        <!-- KOLOM 2: MAIN AREA -->
        <div class="flex-1 flex flex-col min-w-0 bg-slate-50 h-screen overflow-hidden">

            <!-- TOPBAR (Di dalam kolom 2) -->
            <header
                class="bg-white/90 backdrop-blur-md border-b border-slate-200 h-20 shrink-0 flex items-center justify-between px-4 sm:px-6 lg:px-8 shadow-sm relative z-30">

                <!-- Tombol Hamburger -->
                <button @click="sidebarOpen = !sidebarOpen"
                    class="text-slate-500 hover:text-indigo-600 focus:outline-none p-2 rounded-lg hover:bg-slate-100 transition">
                    <i class="fas fa-bars text-2xl"></i>
                </button>

                <!-- Menu Profil Kanan -->
                <div class="flex items-center gap-4 ml-auto">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button
                                class="inline-flex items-center gap-3 px-3 py-2 border-2 border-transparent hover:border-slate-100 text-sm leading-4 font-bold rounded-xl text-slate-600 bg-white hover:bg-slate-50 hover:text-indigo-600 focus:outline-none transition">
                                <div
                                    class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-black">
                                    {{ substr(Auth::user()->name, 0, 1) }}
                                </div>
                                <div class="hidden sm:block">{{ Auth::user()->name }}</div>
                                <i class="fas fa-chevron-down text-xs text-slate-400"></i>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="px-4 py-3 border-b border-gray-100">
                                <div class="text-xs text-gray-400 mb-1">Akses:</div>
                                <div class="font-black uppercase text-indigo-500">{{ Auth::user()->roles->first()->name
                                    ?? 'User' }}</div>
                            </div>
                            <x-dropdown-link :href="route('profile.edit')" class="font-bold py-3"><i
                                    class="fas fa-user-circle w-5 text-slate-400"></i> Profile</x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();"
                                    class="font-bold text-rose-500 py-3 hover:bg-rose-50 hover:text-rose-600">
                                    <i class="fas fa-sign-out-alt w-5"></i> Log Out
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </header>

            <!-- KONTEN BISA DI-SCROLL MANDIRI -->
            <main class="flex-1 w-full overflow-y-auto">

                @isset($header)
                <div class="bg-white border-b border-slate-200 py-5 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
                @endisset

                <div class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto flex flex-col min-h-full">
                    {{ $slot }}

                    <footer class="mt-auto pt-10 pb-4 flex justify-center items-center">
                        <p class="text-sm font-bold text-slate-400">&copy; {{ date('Y') }} {{ config('app.name', 'CBT
                            Modern') }}. All rights reserved.</p>
                    </footer>
                </div>
            </main>

        </div>

    </div>

    @stack('scripts')
</body>

</html>