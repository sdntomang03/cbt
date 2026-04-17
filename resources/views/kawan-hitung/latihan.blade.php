<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Latihan KawanHitung</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
        }

        /* Menyembunyikan panah atas-bawah bawaan pada input number agar terlihat rapi */
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* Animasi masuk perlahan untuk soal */
        .slide-up {
            animation: slideUp 0.4s ease-out forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        @keyframes slideUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body class="bg-gradient-to-b from-sky-50 to-indigo-50 min-h-screen py-8 md:py-12 px-4">
    <div class="max-w-3xl mx-auto">

        <div
            class="bg-white p-6 md:p-8 rounded-3xl shadow-sm border-2 border-white mb-8 flex flex-col md:flex-row justify-between items-center gap-4 relative overflow-hidden">
            <div
                class="absolute -right-6 -top-6 w-24 h-24 bg-yellow-200 rounded-full mix-blend-multiply opacity-50 blur-xl">
            </div>

            <div class="flex items-center gap-4 z-10">
                <div class="w-14 h-14 bg-blue-100 rounded-2xl flex items-center justify-center text-3xl shadow-inner">
                    🧠
                </div>
                <div>
                    <h2 class="text-2xl font-black text-slate-800">Ayo Selesaikan!</h2>
                    <p class="text-slate-500 font-bold text-sm">Fokus dan hitung dengan teliti ya.</p>
                </div>
            </div>

            <div
                class="z-10 bg-blue-50 border border-blue-200 text-blue-700 py-2 px-6 rounded-full font-black text-sm md:text-base flex items-center gap-2 shadow-sm">
                <span>📝</span> Total: {{ count($soal) }} Soal
            </div>
        </div>

        <form action="{{ route('hitung.submit') }}" method="POST" class="space-y-5">
            @csrf

            @foreach($soal as $index => $s)
            <div class="group bg-white p-4 md:p-6 rounded-[2rem] shadow-sm border-2 border-slate-100 hover:border-blue-400 hover:shadow-md transition-all flex items-center justify-between gap-4 slide-up"
                style="animation-delay: {{ $index * 0.1 }}s">

                <div class="flex items-center gap-4 md:gap-8 flex-1">
                    <div
                        class="w-12 h-12 md:w-14 md:h-14 bg-slate-50 text-slate-400 font-black rounded-2xl flex items-center justify-center text-lg md:text-xl border-2 border-slate-100 group-hover:bg-blue-500 group-hover:text-white group-hover:border-blue-500 transition-colors shadow-inner">
                        {{ $index + 1 }}
                    </div>

                    <div
                        class="text-3xl md:text-5xl font-black text-slate-700 flex items-center gap-3 md:gap-4 tracking-tight">
                        <span>{{ $s['n1'] }}</span>

                        @php
                        $opClass = $s['op'] == '+' ? 'text-blue-500 bg-blue-50' :
                        ($s['op'] == '-' ? 'text-rose-500 bg-rose-50' :
                        ($s['op'] == '*' ? 'text-amber-500 bg-amber-50' : 'text-emerald-500 bg-emerald-50'));
                        @endphp
                        <span
                            class="{{ $opClass }} w-10 h-10 md:w-14 md:h-14 flex items-center justify-center rounded-2xl text-2xl md:text-4xl shadow-sm">
                            {{ $s['op'] == '*' ? '×' : ($s['op'] == '/' ? '÷' : $s['op']) }}
                        </span>

                        <span>{{ $s['n2'] }}</span>
                        <span class="text-slate-300">=</span>
                    </div>
                </div>

                <input type="number" name="jawaban[{{ $index }}]" required placeholder="?"
                    class="w-24 md:w-32 py-4 md:py-5 text-center text-3xl md:text-4xl font-black rounded-2xl border-4 border-slate-100 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-0 outline-none text-blue-700 transition-all placeholder:text-slate-300 shadow-inner">
            </div>
            @endforeach

            <div class="pt-8">
                <button type="submit"
                    class="w-full bg-gradient-to-r from-emerald-400 to-teal-500 hover:from-emerald-500 hover:to-teal-600 text-white font-black py-5 rounded-[2rem] text-xl md:text-2xl transition duration-300 shadow-xl shadow-emerald-200 hover:-translate-y-1 hover:shadow-2xl flex items-center justify-center gap-3">
                    <span>Selesai & Kumpulkan</span>
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                    </svg>
                </button>
            </div>
        </form>

    </div>
</body>

</html>