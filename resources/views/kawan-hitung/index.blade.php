<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Latihan KawanHitung</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
        }

        /* Menyembunyikan panah atas-bawah bawaan pada input number */
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
    </style>
</head>

<body
    class="bg-gradient-to-br from-sky-100 via-blue-50 to-indigo-100 min-h-screen flex items-center justify-center p-4 md:p-6">

    <div
        class="bg-white p-8 md:p-10 rounded-[2rem] shadow-2xl shadow-blue-200/50 w-full max-w-lg border-4 border-white relative overflow-hidden">

        <div
            class="absolute -top-10 -right-10 w-32 h-32 bg-yellow-300 rounded-full mix-blend-multiply filter blur-2xl opacity-40">
        </div>
        <div
            class="absolute -bottom-10 -left-10 w-32 h-32 bg-blue-300 rounded-full mix-blend-multiply filter blur-2xl opacity-40">
        </div>

        <div class="relative z-10">
            <div class="text-center mb-8">
                <div
                    class="inline-flex items-center justify-center w-20 h-20 bg-blue-100 text-blue-500 rounded-3xl mb-4 shadow-inner transform rotate-3 hover:rotate-0 transition-transform duration-300">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                        </path>
                    </svg>
                </div>
                <h2 class="text-3xl font-black text-slate-800 tracking-tight">Kawan Hitung</h2>
                <p class="text-slate-500 font-semibold mt-1">Mari atur latihan matematikamu hari ini! 🚀</p>
            </div>

            <form action="{{ route('hitung.generate') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label
                        class="block text-sm font-bold text-slate-400 mb-3 uppercase tracking-wider text-center">Pilih
                        Operasi</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="operasi" value="+" class="peer sr-only" checked>
                            <div
                                class="p-4 rounded-2xl border-2 border-slate-100 peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:bg-slate-50 transition-all text-center group">
                                <div class="text-3xl mb-1 group-hover:scale-110 transition-transform">➕</div>
                                <div class="font-bold text-slate-600 peer-checked:text-blue-700 text-sm md:text-base">
                                    Penjumlahan</div>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="operasi" value="-" class="peer sr-only">
                            <div
                                class="p-4 rounded-2xl border-2 border-slate-100 peer-checked:border-rose-500 peer-checked:bg-rose-50 hover:bg-slate-50 transition-all text-center group">
                                <div class="text-3xl mb-1 group-hover:scale-110 transition-transform">➖</div>
                                <div class="font-bold text-slate-600 peer-checked:text-rose-700 text-sm md:text-base">
                                    Pengurangan</div>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="operasi" value="*" class="peer sr-only">
                            <div
                                class="p-4 rounded-2xl border-2 border-slate-100 peer-checked:border-amber-500 peer-checked:bg-amber-50 hover:bg-slate-50 transition-all text-center group">
                                <div class="text-3xl mb-1 group-hover:scale-110 transition-transform">✖️</div>
                                <div class="font-bold text-slate-600 peer-checked:text-amber-700 text-sm md:text-base">
                                    Perkalian</div>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="operasi" value="/" class="peer sr-only">
                            <div
                                class="p-4 rounded-2xl border-2 border-slate-100 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 hover:bg-slate-50 transition-all text-center group">
                                <div class="text-3xl mb-1 group-hover:scale-110 transition-transform">➗</div>
                                <div
                                    class="font-bold text-slate-600 peer-checked:text-emerald-700 text-sm md:text-base">
                                    Pembagian</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 md:gap-5 bg-slate-50 p-4 md:p-5 rounded-2xl border border-slate-100">
                    <div>
                        <label class="block text-xs md:text-sm font-bold text-slate-500 mb-2">Angka Pertama</label>
                        <div class="relative">
                            <select name="digit1"
                                class="w-full pl-4 pr-10 py-3 rounded-xl border border-slate-200 focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 outline-none appearance-none font-bold text-slate-700 bg-white">
                                <option value="1">1 Digit (Satuan)</option>
                                <option value="2">2 Digit (Puluhan)</option>
                                <option value="3" selected>3 Digit (Ratusan)</option>
                                <option value="4">4 Digit (Ribuan)</option>
                            </select>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                                    <path
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs md:text-sm font-bold text-slate-500 mb-2">Angka Kedua</label>
                        <div class="relative">
                            <select name="digit2"
                                class="w-full pl-4 pr-10 py-3 rounded-xl border border-slate-200 focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 outline-none appearance-none font-bold text-slate-700 bg-white">
                                <option value="1" selected>1 Digit (Satuan)</option>
                                <option value="2">2 Digit (Puluhan)</option>
                                <option value="3">3 Digit (Ratusan)</option>
                                <option value="4">4 Digit (Ribuan)</option>
                            </select>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                                    <path
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-500 mb-3 text-center">Berapa soal yang ingin
                        dikerjakan?</label>
                    <div class="flex items-center justify-center">
                        <div class="relative">
                            <span
                                class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xl font-black">📝</span>
                            <input type="number" name="jumlah_soal" value="5" min="1" max="50"
                                class="w-40 text-center pl-8 text-3xl font-black text-blue-600 py-3 rounded-2xl border-2 border-slate-200 focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 outline-none bg-white shadow-inner transition-all">
                        </div>
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-black text-lg py-4 rounded-2xl transition-all duration-300 shadow-lg shadow-blue-300/50 hover:-translate-y-1 hover:shadow-xl flex items-center justify-center gap-2 mt-4">
                    <span>Mulai Latihan Sekarang!</span>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                            d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</body>

</html>