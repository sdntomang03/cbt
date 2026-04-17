<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Kawan Baca</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom style untuk mengubah checkbox biasa menjadi kartu yang bisa diklik */
        .check-card input:checked+div {
            border-color: #3b82f6;
            /* blue-500 */
            background-color: #eff6ff;
            /* blue-50 */
        }

        .check-card input:checked+div .check-icon {
            opacity: 1;
            transform: scale(1);
        }
    </style>
</head>

<body class="bg-slate-50 min-h-screen flex items-center justify-center p-6 font-sans">
    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 w-full max-w-xl">

        <div class="text-center mb-8">
            <div class="inline-block bg-blue-100 p-4 rounded-full mb-3">
                <span class="text-4xl">📚</span>
            </div>
            <h2 class="text-2xl md:text-3xl font-extrabold text-slate-800">Mari Membaca!</h2>
            <p class="text-slate-500 font-medium mt-2">Pilih materi yang ingin kamu pelajari hari ini.</p>
        </div>

        @if ($errors->any())
        <div class="bg-rose-50 text-rose-600 p-4 rounded-xl mb-6 text-sm font-bold border border-rose-200">
            <ul>
                @foreach ($errors->all() as $error)
                <li>⚠️ {{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('baca.generate') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-3">Pilih Jenis Bacaan (Boleh lebih dari
                    satu):</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

                    <label class="check-card cursor-pointer">
                        <input type="checkbox" name="jenis_bacaan[]" value="kata" class="hidden" checked>
                        <div class="border-2 border-slate-200 rounded-xl p-4 transition-all relative overflow-hidden">
                            <span class="text-2xl block mb-1">🔤</span>
                            <span class="font-bold text-slate-700 block">Satu Kata</span>
                            <span class="text-xs text-slate-500">Membaca kata dasar</span>
                            <div
                                class="check-icon absolute top-4 right-4 opacity-0 scale-50 transition-all text-blue-500">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                    </label>

                    <label class="check-card cursor-pointer">
                        <input type="checkbox" name="jenis_bacaan[]" value="kalimat" class="hidden" checked>
                        <div class="border-2 border-slate-200 rounded-xl p-4 transition-all relative overflow-hidden">
                            <span class="text-2xl block mb-1">📝</span>
                            <span class="font-bold text-slate-700 block">Kalimat Sederhana</span>
                            <span class="text-xs text-slate-500">Satu baris kalimat utuh</span>
                            <div
                                class="check-icon absolute top-4 right-4 opacity-0 scale-50 transition-all text-blue-500">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                    </label>

                    <label class="check-card cursor-pointer">
                        <input type="checkbox" name="jenis_bacaan[]" value="paragraf" class="hidden">
                        <div class="border-2 border-slate-200 rounded-xl p-4 transition-all relative overflow-hidden">
                            <span class="text-2xl block mb-1">📄</span>
                            <span class="font-bold text-slate-700 block">Paragraf Pendek</span>
                            <span class="text-xs text-slate-500">3-4 baris kalimat berurutan</span>
                            <div
                                class="check-icon absolute top-4 right-4 opacity-0 scale-50 transition-all text-blue-500">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                    </label>

                    <label class="check-card cursor-pointer">
                        <input type="checkbox" name="jenis_bacaan[]" value="bacaan" class="hidden">
                        <div class="border-2 border-slate-200 rounded-xl p-4 transition-all relative overflow-hidden">
                            <span class="text-2xl block mb-1">📖</span>
                            <span class="font-bold text-slate-700 block">Cerita Penuh</span>
                            <span class="text-xs text-slate-500">Bacaan panjang berjudl</span>
                            <div
                                class="check-icon absolute top-4 right-4 opacity-0 scale-50 transition-all text-blue-500">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                    </label>

                </div>
            </div>

            <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100">
                <label class="block text-sm font-bold text-slate-700 mb-2">Berapa kali kamu ingin berlatih?</label>
                <div class="flex items-center gap-4">
                    <input type="number" name="jumlah" value="5" min="1" max="20"
                        class="w-24 text-center text-lg font-bold p-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none">
                    <span class="text-slate-500 font-medium">Latihan</span>
                </div>
            </div>

            <button type="submit"
                class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-lg py-4 rounded-xl transition duration-200 shadow-lg shadow-emerald-200 hover:-translate-y-1">
                Mulai Berlatih Sekarang!
            </button>
        </form>
    </div>
</body>

</html>