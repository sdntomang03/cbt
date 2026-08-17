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
                <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-slate-400" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="m15.5 7.5 2.3 2.3a1 1 0 0 0 1.4 0l2.1-2.1a1 1 0 0 0 0-1.4L19 4l-4 4Z" />
                        <path d="m21 2-9.6 9.6" />
                        <path d="m5.4 16.6-2.1 2.1a1 1 0 0 0 0 1.4l2.2 2.2a1 1 0 0 0 1.4 0l2.1-2.1" />
                        <path d="M12 14v2" />
                        <path d="M14 12h2" />
                    </svg>
                    <input type="password" id="apiKey" value="{{ env('GEMINI_API_KEY', '') }}"
                        placeholder="Masukkan Gemini API Key..."
                        class="outline-none bg-transparent text-sm w-full focus:ring-0 border-none p-0">
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Mata Pelajaran</label>
                            <input type="text" id="mapel" placeholder="Contoh: Sejarah Indonesia"
                                class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Topik / Materi
                                Spesifik</label>
                            <input type="text" id="topik" placeholder="Contoh: Masa Orde Baru"
                                class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Tipe Soal</label>
                            <select id="tipeSoal"
                                class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none">
                                <option value="single_choice">Pilihan Ganda (Satu Jawaban Benar)</option>
                                <option value="complex_choice">Pilihan Ganda Kompleks (Banyak Jawaban Benar)</option>
                                <option value="true_false">Benar / Salah</option>
                                <option value="essay">Isian Singkat / Essay</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Jumlah Soal</label>
                            <input type="number" id="jumlahSoal" min="1" max="20" value="5"
                                class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Tingkat
                                Kesulitan</label>
                            <select id="kesulitan"
                                class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none">
                                <option value="Mudah">Mudah (HOTS Level 1)</option>
                                <option value="Sedang">Sedang (HOTS Level 2)</option>
                                <option value="Sulit">Sulit (HOTS Level 3)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-6">
                        <button onclick="generateSoal()" id="btnGenerate"
                            class="w-full py-3 px-6 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-lg shadow-indigo-200 transition-all flex items-center justify-center gap-2">
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
            const tipeSoal = document.getElementById('tipeSoal').value;
            const jumlahSoal = document.getElementById('jumlahSoal').value;
            const kesulitan = document.getElementById('kesulitan').value;

            if (!apiKey) return alert("API Key Gemini tidak boleh kosong!");
            if (!mapel || !topik) return alert("Mata Pelajaran dan Topik wajib diisi!");

            // Tentukan struktur JSON opsi yang diharapkan berdasarkan tipe soal
            let strukturOpsiInstruksi = "";
            if (tipeSoal === "single_choice") {
                strukturOpsiInstruksi = `Berikan 4 pilihan ganda (A, B, C, D). Set "is_correct": true HANYA untuk satu pilihan yang benar, sisanya false.`;
            } else if (tipeSoal === "complex_choice") {
                strukturOpsiInstruksi = `Berikan 4-5 pilihan. Set "is_correct": true untuk 2 atau lebih pilihan yang benar, sisanya false.`;
            } else if (tipeSoal === "true_false") {
                strukturOpsiInstruksi = `Berikan beberapa pernyataan sebagai "option_text". Set "is_correct": true jika pernyataan Benar, dan false jika pernyataan Salah.`;
            } else if (tipeSoal === "essay") {
                strukturOpsiInstruksi = `Berikan 1 jawaban pasti pada "option_text" dan set "is_correct": true.`;
            }

            const prompt = `Anda adalah pembuat soal ujian profesional. Buatkan tepat ${jumlahSoal} soal ujian untuk mata pelajaran "${mapel}" dengan topik "${topik}". Tingkat kesulitan: ${kesulitan}.

Instruksi Output (SANGAT PENTING):
1. Anda HARUS mengembalikan data MURNI dalam format ARRAY JSON.
2. JANGAN tambahkan teks pengantar, penjelasan, atau blok kode Markdown (seperti \`\`\`json). Mulailah langsung dengan tanda "[" dan akhiri dengan "]".
3. Setiap elemen array mewakili 1 soal dengan struktur key JSON berikut:
[
    {
        "type": "${tipeSoal}",
        "content": "<p>Tuliskan pertanyaan disini (Gunakan tag HTML dasar seperti <p>, <strong>, dll jika perlu)</p>",
        "options": [
            { "option_text": "Teks Pilihan 1", "is_correct": true_atau_false },
            { "option_text": "Teks Pilihan 2", "is_correct": true_atau_false }
        ]
    }
]
${strukturOpsiInstruksi}
`;

            toggleLoading(true);

            try {
                const response = await fetch(`https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent`, {
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

            } catch (error) {
                alert("Gagal memproses soal: " + error.message);
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