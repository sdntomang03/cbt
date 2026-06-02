<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Preview Pembuatan Ujian / Materi</title>

    <!-- Tailwind CSS & Typography -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tailwindcss/typography@0.5.9/dist/typography.min.css">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <!-- KaTeX untuk Rumus Matematika -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css">
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/contrib/auto-render.min.js"></script>

    <!-- TinyMCE Editor -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        /* Styling Scrollbar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        /* Menyesuaikan border TinyMCE dengan desain Tailwind */
        .tox-tinymce {
            border-radius: 0.75rem !important;
            border-color: #cbd5e1 !important;
        }
    </style>
</head>

<body class="bg-slate-200 h-screen overflow-hidden text-slate-800 font-sans">

    {{-- KONTANER UTAMA ALPINE.JS --}}
    <div x-data="examPreview()" class="flex flex-col lg:flex-row h-full w-full">

        {{-- ========================================== --}}
        {{-- BAGIAN KIRI: FORM INPUT (EDITOR) --}}
        {{-- ========================================== --}}
        <div class="w-full lg:w-1/3 bg-white border-r border-slate-300 flex flex-col h-full shadow-lg z-10">
            <div class="p-5 border-b border-slate-100 bg-slate-50 flex justify-between items-center shrink-0">
                <h2 class="font-black text-lg text-slate-800"><i class="fas fa-edit text-indigo-500 mr-2"></i> Editor
                    Materi / Ujian</h2>
            </div>

            <div class="p-6 overflow-y-auto custom-scrollbar flex-1 space-y-5 pb-20">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Judul Ujian / Materi</label>
                    <input type="text" x-model="exam.title"
                        class="w-full p-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 transition-all">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Deskripsi Singkat</label>
                    <textarea x-model="exam.description" rows="2"
                        class="w-full p-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Durasi (Menit)</label>
                        <input type="number" x-model="exam.duration"
                            class="w-full p-3 border border-slate-300 rounded-xl">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Jumlah Soal</label>
                        <input type="number" x-model="exam.questions_count"
                            class="w-full p-3 border border-slate-300 rounded-xl">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Konten / Panduan Khusus</label>
                    <p class="text-xs text-slate-500 mb-2">
                        Pilih <b>Template</b> untuk struktur tata letak. Pilih <b>Formats</b> untuk warna. Ketik
                        <b>$$rumus$$</b> untuk matematika.
                    </p>

                    {{-- Container TinyMCE --}}
                    <textarea x-ref="tinyEditor" class="hidden"></textarea>
                </div>
            </div>
        </div>

        {{-- ========================================== --}}
        {{-- BAGIAN KANAN: LIVE PREVIEW --}}
        {{-- ========================================== --}}
        <div class="w-full lg:w-2/3 bg-slate-50 overflow-y-auto custom-scrollbar relative">
            <div
                class="sticky top-0 bg-amber-500 text-white text-center py-2 text-xs font-black tracking-widest shadow-md z-50">
                <i class="fas fa-eye mr-2"></i> LIVE PREVIEW
            </div>

            <div class="pb-20">
                {{-- HERO SECTION PREVIEW --}}
                <div class="w-full bg-slate-900 relative overflow-hidden pt-16 pb-20 transition-all">
                    <div class="absolute inset-0 bg-gradient-to-r from-indigo-800 to-slate-900 opacity-90"></div>
                    <div class="max-w-5xl mx-auto px-6 relative z-10 text-center">
                        <span
                            class="inline-block px-4 py-1.5 rounded-full bg-indigo-500/20 text-indigo-300 text-xs font-black tracking-widest uppercase mb-4 border border-indigo-400/30">
                            <i class="fas fa-book-open mr-1"></i> Mode Siswa
                        </span>

                        <h1 class="text-3xl md:text-5xl font-black text-white tracking-tight mb-4 max-w-4xl mx-auto leading-tight"
                            x-text="exam.title || 'Judul Akan Tampil di Sini'"></h1>

                        <p class="text-base text-slate-300 max-w-2xl mx-auto font-medium"
                            x-text="exam.description || 'Deskripsi akan tampil di sini...'"></p>
                    </div>
                </div>

                {{-- KONTEN & SIDEBAR PREVIEW --}}
                <div class="max-w-5xl mx-auto px-6 -mt-10 relative z-20">
                    <div class="flex flex-col md:flex-row gap-6">

                        {{-- KIRI: ARTIKEL MATERI --}}
                        <div class="md:w-2/3">
                            <article
                                class="bg-white rounded-[2rem] shadow-sm border border-slate-200 p-8 overflow-hidden transition-all">
                                <h2 class="text-xl font-black text-slate-800 mb-6 border-b border-slate-100 pb-4">Isi
                                    Materi / Panduan</h2>

                                {{-- Preview Konten dari TinyMCE dirender di sini --}}
                                <div id="preview-content" class="prose prose-indigo max-w-none text-slate-700"
                                    x-html="exam.content || '<p class=\'text-slate-400 italic\'>Belum ada konten yang ditulis.</p>'">
                                </div>
                            </article>
                        </div>

                        {{-- KANAN: SIDEBAR INFO --}}
                        <div class="md:w-1/3">
                            <div
                                class="bg-white rounded-[2rem] shadow-xl shadow-indigo-500/5 border border-slate-200 p-6 sticky top-12">
                                <h3 class="font-black text-slate-800 text-lg mb-6">Detail Informasi</h3>
                                <div class="space-y-3 mb-6">
                                    <div
                                        class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100">
                                        <div class="flex items-center gap-2 text-slate-600 font-bold text-sm"><i
                                                class="fas fa-list-ol text-indigo-500 w-5"></i> Jumlah Soal</div>
                                        <div class="font-black text-slate-800" x-text="exam.questions_count"></div>
                                    </div>
                                    <div
                                        class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100">
                                        <div class="flex items-center gap-2 text-slate-600 font-bold text-sm"><i
                                                class="fas fa-stopwatch text-emerald-500 w-5"></i> Durasi Waktu</div>
                                        <div class="font-black text-slate-800"><span x-text="exam.duration"></span>
                                            Menit</div>
                                    </div>
                                </div>
                                <button disabled
                                    class="w-full bg-slate-200 text-slate-400 font-bold py-3 px-4 rounded-xl text-sm text-center cursor-not-allowed border-2 border-dashed border-slate-300">
                                    Mulai Kerjakan (Preview)
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPT ALPINE & TINYMCE --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('examPreview', () => ({
                // Data default saat halaman dimuat
                exam: {
                    title: 'Materi Geometri & Trigonometri Dasar',
                    description: 'Pelajari konsep dasar segitiga dan bangun ruang sebelum mengerjakan latihan soal.',
                    duration: 90,
                    questions_count: 25,
                    content: ''
                },

                init() {
                    // Konfigurasi Utama TinyMCE
                    tinymce.init({
                        target: this.$refs.tinyEditor,
                        height: 600,
                        menubar: false,
                        plugins: 'code table lists link template image',
                        toolbar: 'template styles | bold italic underline | alignleft aligncenter alignright | bullist numlist | table image code',
                        valid_elements: '*[*]', // Mencegah TinyMCE menghapus class Tailwind

                        // ==========================================
                        // THEME WARNA & ELEMEN (Menu 'Formats')
                        // ==========================================
                        style_formats: [
                            {
                                title: '🎨 Tema Blok Warna', items: [
                                    { title: 'Card Biru (Info/Fakta)', block: 'div', classes: 'bg-blue-50 border-l-4 border-blue-500 text-blue-900 p-5 rounded-r-xl my-4 shadow-sm' },
                                    { title: 'Card Hijau (Rumus/Sukses)', block: 'div', classes: 'bg-emerald-50 border border-emerald-200 text-emerald-900 p-5 rounded-xl my-4 shadow-sm' },
                                    { title: 'Card Kuning (Peringatan/Note)', block: 'div', classes: 'bg-amber-50 border-t-4 border-amber-500 text-amber-900 p-5 rounded-b-xl my-4 shadow-sm' },
                                    { title: 'Card Ungu (Definisi/Istilah)', block: 'div', classes: 'bg-purple-50 text-purple-900 p-5 rounded-2xl border-2 border-dashed border-purple-300 my-4' }
                                ]
                            },
                            {
                                title: '🔠 Tipografi Materi', items: [
                                    { title: 'Judul Bab (Besar)', block: 'h2', classes: 'text-3xl font-black text-slate-800 mb-4 border-b-2 border-indigo-500 pb-2 inline-block' },
                                    { title: 'Sub-judul (Sedang)', block: 'h3', classes: 'text-xl font-bold text-indigo-700 mt-6 mb-3' },
                                    { title: 'Teks Paragraf Awal (Lead)', block: 'p', classes: 'text-lg text-slate-600 font-medium leading-relaxed mb-4' },
                                    { title: 'Teks Highlight Kuning', inline: 'span', classes: 'bg-yellow-200 text-yellow-900 px-1 rounded' },
                                ]
                            }
                        ],

                        // ==========================================
                        // TEMPLATE TATA LETAK / LAYOUT KOSONG
                        // ==========================================
                       templates: [
    {
        title: '📖 Materi + Definisi + Contoh Soal',
        description: 'Layout standar paling sering dipakai untuk satu topik lengkap.',
        content: `
            <h2 class="text-3xl font-black text-slate-800 mb-4 border-b-2 border-indigo-500 pb-2 inline-block">Judul Materi</h2>
            <p class="text-lg text-slate-600 mb-6">Pengantar materi di sini.</p>
            <div class="bg-blue-50 border-l-4 border-blue-500 p-5 rounded-r-xl my-6">
                <h4 class="font-bold mb-2">Definisi Penting</h4>
                <p>Masukkan definisi atau rumus utama ($$ y = mx + c $$).</p>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden my-6 shadow-sm">
                <div class="bg-slate-100 px-5 py-3 border-b border-slate-200"><h4 class="font-bold text-slate-700">Contoh Soal</h4></div>
                <div class="p-5">
                    <p class="mb-4">Tuliskan soal di sini.</p>
                    <div class="bg-emerald-50 text-emerald-900 p-4 rounded-xl border border-emerald-100">
                        <strong>Pembahasan:</strong>
                        <p class="mt-2">Jelaskan langkah penyelesaian.</p>
                    </div>
                </div>
            </div>
        `
    },
    {
        title: '🗂️ 3 Card Kolom (Konsep / Pilar)',
        description: 'Tiga kartu berjajar untuk memecah 3 konsep utama agar mudah dibaca.',
        content: `
            <h3 class="text-2xl font-black text-slate-800 mb-6 text-center">3 Pilar Utama Materi</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 my-8">
                <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-sm hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center text-xl mb-4"><i class="fas fa-rocket"></i></div>
                    <h4 class="text-lg font-bold text-slate-800 mb-2">Konsep Inti</h4>
                    <p class="text-sm text-slate-600">Jelaskan ide pokok pertama di sini.</p>
                </div>
                <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-sm hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center text-xl mb-4"><i class="fas fa-chart-pie"></i></div>
                    <h4 class="text-lg font-bold text-slate-800 mb-2">Analisis Data</h4>
                    <p class="text-sm text-slate-600">Jelaskan fungsi analitis dari konsep pertama.</p>
                </div>
                <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-sm hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center text-xl mb-4"><i class="fas fa-lightbulb"></i></div>
                    <h4 class="text-lg font-bold text-slate-800 mb-2">Kesimpulan</h4>
                    <p class="text-sm text-slate-600">Benang merah agar siswa mudah mengingatnya.</p>
                </div>
            </div>
        `
    },
    {
        title: '📊 Tabel Perbandingan',
        description: 'Tabel modern untuk membandingkan dua teori, konsep, atau pendekatan.',
        content: `
            <h3 class="text-2xl font-black text-slate-800 mb-4">Tabel Komparasi</h3>
            <p class="text-slate-600 mb-6">Perbedaan mendasar antara Teori A dan Teori B.</p>
            <div class="overflow-x-auto my-6 border border-slate-200 rounded-2xl shadow-sm not-prose">
                <table class="w-full text-left border-collapse min-w-[600px]">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-700 text-sm uppercase tracking-wider">
                            <th class="p-4 font-black w-1/3">Aspek</th>
                            <th class="p-4 font-black text-indigo-600 w-1/3">Teori A</th>
                            <th class="p-4 font-black text-rose-600 w-1/3">Teori B</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-600 divide-y divide-slate-200">
                        <tr class="hover:bg-slate-50"><td class="p-4 font-bold text-slate-800">Definisi</td><td class="p-4">Sifat dari A</td><td class="p-4">Sifat dari B</td></tr>
                        <tr class="hover:bg-slate-50"><td class="p-4 font-bold text-slate-800">Kelebihan</td><td class="p-4 text-emerald-600">✓ Lebih cepat</td><td class="p-4 text-emerald-600">✓ Lebih akurat</td></tr>
                    </tbody>
                </table>
            </div>
        `
    },
    {
        title: '❓ FAQ / Akordion',
        description: 'Tanya jawab SEO-friendly yang bisa dibuka/tutup, cocok untuk menjawab pertanyaan umum.',
        content: `
            <div class="my-10 space-y-4 not-prose">
                <h3 class="text-2xl font-black text-slate-800 mb-6 border-b border-slate-200 pb-3">Tanya Jawab (FAQ)</h3>
                <details class="group bg-white border border-slate-200 rounded-2xl shadow-sm">
                    <summary class="flex items-center justify-between cursor-pointer p-5 font-bold text-slate-800">
                        <span>Pertanyaan pertama di sini?</span>
                        <i class="fas fa-chevron-down group-open:rotate-180 transition-transform text-indigo-500"></i>
                    </summary>
                    <div class="p-5 pt-0 mt-2 border-t border-slate-100 text-slate-600">Jawaban pertama di sini.</div>
                </details>
                <details class="group bg-white border border-slate-200 rounded-2xl shadow-sm">
                    <summary class="flex items-center justify-between cursor-pointer p-5 font-bold text-slate-800">
                        <span>Pertanyaan kedua di sini?</span>
                        <i class="fas fa-chevron-down group-open:rotate-180 transition-transform text-indigo-500"></i>
                    </summary>
                    <div class="p-5 pt-0 mt-2 border-t border-slate-100 text-slate-600">Jawaban kedua di sini.</div>
                </details>
            </div>
        `
    },
    {
        title: '🖼️ Card Gambar + Teks (Studi Kasus)',
        description: 'Layout horizontal elegan untuk studi kasus, profil tokoh, atau fenomena.',
        content: `
            <div class="flex flex-col md:flex-row bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm my-8 not-prose hover:shadow-md transition-shadow">
                <div class="md:w-2/5 bg-slate-200 min-h-[250px] flex flex-col items-center justify-center text-slate-400 p-6 text-center">
                    <i class="fas fa-image text-4xl mb-3"></i>
                    <span class="text-sm font-bold uppercase tracking-widest">Area Gambar</span>
                </div>
                <div class="md:w-3/5 p-6 md:p-8 flex flex-col justify-center bg-slate-50/50">
                    <span class="text-xs font-black tracking-widest uppercase text-indigo-500 mb-2">Studi Kasus</span>
                    <h3 class="text-2xl font-bold text-slate-800 mb-3">Penerapan di Dunia Nyata</h3>
                    <p class="text-slate-600 mb-5 leading-relaxed">Deskripsi mendalam tentang studi kasus atau konteks penerapan materi.</p>
                    <span class="inline-block bg-white border border-slate-200 text-slate-600 px-3 py-1 rounded-lg text-xs font-bold shadow-sm">
                        <i class="fas fa-tag mr-1"></i> Fakta Menarik
                    </span>
                </div>
            </div>
        `
    },
    {
        title: '✅ Soal Pilihan Ganda Interaktif',
        description: 'Template soal PG dengan feedback langsung benar/salah menggunakan Alpine.js.',
        content: `
            <div x-data="{ answered: false, isCorrect: null }" class="bg-white border border-slate-200 rounded-2xl p-6 my-6 shadow-sm not-prose">
                <div class="flex items-center gap-3 mb-4">
                    <span class="bg-indigo-600 text-white text-sm font-bold w-8 h-8 rounded-full flex items-center justify-center">1</span>
                    <h4 class="font-bold text-slate-800">Tuliskan soal latihan di sini</h4>
                </div>

                <div class="space-y-3">
                    <!-- Tombol Benar -->
                    <button @click="answered = true; isCorrect = true"
                            :disabled="answered"
                            class="w-full text-left px-4 py-3 border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors font-medium disabled:opacity-60 disabled:cursor-not-allowed focus:outline-none"
                            :class="answered && isCorrect === true ? 'ring-2 ring-emerald-500 bg-emerald-50' : ''">
                        A. Jawaban Benar
                    </button>

                    <!-- Tombol Salah -->
                    <button @click="answered = true; isCorrect = false"
                            :disabled="answered"
                            class="w-full text-left px-4 py-3 border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors disabled:opacity-60 disabled:cursor-not-allowed focus:outline-none">
                        B. Jawaban Salah
                    </button>

                    <!-- Tombol Salah -->
                    <button @click="answered = true; isCorrect = false"
                            :disabled="answered"
                            class="w-full text-left px-4 py-3 border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors disabled:opacity-60 disabled:cursor-not-allowed focus:outline-none">
                        C. Jawaban Salah
                    </button>

                    <!-- Tombol Salah -->
                    <button @click="answered = true; isCorrect = false"
                            :disabled="answered"
                            class="w-full text-left px-4 py-3 border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors disabled:opacity-60 disabled:cursor-not-allowed focus:outline-none">
                        D. Jawaban Salah
                    </button>
                </div>

                <!-- Kotak Feedback (Hanya muncul setelah dijawab) -->
                <div x-show="answered"
                     x-transition.opacity.duration.300ms
                     style="display: none;"
                     class="mt-5 p-5 rounded-xl border"
                     :class="isCorrect ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-rose-50 border-rose-200 text-rose-900'">

                    <div class="flex items-center gap-2 font-black mb-2" :class="isCorrect ? 'text-emerald-700' : 'text-rose-700'">
                        <i class="fas" :class="isCorrect ? 'fa-check-circle' : 'fa-times-circle'"></i>
                        <span x-text="isCorrect ? 'Tepat Sekali!' : 'Kurang Tepat!'"></span>
                    </div>

                    <p class="text-sm opacity-90"><strong>Pembahasan:</strong> Tuliskan alasan mengapa jawaban A yang paling benar di sini.</p>
                </div>
            </div>
        `
    },
    {
        title: '⏳ Timeline / Langkah-Langkah',
        description: 'Alur kronologis untuk sejarah, proses, atau tahapan suatu konsep.',
        content: `
            <h3 class="text-2xl font-black text-slate-800 mb-6">Tahapan Proses</h3>
            <div class="relative not-prose">
                <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-slate-200 -z-10"></div>
                <div class="space-y-6">
                    <div class="flex gap-4">
                        <div class="w-8 h-8 bg-indigo-100 text-indigo-700 rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0">1</div>
                        <div class="bg-white border border-slate-200 rounded-2xl p-5 flex-1 shadow-sm">
                            <h4 class="font-bold text-slate-800 mb-1">Langkah Pertama</h4>
                            <p class="text-sm text-slate-600">Deskripsi langkah pertama di sini.</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="w-8 h-8 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0">2</div>
                        <div class="bg-white border border-slate-200 rounded-2xl p-5 flex-1 shadow-sm">
                            <h4 class="font-bold text-slate-800 mb-1">Langkah Kedua</h4>
                            <p class="text-sm text-slate-600">Deskripsi langkah kedua di sini.</p>
                        </div>
                    </div>
                </div>
            </div>
        `
    },
    {
        title: '✍️ Latihan Essay / Uraian',
        description: 'Template soal uraian dengan panduan jawaban dan rubrik penilaian.',
        content: `
            <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden my-6 shadow-sm not-prose">
                <div class="bg-slate-800 text-white px-5 py-3 flex items-center justify-between">
                    <h4 class="font-bold">Soal Uraian</h4>
                    <span class="text-xs bg-white/20 px-2 py-1 rounded-lg">15 Poin</span>
                </div>
                <div class="p-5">
                    <p class="text-slate-800 font-medium mb-4">Tuliskan soal uraian Anda di sini dengan jelas dan terstruktur.</p>
                    <div class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded-r-xl mb-4">
                        <p class="font-bold text-amber-800 mb-1">Panduan Jawaban</p>
                        <p class="text-sm text-amber-700">Poin-poin yang harus ada dalam jawaban siswa.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span class="text-xs bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full font-medium">3 poin: lengkap</span>
                        <span class="text-xs bg-amber-100 text-amber-700 px-3 py-1 rounded-full font-medium">2 poin: cukup</span>
                        <span class="text-xs bg-red-100 text-red-700 px-3 py-1 rounded-full font-medium">1 poin: kurang</span>
                    </div>
                </div>
            </div>
        `
    },
    {
        title: '📚 Daftar Kosakata / Glosarium',
        description: 'Daftar istilah penting dengan definisi, cocok untuk pelajaran bahasa atau IPA.',
        content: `
            <h3 class="text-2xl font-black text-slate-800 mb-6">Glosarium Penting</h3>
            <div class="divide-y divide-slate-200 not-prose">
                <div class="flex gap-4 py-4 items-start">
                    <span class="bg-indigo-100 text-indigo-700 font-black w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 text-sm">1</span>
                    <div>
                        <h4 class="font-bold text-slate-800 mb-1">Istilah Pertama</h4>
                        <p class="text-sm text-slate-600">Definisi dan penjelasan istilah ini dalam konteks materi.</p>
                        <span class="inline-block mt-2 bg-slate-100 text-slate-600 text-xs px-2 py-0.5 rounded-md">contoh: kalimat penggunaan</span>
                    </div>
                </div>
                <div class="flex gap-4 py-4 items-start">
                    <span class="bg-blue-100 text-blue-700 font-black w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 text-sm">2</span>
                    <div>
                        <h4 class="font-bold text-slate-800 mb-1">Istilah Kedua</h4>
                        <p class="text-sm text-slate-600">Definisi istilah kedua dalam konteks materi.</p>
                    </div>
                </div>
            </div>
        `
    },
    {
        title: '📐 Rumus + Penurunan Formula',
        description: 'Template khusus matematik/fisika dengan box rumus besar dan langkah penurunan.',
        content: `
            <div class="my-8 not-prose">
                <h3 class="text-2xl font-black text-slate-800 mb-4">Nama Rumus</h3>
                <div class="bg-indigo-50 border border-indigo-200 rounded-2xl p-6 text-center my-6">
                    <div class="text-4xl font-mono font-black text-indigo-800 mb-2">y = mx + c</div>
                    <p class="text-indigo-600 text-sm font-medium">Nama / Deskripsi Rumus</p>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div class="bg-blue-50 border border-blue-100 p-3 rounded-xl text-center">
                        <div class="font-black text-blue-700 text-lg">y</div>
                        <div class="text-xs text-blue-600 mt-1">Nama Variabel</div>
                    </div>
                    <div class="bg-blue-50 border border-blue-100 p-3 rounded-xl text-center">
                        <div class="font-black text-blue-700 text-lg">m</div>
                        <div class="text-xs text-blue-600 mt-1">Nama Variabel</div>
                    </div>
                    <div class="bg-blue-50 border border-blue-100 p-3 rounded-xl text-center">
                        <div class="font-black text-blue-700 text-lg">x</div>
                        <div class="text-xs text-blue-600 mt-1">Nama Variabel</div>
                    </div>
                    <div class="bg-blue-50 border border-blue-100 p-3 rounded-xl text-center">
                        <div class="font-black text-blue-700 text-lg">c</div>
                        <div class="text-xs text-blue-600 mt-1">Nama Variabel</div>
                    </div>
                </div>
            </div>
        `
    },
    {
        title: '🧠 Peta Konsep / Mind Map',
        description: 'Visualisasi hubungan antar konsep dalam format peta pikiran sederhana.',
        content: `
            <div class="my-8 text-center not-prose">
                <h3 class="text-2xl font-black text-slate-800 mb-6">Peta Konsep: [Topik]</h3>
                <div class="inline-block bg-indigo-600 text-white font-black text-lg px-6 py-3 rounded-2xl shadow-md mb-6">Topik Utama</div>
                <div class="flex flex-wrap justify-center gap-4 mb-4">
                    <div class="bg-white border-2 border-indigo-200 rounded-xl p-4 text-center w-[140px] shadow-sm">
                        <div class="text-indigo-600 font-bold mb-2">Sub Topik A</div>
                        <div class="text-xs text-slate-500 space-y-1"><div>Detail A1</div><div>Detail A2</div></div>
                    </div>
                    <div class="bg-white border-2 border-blue-200 rounded-xl p-4 text-center w-[140px] shadow-sm">
                        <div class="text-blue-600 font-bold mb-2">Sub Topik B</div>
                        <div class="text-xs text-slate-500 space-y-1"><div>Detail B1</div><div>Detail B2</div></div>
                    </div>
                    <div class="bg-white border-2 border-emerald-200 rounded-xl p-4 text-center w-[140px] shadow-sm">
                        <div class="text-emerald-600 font-bold mb-2">Sub Topik C</div>
                        <div class="text-xs text-slate-500 space-y-1"><div>Detail C1</div><div>Detail C2</div></div>
                    </div>
                </div>
            </div>
        `
    },
    {
        title: '📑 2 Kolom: Materi + Catatan Pinggir',
        description: 'Layout seperti buku teks dengan catatan penting di samping kanan.',
        content: `
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 my-8 not-prose">
                <div class="md:col-span-2 prose prose-slate max-w-none">
                    <h3 class="text-xl font-black text-slate-800 mb-4">Judul Bagian Materi</h3>
                    <p class="text-slate-600 leading-relaxed mb-4">Konten utama materi di sini. Penjelasan detail dan komprehensif mengenai topik yang sedang dibahas.</p>
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-xl">
                        <p class="text-blue-900 text-sm font-medium">Poin kunci atau rumus penting.</p>
                    </div>
                </div>
                <div class="border-l border-slate-200 pl-6">
                    <p class="text-xs font-black uppercase tracking-widest text-slate-400 mb-4">Catatan Pinggir</p>
                    <div class="space-y-4">
                        <div class="border-l-2 border-amber-400 pl-3">
                            <p class="text-xs font-bold text-slate-700 mb-1">Ingat!</p>
                            <p class="text-xs text-slate-500">Poin penting yang harus diingat.</p>
                        </div>
                        <div class="border-l-2 border-indigo-400 pl-3">
                            <p class="text-xs font-bold text-slate-700 mb-1">Tips Soal</p>
                            <p class="text-xs text-slate-500">Strategi cepat mengerjakan.</p>
                        </div>
                    </div>
                </div>
            </div>
        `
    },
    {
        title: '🪜 Pembahasan Langkah demi Langkah',
        description: 'Step-by-step solving yang visual dan terstruktur untuk soal bertahap.',
        content: `
            <div class="my-8 not-prose">
                <div class="bg-slate-800 text-white rounded-2xl p-5 mb-6">
                    <p class="text-sm text-slate-300 mb-1 uppercase tracking-wider text-xs font-bold">Soal</p>
                    <p class="font-medium text-lg">Tuliskan soal yang akan dibahas di sini.</p>
                </div>
                <div class="space-y-4">
                    <div class="flex gap-4">
                        <div class="flex flex-col items-center">
                            <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-700 font-black flex items-center justify-center flex-shrink-0">1</div>
                            <div class="flex-1 w-0.5 bg-slate-200 my-2"></div>
                        </div>
                        <div class="bg-white border border-slate-200 rounded-2xl p-4 flex-1 mb-2 shadow-sm">
                            <h5 class="font-bold text-slate-700 mb-2 text-sm uppercase tracking-wide">Identifikasi</h5>
                            <p class="text-sm text-slate-600">Tulis apa yang diketahui dan apa yang ditanya.</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex flex-col items-center">
                            <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 font-black flex items-center justify-center flex-shrink-0">2</div>
                            <div class="flex-1 w-0.5 bg-slate-200 my-2"></div>
                        </div>
                        <div class="bg-white border border-slate-200 rounded-2xl p-4 flex-1 mb-2 shadow-sm">
                            <h5 class="font-bold text-slate-700 mb-2 text-sm uppercase tracking-wide">Penerapan Rumus</h5>
                            <p class="text-sm text-slate-600">$$ s = v \\times t $$</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div>
                            <div class="w-9 h-9 rounded-full bg-emerald-100 text-emerald-700 font-black flex items-center justify-center flex-shrink-0">3</div>
                        </div>
                        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 flex-1 shadow-sm">
                            <h5 class="font-bold text-emerald-700 mb-2 text-sm uppercase tracking-wide">Kesimpulan</h5>
                            <p class="text-sm text-emerald-800 font-medium">Jadi, jawaban akhir adalah ...</p>
                        </div>
                    </div>
                </div>
            </div>
        `
    },
    {
        title: '🚥 Indikator Tingkat Kesulitan',
        description: 'Card dengan label level (mudah/sedang/sulit) untuk tiap subtopik atau soal.',
        content: `
            <h3 class="text-2xl font-black text-slate-800 mb-6">Peta Soal Latihan</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 not-prose">
                <div class="bg-white border border-emerald-200 rounded-2xl p-5 shadow-sm">
                    <span class="inline-block bg-emerald-100 text-emerald-700 text-xs font-black px-3 py-1 rounded-full mb-3">Mudah</span>
                    <h4 class="font-bold text-slate-800 mb-1">Operasi Dasar</h4>
                    <p class="text-xs text-slate-500 mb-3">10 soal • ±15 menit</p>
                    <div class="h-1.5 bg-emerald-100 rounded-full overflow-hidden">
                        <div class="h-full bg-emerald-500 rounded-full" style="width: 90%"></div>
                    </div>
                </div>
                <div class="bg-white border border-amber-200 rounded-2xl p-5 shadow-sm">
                    <span class="inline-block bg-amber-100 text-amber-700 text-xs font-black px-3 py-1 rounded-full mb-3">Sedang</span>
                    <h4 class="font-bold text-slate-800 mb-1">Penerapan Rumus</h4>
                    <p class="text-xs text-slate-500 mb-3">8 soal • ±25 menit</p>
                    <div class="h-1.5 bg-amber-100 rounded-full overflow-hidden">
                        <div class="h-full bg-amber-500 rounded-full" style="width: 60%"></div>
                    </div>
                </div>
                <div class="bg-white border border-red-200 rounded-2xl p-5 shadow-sm">
                    <span class="inline-block bg-red-100 text-red-700 text-xs font-black px-3 py-1 rounded-full mb-3">Sulit (HOTS)</span>
                    <h4 class="font-bold text-slate-800 mb-1">Soal Analisis</h4>
                    <p class="text-xs text-slate-500 mb-3">5 soal • ±40 menit</p>
                    <div class="h-1.5 bg-red-100 rounded-full overflow-hidden">
                        <div class="h-full bg-red-500 rounded-full" style="width: 30%"></div>
                    </div>
                </div>
            </div>
        `
    }
],

                        // ==========================================
                        // SETUP EDITOR & EVENT LISTENER
                        // ==========================================
                        setup: (editor) => {
                            // Masukkan data awal (jika ada) saat editor siap
                            editor.on('init', () => {
                                editor.setContent(this.exam.content);
                            });

                            // Deteksi setiap ketikan, perubahan format, atau insersi template
                            editor.on('KeyUp Change SetContent', () => {
                                this.exam.content = editor.getContent();
                                this.updateLatex(); // Panggil fungsi render ulang rumus
                            });
                        }
                    });

                    // Render LaTeX pertama kali saat halaman dibuka
                    this.$nextTick(() => { this.triggerLatexRender(); });
                },

                updateLatex() {
                    // $nextTick memastikan DOM HTML dari x-html sudah ter-update
                    // sebelum kita suruh KaTeX untuk memproses rumus $$
                    this.$nextTick(() => { this.triggerLatexRender(); });
                },

                triggerLatexRender() {
                    if (typeof renderMathInElement === 'function') {
                        const previewDiv = document.getElementById('preview-content');
                        if (previewDiv) {
                            renderMathInElement(previewDiv, {
                                delimiters: [
                                    { left: '$$', right: '$$', display: true },
                                    { left: '$', right: '$', display: false },
                                    { left: '\\(', right: '\\)', display: false },
                                    { left: '\\[', right: '\\]', display: true }
                                ],
                                throwOnError: false
                            });
                        }
                    }
                }
            }));
        });
    </script>
</body>

</html>