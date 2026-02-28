<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'CBT Pro') }} - Otentikasi</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=nunito:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Nunito', sans-serif;
        }

        .animate-blob {
            animation: blob 7s infinite;
        }

        .animation-delay-2000 {
            animation-delay: 2s;
        }

        .animation-delay-4000 {
            animation-delay: 4s;
        }

        @keyframes blob {
            0% {
                transform: translate(0px, 0px) scale(1);
            }

            33% {
                transform: translate(30px, -50px) scale(1.1);
            }

            66% {
                transform: translate(-20px, 20px) scale(0.9);
            }

            100% {
                transform: translate(0px, 0px) scale(1);
            }
        }
    </style>
</head>

<body
    class="text-slate-800 antialiased bg-slate-50 min-h-screen relative overflow-hidden flex items-center justify-center selection:bg-indigo-500 selection:text-white">

    <div class="fixed inset-0 w-full h-full pointer-events-none z-0">
        <div
            class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-indigo-300 rounded-full mix-blend-multiply filter blur-3xl opacity-40 animate-blob">
        </div>
        <div
            class="absolute top-[20%] right-[-5%] w-96 h-96 bg-cyan-300 rounded-full mix-blend-multiply filter blur-3xl opacity-40 animate-blob animation-delay-2000">
        </div>
        <div
            class="absolute bottom-[-20%] left-[20%] w-[30rem] h-[30rem] bg-purple-300 rounded-full mix-blend-multiply filter blur-3xl opacity-40 animate-blob animation-delay-4000">
        </div>
    </div>

    <div class="relative z-10 w-full flex flex-col sm:justify-center items-center pt-6 sm:pt-0 px-4">

        <div class="mb-8 transform transition hover:scale-105 duration-300">
            <a href="/" class="flex flex-col items-center gap-3">
                <div
                    class="w-16 h-16 bg-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-xl shadow-indigo-200">
                    {{-- Menggunakan icon FontAwesome atau Logo bawaan --}}
                    <i class="fas fa-graduation-cap text-3xl"></i>
                </div>
                <span class="font-black text-3xl tracking-tight text-slate-800">
                    CBT<span class="text-indigo-600">Pro</span>
                </span>
            </a>
        </div>

        <div
            class="w-full sm:max-w-md px-8 py-10 bg-white/80 backdrop-blur-xl shadow-2xl shadow-indigo-500/10 border border-white rounded-[2.5rem] overflow-hidden relative">

            <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-indigo-500 via-purple-500 to-cyan-500">
            </div>

            <div class="relative z-10">
                {{ $slot }}
            </div>

        </div>

        <p class="mt-8 text-sm font-bold text-slate-400">
            &copy; {{ date('Y') }} {{ config('app.name', 'CBT Modern') }}. All rights reserved.
        </p>

    </div>
</body>

</html>