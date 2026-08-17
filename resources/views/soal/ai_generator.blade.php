<x-app-layout>
    <div class="max-w-5xl mx-auto px-4 py-8">
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-slate-800 uppercase tracking-tight">AI Soal Generator</h2>
                <p class="text-sm text-slate-500 mt-1">Buat soal otomatis dengan AI untuk ujian: <span
                        class="font-bold text-indigo-600">{{ $exam->title }}</span></p>
            </div>
            <a href="{{ route('admin.exams.soal.index', $exam->id) }}"
                class="px-4 py-2 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 rounded-lg text-sm font-bold shadow-sm">
                &larr; Kembali
            </a>
        </div>

        @if($errors->any())
        <div
            class="mb-6 p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-r-lg text-sm font-medium shadow-sm">
            <ul class="list-disc ml-4 space-y-1">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- PANEL PENGATURAN -->
            <aside class="lg:col-span-12 space-y-6">
                <!-- API Key -->
                <input type="hidden" id="apiKey" value="{{ config('services.gemini.key') }}">

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <!-- Mapel -->
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Mata Pelajaran</label>
                            <input type="text" id="mapel" placeholder="Contoh: Sejarah Indonesia"
                                class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>

                        <!-- Topik -->
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Topik / Materi
                                Spesifik</label>
                            <input type="text" id="topik" placeholder="Contoh: Masa Orde Baru"
                                class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>

                        <!-- Tipe Soal (Checkbox) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Tipe Soal (Bisa pilih >
                                1)</label>
                            <div class="space-y-2 p-3 border border-slate-200 rounded-xl bg-slate-50">
                                <label class="flex items-start gap-2 cursor-pointer">
                                    <input type="checkbox" name="tipeSoal" value="single_choice"
                                        class="mt-1 w-4 h-4 text-indigo-600 rounded" checked>
                                    <span class="text-sm text-slate-700 font-medium">Pilihan Ganda (1 Benar)</span>
                                </label>
                                <label class="flex items-start gap-2 cursor-pointer">
                                    <input type="checkbox" name="tipeSoal" value="complex_choice"
                                        class="mt-1 w-4 h-4 text-indigo-600 rounded">
                                    <span class="text-sm text-slate-700 font-medium">PG Kompleks (>1 Benar)</span>
                                </label>
                                <label class="flex items-start gap-2 cursor-pointer">
                                    <input type="checkbox" name="tipeSoal" value="true_false"
                                        class="mt-1 w-4 h-4 text-indigo-600 rounded">
                                    <span class="text-sm text-slate-700 font-medium">Benar / Salah</span>
                                </label>
                                <label class="flex items-start gap-2 cursor-pointer">
                                    <input type="checkbox" name="tipeSoal" value="essay"
                                        class="mt-1 w-4 h-4 text-indigo-600 rounded">
                                    <span class="text-sm text-slate-700 font-medium">Isian Singkat / Essay</span>
                                </label>
                            </div>
                        </div>

                        <!-- Tingkat Kesulitan (Checkbox) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Tingkat Kesulitan (Bisa
                                pilih > 1)</label>
                            <div class="space-y-2 p-3 border border-slate-200 rounded-xl bg-slate-50">
                                <label class="flex items-start gap-2 cursor-pointer">
                                    <input type="checkbox" name="kesulitan" value="Mudah (HOTS Level 1)"
                                        class="mt-1 w-4 h-4 text-indigo-600 rounded" checked>
                                    <span class="text-sm text-slate-700 font-medium">Mudah (HOTS Level 1)</span>
                                </label>
                                <label class="flex items-start gap-2 cursor-pointer">
                                    <input type="checkbox" name="kesulitan" value="Sedang (HOTS Level 2)"
                                        class="mt-1 w-4 h-4 text-indigo-600 rounded">
                                    <span class="text-sm text-slate-700 font-medium">Sedang (HOTS Level 2)</span>
                                </label>
                                <label class="flex items-start gap-2 cursor-pointer">
                                    <input type="checkbox" name="kesulitan" value="Sulit (HOTS Level 3)"
                                        class="mt-1 w-4 h-4 text-indigo-600 rounded">
                                    <span class="text-sm text-slate-700 font-medium">Sulit (HOTS Level 3)</span>
                                </label>
                            </div>
                        </div>

                        <!-- Jumlah Soal -->
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Total Jumlah
                                Soal</label>
                            <input type="number" id="jumlahSoal" min="1" max="30" value="5"
                                class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none">
                            <p class="text-[10px] text-slate-400 mt-1">*AI akan membagi rata tipe soal jika Anda memilih
                                lebih dari 1.</p>
                        </div>

                        <!-- Prompt Tambahan -->
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Prompt Tambahan
                                (Opsional)</label>
                            <textarea id="promptTambahan" rows="3"
                                placeholder="Contoh: Buatkan soal dengan studi kasus kehidupan sehari-hari anak SMA, jangan gunakan kalimat bersayap..."
                                class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none"></textarea>
                        </div>

                    </div>

                    <div class="mt-6">
                        <button onclick="generateSoal()" id="btnGenerate"
                            class="w-full py-3 px-6 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-lg shadow-indigo-200 transition-all flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                            </svg>
                            <span id="btnText">Mulai Generate Soal</span>
                        </button>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    <!-- Form Hidden untuk melempar JSON ke backend -->
    <form id="formPreview" action="{{ route('admin.exams.soal.ai_preview', $exam) }}" method="POST" class="hidden">
        @csrf
        <textarea name="json_data" id="jsonDataInput"></textarea>
    </form>

    <!-- Overlay Loading -->
    <div id="loadingOverlay"
        class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm hidden z-50 flex flex-col items-center justify-center">
        <svg class="w-16 h-16 text-white animate-spin mb-4" xmlns="http://www.w3.org/2000/svg" fill="none"
            viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
            </path>
        </svg>
        <p class="text-white font-bold text-lg animate-pulse">AI sedang menyusun soal... Mohon tunggu.</p>
    </div>

    <script>
        async function generateSoal() {
            const apiKey = document.getElementById('apiKey').value.trim();
            const mapel = document.getElementById('mapel').value;
            const topik = document.getElementById('topik').value;
            const jumlahSoal = document.getElementById('jumlahSoal').value;
            const promptTambahan = document.getElementById('promptTambahan').value.trim();

            // Ambil semua tipe soal yang dicentang
            const selectedTipeSoal = Array.from(document.querySelectorAll('input[name="tipeSoal"]:checked')).map(cb => cb.value);

            // Ambil semua kesulitan yang dicentang
            const selectedKesulitan = Array.from(document.querySelectorAll('input[name="kesulitan"]:checked')).map(cb => cb.value);

            if (!apiKey) return alert("API Key Gemini tidak boleh kosong! (Periksa konfigurasi .env)");
            if (!mapel || !topik) return alert("Mata Pelajaran dan Topik wajib diisi!");
            if (selectedTipeSoal.length === 0) return alert("Pilih minimal satu Tipe Soal!");
            if (selectedKesulitan.length === 0) return alert("Pilih minimal satu Tingkat Kesulitan!");

            // Bangun instruksi dinamis untuk setiap tipe soal yang dipilih
            let strukturOpsiInstruksi = "";
            if (selectedTipeSoal.includes("single_choice")) {
                strukturOpsiInstruksi += `- Untuk "type": "single_choice", berikan 4 pilihan ganda (A, B, C, D). Set "is_correct": true HANYA untuk 1 pilihan yang benar.\n`;
            }
            if (selectedTipeSoal.includes("complex_choice")) {
                strukturOpsiInstruksi += `- Untuk "type": "complex_choice", berikan 4-5 pilihan. Set "is_correct": true untuk 2 atau lebih pilihan yang benar.\n`;
            }
            if (selectedTipeSoal.includes("true_false")) {
                strukturOpsiInstruksi += `- Untuk "type": "true_false", berikan pernyataan sebagai "option_text". Set "is_correct": true jika pernyataan Benar, dan false jika pernyataan Salah.\n`;
            }
            if (selectedTipeSoal.includes("essay")) {
                strukturOpsiInstruksi += `- Untuk "type": "essay", berikan 1 jawaban pasti pada "option_text" dan set "is_correct": true.\n`;
            }

            const prompt = `Anda adalah pembuat soal ujian profesional. Buatkan tepat ${jumlahSoal} soal ujian untuk mata pelajaran "${mapel}" dengan topik "${topik}".

Variasi Tingkat Kesulitan yang diminta: ${selectedKesulitan.join(', ')}.
Variasi Tipe Soal yang diminta: ${selectedTipeSoal.join(', ')}.
(PENTING: Jika ada lebih dari satu tipe soal yang diminta, distribusikan ke ${jumlahSoal} soal tersebut secara proporsional).

${promptTambahan ? `Instruksi Tambahan dari Guru:\n"${promptTambahan}"\n` : ""}

Instruksi Output (SANGAT PENTING):
1. Anda HARUS mengembalikan data MURNI dalam format ARRAY JSON.
2. JANGAN tambahkan teks pengantar, penjelasan, atau blok kode Markdown (seperti \`\`\`json). Mulailah langsung dengan tanda "[" dan akhiri dengan "]".
3. Setiap elemen array mewakili 1 soal dengan struktur key JSON berikut:
[
    {
        "type": "isi_dengan_tipe_soal_yang_sesuai",
        "content": "<p>Tuliskan pertanyaan disini (Gunakan tag HTML dasar seperti <p>, <strong>, dll jika perlu)</p>",
        "options": [
            { "option_text": "Teks Pilihan 1", "is_correct": true_atau_false },
            { "option_text": "Teks Pilihan 2", "is_correct": true_atau_false }
        ]
    }
]

Panduan pengisian "options" berdasarkan "type":
${strukturOpsiInstruksi}
`;

            toggleLoading(true);

            try {
                const response = await fetch(`https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-goog-api-key': apiKey },
                    body: JSON.stringify({
                        contents: [{ parts: [{ text: prompt }] }],
                        generationConfig: { temperature: 0.7 }
                    })
                });

                const data = await response.json();
                if (!response.ok) throw new Error(data.error?.message || "Terjadi kesalahan pada API Gemini.");

                let jsonResult = data.candidates[0].content.parts[0].text;

                // Pastikan teks murni JSON tanpa backticks
                jsonResult = jsonResult.replace(/```json/g, '').replace(/```/g, '').trim();

                // Set value ke textarea hidden form
                document.getElementById('jsonDataInput').value = jsonResult;

                // Submit form otomatis
                document.getElementById('formPreview').submit();

            } } catch (error) {
                console.error(error);
                if (error.message.includes("high demand") || error.message.includes("503")) {
                    alert("Server AI sedang penuh karena tingginya permintaan. Mohon tunggu 1-2 menit dan coba lagi.");
                } else {
                    alert("Gagal memproses soal: " + error.message);
                }
                toggleLoading(false);
            }
        }

        function toggleLoading(isLoading) {
            const overlay = document.getElementById('loadingOverlay');
            if (isLoading) {
                overlay.classList.remove('hidden');
            } else {
                overlay.classList.add('hidden');
            }
        }
    </script>
</x-app-layout>