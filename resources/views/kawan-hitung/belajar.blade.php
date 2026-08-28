<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Latihan Terbimbing - KawanHitung</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
        }

        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body class="bg-gradient-to-b from-blue-50 to-indigo-50 min-h-screen flex flex-col font-sans pb-10">

    <!-- Navbar Sederhana -->
    <header class="bg-white shadow-sm p-4 sticky top-0 z-10 border-b border-slate-100">
        <div class="max-w-3xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div
                    class="w-10 h-10 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center text-xl shadow-inner">
                    🧑‍🏫</div>
                <h1 class="font-black text-slate-700 text-lg md:text-xl">Belajar Berhitung</h1>
            </div>
            <a href="{{ route('hitung.index') }}"
                class="text-sm font-bold text-slate-400 hover:text-rose-500 transition bg-slate-50 hover:bg-rose-50 px-4 py-2 rounded-full">Kembali
                ke Menu ✖</a>
        </div>
    </header>

    <main class="flex-grow flex flex-col items-center p-4 md:p-8 w-full max-w-3xl mx-auto relative">

        <!-- Papan Soal -->
        <div
            class="bg-white p-8 md:p-12 rounded-[2.5rem] shadow-xl shadow-blue-100/50 border-4 border-white w-full text-center fade-in z-10">
            <h3 class="text-slate-400 font-bold mb-6 tracking-widest uppercase text-sm">Berapakah Hasil Dari:</h3>

            <div
                class="flex flex-wrap items-center justify-center gap-4 md:gap-6 text-5xl md:text-7xl font-black text-slate-700 tracking-tight mb-10">
                <span id="angka1">{{ $s['n1'] }}</span>

                @php
                $opClass = $s['op'] == '+' ? 'text-blue-500 bg-blue-50 border-blue-100' :
                ($s['op'] == '-' ? 'text-rose-500 bg-rose-50 border-rose-100' :
                ($s['op'] == '*' ? 'text-amber-500 bg-amber-50 border-amber-100' : 'text-emerald-500 bg-emerald-50
                border-emerald-100'));
                @endphp

                <span id="operasi" data-op="{{ $s['op'] }}"
                    class="{{ $opClass }} w-16 h-16 md:w-24 md:h-24 flex items-center justify-center rounded-[2rem] border-2 shadow-inner text-4xl md:text-6xl">
                    {{ $s['op'] == '*' ? '×' : ($s['op'] == '/' ? '÷' : $s['op']) }}
                </span>

                <span id="angka2">{{ $s['n2'] }}</span>
                <span class="text-slate-300">=</span>
            </div>

            <!-- Interaksi Siswa -->
            <div class="flex flex-col md:flex-row items-center justify-center gap-4">
                <input type="text" id="jawaban-siswa" placeholder="Jawabanmu..." oninput="formatRibuan(this)"
                    class="w-full md:w-64 py-4 px-6 text-center text-3xl font-black rounded-2xl border-4 border-slate-100 bg-slate-50 focus:bg-white focus:border-blue-500 outline-none text-blue-700 transition-all placeholder:text-slate-300">

                <button onclick="cekJawaban()" id="btn-cek"
                    class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white font-black py-4 px-8 rounded-2xl transition-all shadow-lg shadow-blue-200 hover:-translate-y-1">
                    Cek Jawaban
                </button>
            </div>

            <div class="mt-4">
                <button onclick="tampilkanPenjelasan()" id="btn-bantuan"
                    class="text-slate-400 hover:text-amber-500 font-bold text-sm underline decoration-2 underline-offset-4 transition-colors">
                    Bingung? Lihat langkah pengerjaannya
                </button>
            </div>

            <!-- Notifikasi Benar/Salah -->
            <div id="feedback-box" class="mt-6 p-4 rounded-xl font-bold text-lg hidden"></div>
        </div>

        <!-- Ruang Penjelasan / Langkah Pengerjaan (Muncul setelah dicek) -->
        <div id="ruang-petunjuk" class="w-full mt-8 fade-in hidden">
            <div class="flex items-center justify-center gap-3 mb-6">
                <span class="bg-amber-400 text-white p-2 rounded-xl text-xl shadow-lg shadow-amber-200">💡</span>
                <h3 class="text-2xl font-black text-slate-700">Langkah Pengerjaan</h3>
            </div>

            <!-- Kontainer Penjelasan (Diisi oleh JavaScript) -->
            <div id="kontainer-langkah" class="w-full"></div>

            <!-- Form Lanjut ke Soal Berikutnya -->
            <form action="{{ route('hitung.generate') }}" method="POST" class="mt-10 text-center">
                @csrf
                <!-- Mengirim ulang konfigurasi agar soal berikutnya sesuai dengan setting awal -->
                <input type="hidden" name="mode" value="belajar">
                <input type="hidden" name="operasi" value="{{ $config['operasi'] ?? $s['op'] }}">
                <input type="hidden" name="digit1" value="{{ strlen((string)$s['n1']) }}">
                <input type="hidden" name="digit2" value="{{ strlen((string)$s['n2']) }}">
                <input type="hidden" name="jumlah_soal" value="1"> <!-- Paksa 1 soal -->

                <button type="submit"
                    class="bg-gradient-to-r from-emerald-400 to-teal-500 hover:from-emerald-500 hover:to-teal-600 text-white font-black text-xl py-4 px-10 rounded-[2rem] transition-all duration-300 shadow-xl shadow-emerald-200 hover:-translate-y-1 flex items-center justify-center gap-3 mx-auto">
                    <span>Lanjut Soal Berikutnya</span>
                    <span class="text-2xl">➡</span>
                </button>
            </form>
        </div>

    </main>

    <script>
        const n1 = {{ $s['n1'] }};
        const n2 = {{ $s['n2'] }};
        const op = "{{ $s['op'] }}";

        let kunci = 0;
        if(op === '+') kunci = n1 + n2;
        else if(op === '-') kunci = n1 - n2;
        else if(op === '*') kunci = n1 * n2;
        else if(op === '/') kunci = n1 / n2;

        function cekJawaban() {
            const inputVal = document.getElementById('jawaban-siswa').value;
            const feedbackBox = document.getElementById('feedback-box');

            if (inputVal.trim() === '') {
                alert("Isi jawabanmu dulu ya, atau klik tombol bingung di bawah!");
                return;
            }

            const cleanVal = inputVal.replace(/\./g, '');
const isCorrect = parseFloat(cleanVal) === kunci;

            feedbackBox.classList.remove('hidden', 'bg-emerald-100', 'text-emerald-700', 'bg-rose-100', 'text-rose-700');

            if (isCorrect) {
                feedbackBox.classList.add('bg-emerald-100', 'text-emerald-700');
                feedbackBox.innerHTML = `🌟 Hebat Sekali! Jawabanmu <strong>${inputVal}</strong> benar!`;
            } else {
                feedbackBox.classList.add('bg-rose-100', 'text-rose-700');
                feedbackBox.innerHTML = `💪 Hampir benar! Jawaban yang tepat adalah <strong>${kunci}</strong>. Mari lihat caranya.`;
            }

            document.getElementById('jawaban-siswa').disabled = true;
            document.getElementById('btn-cek').classList.add('hidden');
            document.getElementById('btn-bantuan').classList.add('hidden');

            tampilkanPenjelasan();
        }

        function tampilkanPenjelasan() {
            document.getElementById('btn-bantuan').classList.add('hidden');
            document.getElementById('ruang-petunjuk').classList.remove('hidden');
            document.getElementById('kontainer-langkah').innerHTML = getLangkahPenyelesaian(n1, n2, op);

            setTimeout(() => {
                document.getElementById('ruang-petunjuk').scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
        }

        // ==========================================
        // ALGORITMA CERITA VISUAL (SIMBOL & GAMBAR)
        // ==========================================

        // Fungsi menggambar objek, dilengkapi parameter 'isSmall' agar layar tidak penuh jika angkanya belasan/puluhan
        function renderObjek(jumlah, ikon, coret = false, isSmall = false) {
            let html = '';
            let sizeClass = isSmall ? 'text-xl m-0.5' : 'text-3xl m-1';
            let bgCoret = isSmall ? 'h-0.5' : 'h-1.5';

            for(let i = 0; i < Math.min(jumlah, 100); i++) { // Max 100 agar aman
                if(coret) {
                    html += `<span class="relative inline-block ${sizeClass} opacity-40 grayscale transition-transform hover:scale-110">${ikon}<div class="absolute inset-0 flex items-center justify-center"><div class="w-full ${bgCoret} bg-rose-500 rotate-45 rounded-full shadow-sm"></div></div></span>`;
                } else {
                    html += `<span class="inline-block ${sizeClass} transition-transform hover:scale-125 hover:-translate-y-1 drop-shadow-sm">${ikon}</span>`;
                }
            }
            return html;
        }

        function getVisualPenyelesaian(n1, n2, op) {
            // 1. Kumpulan Buah-buahan
            const daftarBuah = [
                { ikon: '🍎', nama: 'apel' },
                { ikon: '🍊', nama: 'jeruk' },
                { ikon: '🍓', nama: 'stroberi' },
                { ikon: '🍇', nama: 'anggur' },
                { ikon: '🍉', nama: 'semangka' },
                { ikon: '🍌', nama: 'pisang' },
                { ikon: '🍒', nama: 'ceri' },
                { ikon: '🍍', nama: 'nanas' }
            ];

            // 2. Pilih buah secara acak
            const buahAcak = daftarBuah[Math.floor(Math.random() * daftarBuah.length)];
            const ikon = buahAcak.ikon;
            const nama = buahAcak.nama;

            let html = `<div class="bg-white p-6 md:p-8 rounded-[2rem] border-4 border-blue-100 shadow-xl w-full text-center fade-in">`;
            html += `<h4 class="font-black text-2xl text-blue-600 mb-6 border-b-2 border-blue-100 pb-4">Mari Kita Gambarkan! 🎨</h4>`;

            if (op === '+') {
                html += `<div class="flex flex-wrap items-center justify-center gap-4 mb-6 bg-slate-50 p-6 rounded-2xl border-2 border-slate-100">`;
                html += `<div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100">${renderObjek(n1, ikon)}</div>`;
                html += `<div class="text-4xl font-black text-slate-300">+</div>`;
                html += `<div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100">${renderObjek(n2, ikon)}</div>`;
                html += `<div class="text-4xl font-black text-slate-300">=</div>`;
                html += `<div class="bg-yellow-50 border-2 border-yellow-200 p-4 rounded-xl">${renderObjek(kunci, ikon)}</div>`;
                html += `</div>`;
                html += `<p class="text-slate-600 text-lg font-medium leading-relaxed">Kamu memiliki <strong>${n1}</strong> ${nama}, kemudian ditambah lagi <strong>${n2}</strong> ${nama}. Jika digabungkan dan dihitung semuanya, jumlah ${nama} milikmu sekarang adalah <strong class="text-emerald-500 text-2xl">${kunci}</strong>.</p>`;
            }
            else if (op === '-') {
                let sisa = n1 - n2;
                html += `<div class="flex flex-wrap items-center justify-center gap-2 mb-6 bg-slate-50 p-6 rounded-2xl border-2 border-slate-100">`;
                html += renderObjek(sisa, ikon);
                html += renderObjek(n2, ikon, true);
                html += `</div>`;
                html += `<p class="text-slate-600 text-lg font-medium leading-relaxed">Awalnya ada <strong>${n1}</strong> ${nama}. Lalu, sebanyak <strong>${n2}</strong> ${nama} dicoret (dimakan). Coba hitung ${nama} yang masih utuh! Sisa ${nama} milikmu sekarang adalah <strong class="text-emerald-500 text-2xl">${kunci}</strong>.</p>`;
            }
            else if (op === '*') {
                html += `<div class="flex flex-col items-center justify-center gap-4 mb-6 bg-slate-50 p-6 rounded-2xl border-2 border-slate-100">`;
                for(let i=0; i<n1; i++) {
                    html += `<div class="bg-white p-3 rounded-xl shadow-sm border border-slate-100 flex items-center gap-3"><span class="bg-blue-100 text-blue-600 font-bold px-3 py-1 rounded-full text-sm whitespace-nowrap">Kotak ${i+1}</span> <div class="text-left">${renderObjek(n2, ikon)}</div></div>`;
                }
                html += `</div>`;
                html += `<p class="text-slate-600 text-lg font-medium leading-relaxed">Perkalian adalah penjumlahan yang diulang-ulang. Bayangkan ada <strong>${n1} kotak</strong>, dan di dalam setiap kotak terdapat <strong>${n2} ${nama}</strong>. Jika kita hitung semua ${nama} di dalam kotak tersebut, totalnya adalah <strong class="text-emerald-500 text-2xl">${kunci}</strong> ${nama}.</p>`;
            }
            else if (op === '/') {
                let isSmallIcon = kunci >= 10;
                html += `<div class="flex flex-wrap items-stretch justify-center gap-4 mb-6 bg-slate-50 p-6 rounded-2xl border-2 border-slate-100">`;
                for(let i=0; i<n2; i++) {
                    html += `<div class="bg-white p-4 rounded-xl shadow-sm border-2 border-emerald-100 flex flex-col items-center flex-1 min-w-[120px] max-w-[200px]">
                                <span class="mb-3 font-bold text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full text-sm w-full text-center truncate">👦 Teman ${i+1}</span>
                                <div class="flex flex-wrap justify-center items-center h-full content-center">
                                    ${renderObjek(kunci, ikon, false, isSmallIcon)}
                                </div>
                             </div>`;
                }
                html += `</div>`;
                html += `<p class="text-slate-600 text-lg font-medium leading-relaxed">Konsep pembagian adalah berbagi sama rata. Bayangkan kamu memiliki <strong>${n1}</strong> ${nama} yang dibagikan kepada <strong>${n2}</strong> teman secara bergantian.</p>`;
                html += `<p class="text-slate-600 text-lg font-medium leading-relaxed mt-2">Setelah dibagikan satu per satu sampai habis, ternyata masing-masing teman mendapatkan bagian <strong class="text-emerald-500 text-2xl">${kunci}</strong> ${nama}!</p>`;
            }

            html += `</div>`;
            return html;
        }

        function getNilaiTempat(indexDariBelakang) {
            const tempat = ["Satuan", "Puluhan", "Ratusan", "Ribuan", "Puluh Ribuan"];
            return tempat[indexDariBelakang] || "Angka ke-" + (indexDariBelakang+1);
        }

        function formatRibuan(input) {
    let value = input.value.replace(/[^\d-]/g, '');
    if (value === '' || value === '-') {
        input.value = value;
        return;
    }
    input.value = parseInt(value, 10).toLocaleString('id-ID');
}
        function getLangkahPenyelesaian(n1, n2, op) {

            // =================================================================================
            // LOGIKA DETEKSI OTOMATIS: Kapan harus pakai gambar, kapan harus pakai bersusun?
            // =================================================================================
            let gunakanGambarVisual = false;

            // 1. Penjumlahan & Pengurangan: Jika angka <= 10
            if ((op === '+' || op === '-') && n1 <= 10 && n2 <= 10) gunakanGambarVisual = true;
            // 2. Perkalian: Jika kotak <= 10 dan isi <= 10
            else if (op === '*' && n1 <= 10 && n2 <= 10) gunakanGambarVisual = true;
            // 3. Pembagian: Jika 2 Angka dibagi 1 Angka (Contoh: 81 / 9, 24 / 3, 50 / 5)
            else if (op === '/' && n1 < 100 && n2 < 10) gunakanGambarVisual = true;

            // Jalankan mode Visual Gambar
            if (gunakanGambarVisual) {
                return getVisualPenyelesaian(n1, n2, op);
            }

            // --- JIKA ANGKA TERLALU BESAR, KEMBALI GUNAKAN ALGORITMA BERSUSUN BERCERITA ---
            let aStr = n1.toString();
            let bStr = n2.toString();
            let maxL = Math.max(aStr.length, bStr.length, kunci.toString().length) + 2;
            const fmt = (v, s = "") => v.toString().padStart(maxL - 1, " ") + " " + s;

            let html = `<div class="flex flex-col lg:flex-row gap-6 items-start w-full text-left">`;

            html += `<div class="w-full lg:w-1/2 bg-slate-800 rounded-3xl overflow-hidden shadow-xl p-2 shrink-0">
                        <div class="bg-slate-900 p-6 rounded-2xl overflow-x-auto flex justify-center">
                            <pre class="text-slate-100 font-mono text-2xl md:text-3xl leading-relaxed">`;

            let cerita = `<div class="w-full lg:w-1/2 bg-white p-6 rounded-3xl border-2 border-slate-100 shadow-sm">
                            <h4 class="font-black text-xl text-blue-600 mb-4 border-b pb-2">Mari kita bedah:</h4>
                            <ul class="space-y-4 text-slate-600 font-medium text-lg">`;

            // (Logika Bersusun untuk +, -, *, dan / jika angkanya ratusan/ribuan)
            // ... [Kode bersusun tetap dipertahankan untuk angka yang sangat besar] ...

            if (op === '+') {
                let s1 = aStr.padStart(maxL - 2, '0'); let s2 = bStr.padStart(maxL - 2, '0');
                let simpananTeks = ""; let carry = 0; let stepVisual = [];
                for(let i = s1.length - 1; i >= 0; i--) {
                    let d1 = parseInt(s1[i]); let d2 = parseInt(s2[i]);
                    if (isNaN(d1) && isNaN(d2)) continue;
                    let tempat = getNilaiTempat(s1.length - 1 - i); let sum = d1 + d2 + carry;
                    if (d1 > 0 || d2 > 0 || carry > 0 || i === s1.length - 1) {
                        let txt = `<li><span class="font-bold text-slate-800">${tempat}:</span> Tambahkan ${d1} + ${d2}`;
                        if (carry > 0) txt += ` + <span class="text-amber-500 font-bold">${carry} (simpanan)</span>`;
                        txt += ` = <strong>${sum}</strong>. `;
                        if (sum > 9 && i > 0) {
                            carry = Math.floor(sum / 10); simpananTeks = carry.toString() + simpananTeks;
                            txt += `Hasilnya puluhan, tulis <strong class="text-emerald-500 text-xl">${sum % 10}</strong> dan <strong class="text-amber-500">simpan ${carry}</strong> di depan.</li>`;
                        } else {
                            simpananTeks = " " + simpananTeks; carry = 0;
                            txt += `Tulis <strong class="text-emerald-500 text-xl">${sum}</strong> di bawah garis.</li>`;
                        }
                        stepVisual.unshift(txt);
                    } else { simpananTeks = " " + simpananTeks; }
                }
                if (simpananTeks.trim().length > 0) html += `<span class="text-amber-400 font-bold text-sm absolute -mt-4">${" ".repeat(maxL - simpananTeks.length)}${simpananTeks}</span>\n`;
                cerita += stepVisual.reverse().join("");
                html += fmt(n1) + "\n" + fmt(n2, "+") + "\n" + "-".repeat(maxL).padStart(maxL) + "\n" + `<span class="text-emerald-400 font-bold">${fmt(kunci)}</span>`;
            }
            else if (op === '-') {
                let aArr = aStr.split('').map(Number); let bArr = bStr.padStart(aStr.length, '0').split('').map(Number);
                let pinjamanVisual = Array(aStr.length).fill(" "); let hasilVisual = [];
                for(let i = aArr.length - 1; i >= 0; i--) {
                    let top = aArr[i]; let bot = bArr[i]; let tempat = getNilaiTempat(aArr.length - 1 - i);
                    if (top < bot) {
                        let p = i - 1; while(p >= 0 && aArr[p] === 0) { p--; }
                        aArr[p] -= 1; pinjamanVisual[p] = aArr[p].toString();
                        for(let k = p + 1; k < i; k++) { aArr[k] = 9; pinjamanVisual[k] = "9"; }
                        let newVal = top + 10;
                        cerita += `<li><span class="font-bold text-slate-800">${tempat}:</span> ${top} - ${bot} tidak bisa! Kita <strong class="text-rose-500">pinjam 1</strong> dari angka depannya. Angka ${top} jadi <strong>${newVal}</strong>. Maka, ${newVal} - ${bot} = <strong class="text-emerald-500 text-xl">${newVal - bot}</strong>.</li>`;
                        hasilVisual.unshift(newVal - bot);
                    } else {
                        cerita += `<li><span class="font-bold text-slate-800">${tempat}:</span> Kurangkan ${top} - ${bot} = <strong class="text-emerald-500 text-xl">${top - bot}</strong>.</li>`;
                        hasilVisual.unshift(top - bot);
                    }
                }
                let barisPinjam = ""; for(let v of pinjamanVisual) barisPinjam += v;
                if(barisPinjam.trim().length > 0) html += `<span class="text-rose-400 font-bold text-sm absolute -mt-4">${barisPinjam.padStart(maxL - 2, " ")} </span>\n`;
                html += fmt(n1) + "\n" + fmt(n2, "-") + "\n" + "-".repeat(maxL).padStart(maxL) + "\n" + `<span class="text-emerald-400 font-bold">${fmt(kunci)}</span>`;
            }
            else if (op === '*') {
                html += fmt(n1) + "\n" + fmt(n2, "×") + "\n" + "-".repeat(maxL).padStart(maxL) + "\n";
                cerita += `<li>Kita kalikan <strong class="text-blue-600">${n1}</strong> dengan tiap angka dari <strong class="text-rose-500">${n2}</strong>, mulai dari belakang.</li>`;
                for (let i = 0; i < bStr.length; i++) {
                    let botDigit = parseInt(bStr[bStr.length - 1 - i]); let carry = 0; let barisHasil = "";
                    cerita += `<li class="mt-6"><span class="bg-blue-100 text-blue-700 font-black px-3 py-1 rounded-lg">Tahap ${i+1}</span> Kalikan <strong class="text-blue-600">${n1}</strong> dengan <strong class="text-rose-500 text-xl">${botDigit}</strong>:</li><ul class="list-disc ml-6 mt-2 space-y-3 text-base text-slate-500">`;
                    for(let j = 0; j < aStr.length; j++) {
                        let topDigit = parseInt(aStr[aStr.length - 1 - j]); let tempProd = (botDigit * topDigit) + carry;
                        let dTxt = `Kalikan ${botDigit} × ${topDigit} = <strong>${botDigit * topDigit}</strong>.`;
                        if(carry > 0) dTxt += ` Tambah simpanan ${carry}, jadi <strong>${tempProd}</strong>.`;
                        if(tempProd > 9 && j < aStr.length - 1) {
                            carry = Math.floor(tempProd / 10); let sisa = tempProd % 10;
                            dTxt += `<br><span class="text-indigo-500">➔ Tulis <strong class="text-emerald-500 text-xl">${sisa}</strong>, <strong class="text-amber-500">simpan ${carry}</strong> di atasnya.</span>`;
                            barisHasil = sisa.toString() + barisHasil;
                        } else {
                            carry = 0;
                            dTxt += `<br><span class="text-indigo-500">➔ Tulis <strong class="text-emerald-500 text-xl">${tempProd}</strong>.</span>`;
                            barisHasil = tempProd.toString() + barisHasil;
                        }
                        cerita += `<li>${dTxt}</li>`;
                    }
                    cerita += `</ul>`;
                    let baris = barisHasil + " ".repeat(i);
                    html += baris.padStart(maxL - 1) + "\n";
                }
                if (bStr.length > 1) {
                    html += "-".repeat(maxL).padStart(maxL) + " +\n";
                    cerita += `<li class="mt-6"><span class="bg-emerald-100 text-emerald-700 font-black px-3 py-1 rounded-lg">Akhir</span> Jumlahkan semua baris untuk mendapat nilai akhir: <strong class="text-emerald-600 text-2xl">${kunci}</strong>.</li>`;
                }
                html += `<span class="text-emerald-400 font-bold">${fmt(kunci)}</span>`;
            }
            else if (op === '/') {
                let n1S = n1.toString(); let n2S = n2.toString(); let hasilBagi = Math.floor(n1 / n2); let indent = n2S.length + 3;
                html += `Hasil: <span class="text-emerald-400 font-bold">${hasilBagi}</span>\n\n` + " ".repeat(indent) + `<span class="text-emerald-400 font-bold">${hasilBagi}</span>` + "\n" + " ".repeat(n2S.length + 1) + "_______" + "\n" + `${n2} / ${n1S}\n`;
                cerita += `<li>Kita menggunakan cara <strong>Porogapit</strong>. Angka pembaginya adalah <strong>${n2}</strong>.</li>`;
                let sisa = 0; let step = 0;
                for (let i = 0; i < n1S.length; i++) {
                    sisa = (sisa * 10) + parseInt(n1S[i]);
                    if (sisa >= n2) {
                        let kali = Math.floor(sisa / n2); let hKali = kali * n2;
                        cerita += `<li class="mt-3">Berapa kali ${n2} yang mendekati ${sisa}? Jawabannya <strong class="text-blue-600 text-xl">${kali}</strong> (${kali} × ${n2} = ${hKali}).</li>`;
                        cerita += `<li>Tulis <strong class="text-emerald-500">${kali}</strong> di atap, dan tulis ${hKali} di bawah ${sisa}. Kurangkan: ${sisa} - ${hKali} = <strong>${sisa - hKali}</strong>.</li>`;
                        if (step > 0) html += " ".repeat(indent + i - (sisa.toString().length - 1)) + sisa + "\n";
                        html += " ".repeat(indent + i - (hKali.toString().length - 1)) + `<span class="text-rose-400">${hKali}</span>` + "\n";
                        let gLen = Math.max(sisa.toString().length, hKali.toString().length);
                        html += " ".repeat(indent + i - (gLen - 1)) + "-".repeat(gLen) + " -\n";
                        sisa = sisa - hKali; step++;
                        if (i === n1S.length - 1) html += " ".repeat(indent + i - (sisa.toString().length - 1)) + `<span class="text-emerald-400 font-bold">${sisa}</span>`;
                    } else {
                        cerita += `<li class="mt-3">Karena <strong>${sisa}</strong> lebih kecil dari <strong>${n2}</strong>, kita belum bisa membaginya. Kita gabungkan dengan angka di sebelahnya.</li>`;
                        if (i === n1S.length - 1) html += " ".repeat(indent + i - (sisa.toString().length - 1)) + `<span class="text-emerald-400 font-bold">${sisa}</span>`;
                    }
                }
            }

            html += `</pre></div></div>`;
            cerita += `</ul></div>`;
            return html + cerita + `</div>`;
        }

        document.getElementById('jawaban-siswa').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') { cekJawaban(); }
        });
    </script>
</body>

</html>