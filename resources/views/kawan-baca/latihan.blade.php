<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Latihan Membaca</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .fade-in {
            animation: fadeIn 0.4s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Font khusus yang ramah anak jika tersedia */
        body {
            font-family: 'Quicksand', 'Segoe UI', sans-serif;
        }
    </style>
</head>

<body class="bg-blue-50 min-h-screen flex flex-col font-sans">

    <header class="bg-white shadow-sm p-4 sticky top-0 z-10">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-2xl">📚</span>
                <h1 class="font-bold text-slate-700 text-lg md:text-xl">Kawan Baca</h1>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm font-bold text-blue-600 bg-blue-100 px-3 py-1 rounded-full" id="progress-text">
                    1 / {{ count($materi) }}
                </span>
                <a href="{{ route('baca.index') }}"
                    class="text-sm font-bold text-slate-500 hover:text-rose-500 transition">Tutup ✖</a>
            </div>
        </div>
        <div class="max-w-4xl mx-auto mt-4 bg-slate-100 h-2 rounded-full overflow-hidden">
            <div id="progress-bar" class="bg-blue-500 h-full transition-all duration-300" style="width: 0%;"></div>
        </div>
    </header>

    <main class="flex-grow flex items-center justify-center p-4 md:p-8 w-full max-w-4xl mx-auto">
        <div id="bacaan-container"
            class="w-full bg-white p-8 md:p-12 rounded-3xl shadow-sm border border-slate-100 text-center fade-in">
        </div>
    </main>

    <footer class="bg-white border-t border-slate-200 p-4">
        <div class="max-w-4xl mx-auto flex justify-between items-center gap-4">
            <button id="btn-prev" onclick="gantiSlide(-1)"
                class="px-6 py-3 rounded-xl font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition hidden">
                ⬅ Sebelumnya
            </button>

            <div class="flex-grow"></div>

            <button id="btn-next" onclick="gantiSlide(1)"
                class="px-8 py-3 rounded-xl font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-lg shadow-blue-200 hover:-translate-y-1 transition text-lg">
                Selanjutnya ➡
            </button>
        </div>
    </footer>

    <div id="modal-selesai"
        class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
        <div class="bg-white rounded-3xl p-8 max-w-md w-full text-center shadow-2xl pop-in">
            <div class="text-6xl mb-4">🎉</div>
            <h2 class="text-3xl font-black text-emerald-500 mb-2">Hebat Sekali!</h2>
            <p class="text-slate-600 font-medium mb-6 leading-relaxed">Kamu telah menyelesaikan semua tugas membaca hari
                ini. Teruslah berlatih agar makin pintar!</p>
            <a href="{{ route('baca.index') }}"
                class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl transition">
                Kembali ke Menu
            </a>
        </div>
    </div>

    <script>
        const materiList = @json($materi);
        let currentIndex = 0;
        const totalItems = materiList.length;

        const container = document.getElementById('bacaan-container');
        const progressText = document.getElementById('progress-text');
        const progressBar = document.getElementById('progress-bar');
        const btnPrev = document.getElementById('btn-prev');
        const btnNext = document.getElementById('btn-next');
        const modalSelesai = document.getElementById('modal-selesai');

        function renderSlide() {
            const data = materiList[currentIndex];
            let html = `<div class="fade-in">`;

            // Label Tipe di atas konten
            let tipeLabel = data.tipe.toUpperCase();
            let labelColor = data.tipe === 'kata' ? 'bg-amber-100 text-amber-700' :
                             data.tipe === 'kalimat' ? 'bg-emerald-100 text-emerald-700' :
                             data.tipe === 'paragraf' ? 'bg-purple-100 text-purple-700' :
                             'bg-rose-100 text-rose-700';

            html += `<div class="inline-block px-4 py-1 rounded-full text-sm font-black tracking-widest mb-8 ${labelColor}">
                        LATIHAN ${tipeLabel}
                     </div>`;

            // Cek apakah konten berupa array (Cerita Penuh / Bacaan) atau Teks Biasa
            if (typeof data.konten === 'object' && data.konten !== null) {
                // Render format Cerita
                html += `<h2 class="text-3xl md:text-4xl font-black text-slate-800 mb-6 text-left">${data.konten.judul}</h2>`;
                html += `<p class="text-xl md:text-2xl text-slate-700 leading-loose text-left tracking-wide">${data.konten.isi}</p>`;
            } else {
                // Render format teks biasa (Kata, Kalimat, Paragraf)
                let textSizeClass = data.tipe === 'kata' ? 'text-6xl md:text-8xl text-center' :
                                    data.tipe === 'kalimat' ? 'text-3xl md:text-5xl text-center leading-normal' :
                                    'text-2xl md:text-3xl text-left leading-loose';

                html += `<div class="font-bold text-slate-800 ${textSizeClass} tracking-wide">
                            ${data.konten}
                         </div>`;
            }

            html += `</div>`;
            container.innerHTML = html;

            // Update Progress Bar & Text
            progressText.innerText = `${currentIndex + 1} / ${totalItems}`;
            progressBar.style.width = `${((currentIndex + 1) / totalItems) * 100}%`;

            // Atur Tombol Navigasi
            btnPrev.style.display = currentIndex === 0 ? 'none' : 'block';

            if (currentIndex === totalItems - 1) {
                btnNext.innerHTML = 'Selesai ✨';
                btnNext.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                btnNext.classList.add('bg-emerald-500', 'hover:bg-emerald-600', 'shadow-emerald-200');
            } else {
                btnNext.innerHTML = 'Selanjutnya ➡';
                btnNext.classList.add('bg-blue-600', 'hover:bg-blue-700');
                btnNext.classList.remove('bg-emerald-500', 'hover:bg-emerald-600', 'shadow-emerald-200');
            }
        }

        function gantiSlide(arah) {
            if (arah === 1 && currentIndex === totalItems - 1) {
                // Jika tombol selesai diklik
                modalSelesai.classList.remove('hidden');
                return;
            }

            currentIndex += arah;

            // Pengaman index
            if (currentIndex < 0) currentIndex = 0;
            if (currentIndex >= totalItems) currentIndex = totalItems - 1;

            renderSlide();
        }

        // Jalankan saat pertama kali dimuat
        renderSlide();
    </script>
</body>

</html>