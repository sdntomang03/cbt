<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Belajar KawanHitung</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Animasi halus untuk elemen visual */
        .pop-in {
            animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }

        @keyframes popIn {
            0% {
                opacity: 0;
                transform: scale(0.5);
            }

            100% {
                opacity: 1;
                transform: scale(1);
            }
        }
    </style>
</head>

<body class="bg-slate-50 min-h-screen py-8 px-4 font-sans text-slate-800">
    <div class="max-w-4xl mx-auto">

        <div
            class="bg-white p-8 rounded-3xl shadow-sm border border-slate-200 mb-8 text-center relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-2 {{ $nilai >= 70 ? 'bg-emerald-400' : 'bg-amber-400' }}"></div>

            <h2 class="text-2xl md:text-3xl font-extrabold text-slate-700 mb-2">Laporan Hasil Belajar</h2>

            <div class="my-6">
                <div
                    class="inline-block text-6xl md:text-8xl font-black {{ $nilai >= 70 ? 'text-emerald-500' : 'text-amber-500' }} pop-in">
                    {{ round($nilai) }}
                </div>
            </div>

            @if($nilai == 100)
            <p class="text-lg text-emerald-600 font-bold mb-1">🌟 Sempurna! Kamu luar biasa!</p>
            @elseif($nilai >= 70)
            <p class="text-lg text-blue-600 font-bold mb-1">👍 Bagus sekali! Pertahankan prestasimu!</p>
            @else
            <p class="text-lg text-amber-600 font-bold mb-1">💪 Jangan menyerah! Mari pelajari pembahasannya.</p>
            @endif

            <p class="text-slate-500 font-medium mb-6">Berhasil menjawab {{ $benar }} dari {{ count($hasil) }} soal
                dengan tepat.</p>

            <a href="{{ route('hitung.index') }}"
                class="inline-flex items-center justify-center px-8 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-bold transition-all shadow-lg shadow-blue-200 hover:-translate-y-1">
                Latihan Lagi Yuk!
            </a>
        </div>

        <div class="flex items-center gap-3 mb-6">
            <div class="h-8 w-2 bg-blue-500 rounded-full"></div>
            <h3 class="text-xl md:text-2xl font-bold text-slate-800">Mari Belajar Bersama</h3>
        </div>

        <div class="space-y-6">
            @foreach($hasil as $index => $item)
            @php
            $opSymbol = $item['soal']['op'] == '*' ? '×' : ($item['soal']['op'] == '/' ? '÷' : $item['soal']['op']);
            @endphp

            <div
                class="bg-white rounded-2xl shadow-sm border {{ $item['is_correct'] ? 'border-emerald-200' : 'border-rose-200' }} overflow-hidden">
                <div
                    class="bg-slate-50/50 p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <span
                                class="bg-slate-800 text-white text-xs font-bold px-3 py-1 rounded-full tracking-wide">SOAL
                                {{ $index + 1 }}</span>
                            <span class="text-xl font-black text-slate-700">
                                {{ $item['soal']['n1'] }} <span class="text-blue-500 mx-1">{{ $opSymbol }}</span> {{
                                $item['soal']['n2'] }} = ?
                            </span>
                        </div>
                        <div class="text-sm font-medium text-slate-600 flex items-center gap-2">
                            Jawabanmu:
                            <span
                                class="px-3 py-1 rounded-lg {{ $item['is_correct'] ? 'bg-emerald-100 text-emerald-700 font-bold' : 'bg-rose-100 text-rose-700 line-through' }}">
                                {{ $item['jawaban_user'] ?? 'Kosong' }}
                            </span>
                            @if(!$item['is_correct'])
                            <span class="text-slate-400">→</span>
                            <span class="bg-emerald-100 text-emerald-700 font-bold px-3 py-1 rounded-lg">Jawaban Tepat:
                                {{ $item['kunci'] }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="shrink-0">
                        @if($item['is_correct'])
                        <div
                            class="flex items-center gap-2 text-emerald-600 font-bold bg-emerald-50 px-4 py-2 rounded-xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                            BENAR
                        </div>
                        @else
                        <div class="flex items-center gap-2 text-rose-500 font-bold bg-rose-50 px-4 py-2 rounded-xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            PERLU DIPERBAIKI
                        </div>
                        @endif
                    </div>
                </div>

                <div class="p-5" id="langkah-{{ $index }}"></div>
            </div>
            @endforeach
        </div>
    </div>

    <script>
        const hasilData = @json($hasil);

        // Kumpulan benda/objek yang ramah anak untuk variasi soal
const bendaVisual = [
            // Alat Tulis & Perlengkapan Sekolah
            { ikon: '✏️', nama: 'pensil' },
            { ikon: '📕', nama: 'buku tulis' },
            { ikon: '🎒', nama: 'tas sekolah' },
            { ikon: '🖍️', nama: 'krayon' },
            { ikon: '📏', nama: 'penggaris' },
            { ikon: '✂️', nama: 'gunting' },

            // Benda di Kamar & Rumah

            { ikon: '⏰', nama: 'jam beker' },

            // Bekal & Peralatan Makan
            { ikon: '🥄', nama: 'sendok' },
            { ikon: '🥛', nama: 'gelas susu' },
            { ikon: '🍱', nama: 'kotak bekal' },
            { ikon: '🍽️', nama: 'piring' },
            { ikon: '🥤', nama: 'botol minum' },
// Buah & Makanan
            { ikon: '🍎', nama: 'apel' },
            { ikon: '🍓', nama: 'stroberi' },
            { ikon: '🍉', nama: 'semangka' },
            { ikon: '🍌', nama: 'pisang' },
            { ikon: '🍇', nama: 'anggur' },
            { ikon: '🍒', nama: 'ceri' },
            { ikon: '🍩', nama: 'donat' },
            { ikon: '🍦', nama: 'es krim' },
            { ikon: '🍬', nama: 'permen' },
            { ikon: '🍰', nama: 'kue' },
            { ikon: '🍕', nama: 'potong pizza' },
            // Mainan Aktivitas Fisik
            { ikon: '⚽', nama: 'bola' },
            { ikon: '🚲', nama: 'sepeda' },
            { ikon: '🪁', nama: 'layang-layang' },
            { ikon: '🧸', nama: 'boneka' },
            { ikon: '🚗', nama: 'mobil-mobilan' }
        ];

        // Fungsi untuk mencetak span gambar agar responsif (flex-wrap friendly)
        function renderObjek(jumlah, ikon, coret = false) {
            let html = '';
            for(let i = 0; i < jumlah; i++) {
                if(coret) {
                    html += `
                    <span class="relative inline-block text-3xl md:text-4xl transition-transform hover:scale-110 opacity-40 grayscale m-1">
                        ${ikon}
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="w-full h-1.5 bg-rose-500 rotate-45 rounded-full shadow-sm"></div>
                        </div>
                    </span>`;
                } else {
                    html += `<span class="inline-block text-3xl md:text-4xl transition-transform hover:scale-125 hover:-translate-y-1 cursor-default m-1 drop-shadow-sm">${ikon}</span>`;
                }
            }
            return html;
        }

        function getVisualPenyelesaian(n1, n2, op, soalIndex) {
            // Pilih benda berdasarkan nomor urut soal agar konsisten dan bervariasi
            const benda = bendaVisual[soalIndex % bendaVisual.length];
            let html = `<div class="bg-blue-50/50 p-4 md:p-6 rounded-2xl border border-blue-100">`;

            html += `<h4 class="font-bold text-lg mb-4 text-blue-800 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Mari berhitung menggunakan gambar!
                     </h4>`;

            if (op === '+') {
                let total = n1 + n2;
                html += `<div class="flex flex-col md:flex-row md:items-center gap-4 mb-6 bg-white p-5 rounded-xl shadow-sm border border-slate-100">`;

                // N1
                html += `<div class="flex-1 flex flex-wrap justify-center items-center bg-slate-50 p-3 rounded-lg border border-slate-100 min-h-[80px]">
                            ${renderObjek(n1, benda.ikon)}
                         </div>`;

                html += `<div class="text-3xl font-black text-slate-300 text-center">+</div>`;

                // N2
                html += `<div class="flex-1 flex flex-wrap justify-center items-center bg-slate-50 p-3 rounded-lg border border-slate-100 min-h-[80px]">
                            ${renderObjek(n2, benda.ikon)}
                         </div>`;

                html += `<div class="text-3xl font-black text-slate-300 text-center">=</div>`;

                // Hasil
                html += `<div class="flex-[1.5] flex flex-wrap justify-center items-center bg-yellow-50 p-3 rounded-lg border border-yellow-200 min-h-[80px]">
                            ${renderObjek(total, benda.ikon)}
                         </div>`;

                html += `</div>`;

                // Penjelasan Teks
                html += `<div class="bg-white p-4 rounded-xl border border-slate-100 text-slate-600 space-y-2">
                            <p class="flex items-start gap-2">
                                <span class="bg-blue-100 text-blue-700 font-bold px-2 py-0.5 rounded text-sm mt-0.5">1</span>
                                <span>Kamu memiliki <strong>${n1} ${benda.nama}</strong>.</span>
                            </p>
                            <p class="flex items-start gap-2">
                                <span class="bg-blue-100 text-blue-700 font-bold px-2 py-0.5 rounded text-sm mt-0.5">2</span>
                                <span>Lalu ditambahkan lagi <strong>${n2} ${benda.nama}</strong>.</span>
                            </p>
                            <p class="flex items-start gap-2">
                                <span class="bg-emerald-100 text-emerald-700 font-bold px-2 py-0.5 rounded text-sm mt-0.5">3</span>
                                <span>Jika kita gabungkan dan hitung semuanya, jumlahnya menjadi <strong>${total} ${benda.nama}</strong>.</span>
                            </p>
                         </div>`;

            } else if (op === '-') {
                let sisa = n1 - n2;

                html += `<div class="flex flex-wrap justify-center items-center gap-2 mb-6 bg-white p-6 rounded-xl shadow-sm border border-slate-100 min-h-[120px]">`;
                // Menampilkan benda sisa (normal) dan benda yang dicoret
                html += renderObjek(sisa, benda.ikon);
                html += renderObjek(n2, benda.ikon, true);
                html += `</div>`;

                html += `<div class="bg-white p-4 rounded-xl border border-slate-100 text-slate-600 space-y-2">
                            <p class="flex items-start gap-2">
                                <span class="bg-blue-100 text-blue-700 font-bold px-2 py-0.5 rounded text-sm mt-0.5">1</span>
                                <span>Awalnya ada <strong>${n1} ${benda.nama}</strong>.</span>
                            </p>
                            <p class="flex items-start gap-2">
                                <span class="bg-rose-100 text-rose-700 font-bold px-2 py-0.5 rounded text-sm mt-0.5">2</span>
                                <span>Sebanyak <strong>${n2} ${benda.nama}</strong> dicoret (dikurangi).</span>
                            </p>
                            <p class="flex items-start gap-2">
                                <span class="bg-emerald-100 text-emerald-700 font-bold px-2 py-0.5 rounded text-sm mt-0.5">3</span>
                                <span>Mari hitung ${benda.nama} yang tidak dicoret! Sisanya adalah <strong>${sisa} ${benda.nama}</strong>.</span>
                            </p>
                         </div>`;
            }
            html += `</div>`;
            return html;
        }

        function getLangkahPenyelesaian(n1, n2, op, index) {
            // Logika Pembagian Pedagogik: Visual Gambar jika Penjumlahan/Pengurangan 1 Digit
            if (n1 <= 10 && n2 <= 10 && (op === '+' || op === '-')) {
                return getVisualPenyelesaian(n1, n2, op, index);
            }

            // Fallback: Matematika Bersusun untuk angka di atas 10 atau Operasi Perkalian/Pembagian
            const aStr = n1.toString();
            const bStr = n2.toString();

            let resTxt = `<div class="bg-slate-800 rounded-2xl overflow-hidden shadow-inner p-1">`;
            resTxt += `<div class="bg-slate-900 p-5 rounded-xl overflow-x-auto">`;
            resTxt += `<pre class="text-slate-100 font-mono text-base md:text-lg leading-relaxed inline-block min-w-full">`;

            if (op !== '/') {
                let hasil = (op === '+') ? n1 + n2 : (op === '-') ? n1 - n2 : n1 * n2;
                let maxL = Math.max(aStr.length, bStr.length, hasil.toString().length) + 2;
                const fmt = (v, s = "") => v.toString().padStart(maxL - 1) + " " + s;

                if (op === '+') {
                    let s1 = aStr.padStart(maxL - 2, '0');
                    let s2 = bStr.padStart(maxL - 2, '0');
                    let simpanan = "";
                    let carry = 0;

                    for(let i = s1.length - 1; i >= 0; i--) {
                        let total = parseInt(s1[i]) + parseInt(s2[i]) + carry;
                        if (total > 9 && i > 0) {
                            simpanan = "1" + simpanan;
                            carry = 1;
                        } else {
                            simpanan = " " + simpanan;
                            carry = 0;
                        }
                    }
                    if (simpanan.trim().length > 0) {
                        resTxt += "<span class='text-amber-400 font-bold'>" + " ".repeat(maxL - simpanan.length - 1) + simpanan + "</span>\n";
                    }
                }

                resTxt += fmt(n1) + "\n";
                resTxt += fmt(n2, op === '*' ? '×' : op) + "\n";
                resTxt += "-".repeat(maxL).padStart(maxL) + "\n";

                if (op === '*' && bStr.length > 1) {
                    for (let i = 0; i < bStr.length; i++) {
                        let p = n1 * parseInt(bStr[bStr.length - 1 - i]);
                        resTxt += (p.toString() + " ".repeat(i)).padStart(maxL - 1) + "\n";
                    }
                    resTxt += "-".repeat(maxL).padStart(maxL) + " +\n";
                }
                resTxt += `<span class="text-emerald-400 font-bold">${fmt(hasil)}</span>`;
            } else {
                let n1S = n1.toString();
                let n2S = n2.toString();
                let hasilBagi = Math.floor(n1 / n2);
                let indent = n2S.length + 3;

                resTxt += `Hasil: <span class="text-emerald-400 font-bold">${hasilBagi}</span>\n\n`;
                resTxt += " ".repeat(indent) + `<span class="text-amber-400 font-bold">${hasilBagi}</span>` + "\n";
                resTxt += " ".repeat(n2S.length + 1) + "_______" + "\n";
                resTxt += `${n2} / ${n1S}\n`;

                let sisa = 0;
                let step = 0;

                for (let i = 0; i < n1S.length; i++) {
                    sisa = (sisa * 10) + parseInt(n1S[i]);
                    if (sisa >= n2) {
                        let kali = Math.floor(sisa / n2);
                        let hKali = kali * n2;

                        if (step > 0) {
                            resTxt += " ".repeat(indent + i - (sisa.toString().length - 1)) + sisa + "\n";
                        }
                        resTxt += " ".repeat(indent + i - (hKali.toString().length - 1)) + `<span class="text-rose-400">${hKali}</span>` + "\n";
                        let gLen = Math.max(sisa.toString().length, hKali.toString().length);
                        resTxt += " ".repeat(indent + i - (gLen - 1)) + "-".repeat(gLen) + " -\n";

                        sisa = sisa - hKali;
                        step++;

                        if (i === n1S.length - 1) {
                            resTxt += " ".repeat(indent + i - (sisa.toString().length - 1)) + `<span class="text-emerald-400 font-bold">${sisa}</span>`;
                        }
                    } else if (i === n1S.length - 1) {
                        resTxt += " ".repeat(indent + i - (sisa.toString().length - 1)) + `<span class="text-emerald-400 font-bold">${sisa}</span>`;
                    }
                }
            }
            resTxt += `</pre></div></div>`;
            return resTxt;
        }

        document.addEventListener("DOMContentLoaded", () => {
            hasilData.forEach((item, index) => {
                const container = document.getElementById(`langkah-${index}`);
                if(container) {
                    container.innerHTML = getLangkahPenyelesaian(item.soal.n1, item.soal.n2, item.soal.op, index);
                }
            });
        });
    </script>
</body>

</html>