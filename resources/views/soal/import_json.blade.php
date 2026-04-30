<x-app-layout>
    <div class="max-w-5xl mx-auto px-4 py-8">

        <div class="mb-6 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-slate-800 uppercase tracking-tight">Import Soal via JSON</h2>
                <p class="text-sm text-slate-500 mt-1">Ujian: <span class="font-bold text-indigo-600">{{ $exam->title ??
                        'Nama Ujian' }}</span></p>
            </div>
            <a href="{{ route('admin.exams.soal.index', $exam->id) }}"
                class="px-4 py-2 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 rounded-lg text-sm font-bold shadow-sm transition-colors">
                &larr; Kembali ke Ujian
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

        <div
            class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 mb-8 hover:border-indigo-300 transition-colors">
            <form action="{{ route('admin.soal.import_json_preview', $exam->id) }}" method="POST"
                enctype="multipart/form-data" class="flex flex-col md:flex-row items-end gap-5">
                @csrf
                <div class="flex-1 w-full">
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-3">Unggah File
                        Bank Soal (.json)</label>
                    <input type="file" name="file_json" accept=".json" required
                        class="block w-full text-sm text-slate-500 file:mr-4 file:py-3.5 file:px-6 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-all border-2 border-slate-100 rounded-2xl cursor-pointer focus:outline-none focus:border-indigo-300">
                </div>
                <button type="submit"
                    class="w-full md:w-auto px-8 py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-black text-sm uppercase tracking-wider rounded-xl shadow-lg shadow-indigo-200 transition-all active:scale-95 flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    Proses Import
                </button>
            </form>
        </div>

        <div class="bg-slate-900 rounded-2xl shadow-xl overflow-hidden border border-slate-800">
            <div class="px-6 py-4 border-b border-slate-800 bg-slate-800/50 flex items-center">
                <svg class="w-5 h-5 text-emerald-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                </svg>
                <h3 class="text-sm font-bold text-white uppercase tracking-widest">Format JSON yang Diterima</h3>
            </div>

            <div class="p-6">
                <p class="text-sm text-slate-400 mb-4">Pastikan struktur JSON sesuai dengan parameter yang diproses oleh
                    sistem Anda. Properti <code
                        class="bg-slate-800 px-1.5 py-0.5 rounded text-indigo-300">options</code> harus disesuaikan
                    dengan format yang bisa dibaca oleh method <code
                        class="bg-slate-800 px-1.5 py-0.5 rounded text-emerald-300">saveQuestionDetails()</code>.</p>

                <div class="overflow-x-auto">
                    <pre
                        class="text-[11px] sm:text-xs text-slate-300 font-mono leading-relaxed bg-black/30 p-4 rounded-xl border border-slate-700/50 custom-scrollbar overflow-x-auto"><code>[
    <span class="text-slate-500">// 1. Pilihan Ganda (Satu Jawaban Benar)</span>
    {
        <span class="text-emerald-400">"type"</span>: <span class="text-amber-300">"single_choice"</span>,
        <span class="text-emerald-400">"content"</span>: <span class="text-amber-300">"&lt;p&gt;Ibu kota negara Indonesia adalah...&lt;/p&gt;"</span>,
        <span class="text-emerald-400">"options"</span>: [
            { <span class="text-emerald-400">"option_text"</span>: <span class="text-amber-300">"Jakarta"</span>, <span class="text-emerald-400">"is_correct"</span>: <span class="text-indigo-400">true</span> },
            { <span class="text-emerald-400">"option_text"</span>: <span class="text-amber-300">"Bandung"</span>, <span class="text-emerald-400">"is_correct"</span>: <span class="text-indigo-400">false</span> }
        ]
    },

    <span class="text-slate-500">// 2. Pilihan Ganda Kompleks (Lebih dari Satu Jawaban Benar)</span>
    {
        <span class="text-emerald-400">"type"</span>: <span class="text-amber-300">"complex_choice"</span>,
        <span class="text-emerald-400">"content"</span>: <span class="text-amber-300">"&lt;p&gt;Hewan manakah yang termasuk mamalia?&lt;/p&gt;"</span>,
        <span class="text-emerald-400">"options"</span>: [
            { <span class="text-emerald-400">"option_text"</span>: <span class="text-amber-300">"Kucing"</span>, <span class="text-emerald-400">"is_correct"</span>: <span class="text-indigo-400">true</span> },
            { <span class="text-emerald-400">"option_text"</span>: <span class="text-amber-300">"Paus"</span>, <span class="text-emerald-400">"is_correct"</span>: <span class="text-indigo-400">true</span> }
        ]
    },

    <span class="text-slate-500">// 3. Benar Salah (Pernyataan Majemuk)</span>
    {
        <span class="text-emerald-400">"type"</span>: <span class="text-amber-300">"true_false"</span>,
        <span class="text-emerald-400">"content"</span>: <span class="text-amber-300">"&lt;p&gt;Tentukan Benar/Salah pernyataan berikut!&lt;/p&gt;"</span>,
        <span class="text-emerald-400">"options"</span>: [
            { <span class="text-emerald-400">"option_text"</span>: <span class="text-amber-300">"Matahari mengelilingi bumi."</span>, <span class="text-emerald-400">"is_correct"</span>: <span class="text-indigo-400">false</span> },
            { <span class="text-emerald-400">"option_text"</span>: <span class="text-amber-300">"Bumi adalah planet ke-3."</span>, <span class="text-emerald-400">"is_correct"</span>: <span class="text-indigo-400">true</span> }
        ]
    },

    <span class="text-slate-500">// 4. Isian Singkat</span>
    {
        <span class="text-emerald-400">"type"</span>: <span class="text-amber-300">"essay"</span>,
        <span class="text-emerald-400">"content"</span>: <span class="text-amber-300">"&lt;p&gt;Sebutkan dasar negara Indonesia!&lt;/p&gt;"</span>,
        <span class="text-emerald-400">"options"</span>: [
            { <span class="text-emerald-400">"option_text"</span>: <span class="text-amber-300">"Pancasila"</span>, <span class="text-emerald-400">"is_correct"</span>: <span class="text-indigo-400">true</span> }
        ]
    },

    <span class="text-slate-500">// 5. Menjodohkan (Matching)</span>
    {
        <span class="text-emerald-400">"type"</span>: <span class="text-amber-300">"matching"</span>,
        <span class="text-emerald-400">"content"</span>: <span class="text-amber-300">"&lt;p&gt;Jodohkan negara dengan ibukotanya!&lt;/p&gt;"</span>,
        <span class="text-emerald-400">"options"</span>: [
            {
                <span class="text-emerald-400">"premise_text"</span>: <span class="text-amber-300">"Jepang"</span>,
                <span class="text-emerald-400">"target_text"</span>: <span class="text-amber-300">"Tokyo"</span>
            },
            {
                <span class="text-emerald-400">"premise_text"</span>: <span class="text-amber-300">"Malaysia"</span>,
                <span class="text-emerald-400">"target_text"</span>: <span class="text-amber-300">"Kuala Lumpur"</span>
            }
        ]
    }
]</code></pre>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>