<x-app-layout>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/katex.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8fafc;
        }

        .prose-custom p {
            font-weight: 300 !important;
            color: #334155 !important;
            line-height: 1.7;
        }

        .prose-custom strong,
        .prose-custom b {
            font-weight: 800 !important;
            color: #0f172a !important;
        }

        .prose-custom .katex {
            font-size: 1.1em;
        }

        /* Checkbox Custom Animation */
        .checkbox-custom:checked+div {
            background-color: #e0e7ff;
            border-color: #6366f1;
        }

        .checkbox-custom:checked+div .check-icon {
            opacity: 1;
            transform: scale(1);
        }
    </style>

    <x-slot name="header">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 py-2">
            <div class="flex items-center gap-4">
                {{-- Tombol Kembali --}}
                <a href="{{ route('admin.exams.soal.index', $exam) }}"
                    class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-slate-400 hover:text-indigo-600 shadow-sm border border-slate-200 transition-all hover:-translate-x-0.5 shrink-0">
                    <i class="fas fa-arrow-left text-sm"></i>
                </a>

                <div class="flex items-center gap-3">
                    <div
                        class="w-11 h-11 rounded-xl bg-cyan-500 flex items-center justify-center shadow-md shadow-cyan-200 shrink-0">
                        <i class="fas fa-database text-white text-base"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">
                            Pilih Dari Bank Soal
                        </p>
                        <h2
                            class="font-black text-lg text-slate-800 tracking-tight leading-tight truncate max-w-[260px] lg:max-w-[400px]">
                            {{ $exam->title }}
                        </h2>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-10 min-h-screen relative pb-32">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Form Pencarian & Filter --}}
            <form method="GET" action="{{ route('admin.exams.soal.bank', $exam) }}"
                class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200 mb-6 space-y-4">

                <div class="flex flex-col xl:flex-row gap-4">
                    {{-- Input Pencarian Teks --}}
                    <div class="relative flex-1">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari narasi soal (Tekan Enter)..."
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border-slate-200 rounded-xl text-sm focus:ring-cyan-500 focus:border-cyan-500 transition-all">
                    </div>

                    {{-- Dropdown Filter --}}
                    <div class="flex flex-col sm:flex-row gap-3 w-full xl:w-auto">
                        <select name="subject_id" onchange="this.form.submit()"
                            class="w-full sm:w-48 bg-slate-50 border-slate-200 rounded-xl text-sm py-2.5 focus:ring-cyan-500 cursor-pointer text-slate-600">
                            <option value="">Semua Mata Pelajaran</option>
                            @foreach($subjects as $sub)
                            <option value="{{ $sub->id }}" {{ request('subject_id')==$sub->id ? 'selected' : '' }}>{{
                                $sub->name }}</option>
                            @endforeach
                        </select>

                        <select name="level_id" onchange="this.form.submit()"
                            class="w-full sm:w-40 bg-slate-50 border-slate-200 rounded-xl text-sm py-2.5 focus:ring-cyan-500 cursor-pointer text-slate-600">
                            <option value="">Semua Level</option>
                            @foreach($levels as $lvl)
                            <option value="{{ $lvl->id }}" {{ request('level_id')==$lvl->id ? 'selected' : '' }}>{{
                                $lvl->name }}</option>
                            @endforeach
                        </select>

                        {{-- Fitur Baru: Baris per Halaman --}}
                        <select name="per_page" onchange="this.form.submit()"
                            class="w-full sm:w-32 bg-slate-50 border-slate-200 rounded-xl text-sm py-2.5 focus:ring-cyan-500 cursor-pointer text-slate-600 font-bold">
                            <option value="10" {{ request('per_page')==10 ? 'selected' : '' }}>10 Baris</option>
                            <option value="20" {{ request('per_page')==20 ? 'selected' : '' }}>20 Baris</option>
                            <option value="50" {{ request('per_page')==50 ? 'selected' : '' }}>50 Baris</option>
                            <option value="100" {{ request('per_page')==100 ? 'selected' : '' }}>100 Baris</option>
                        </select>
                    </div>
                </div>

                {{-- Status Bar & Tombol Reset --}}
                <div
                    class="flex justify-between items-center text-sm text-slate-500 font-semibold border-t border-slate-100 pt-3">
                    <span>
                        Menampilkan {{ $bankQuestions->firstItem() ?? 0 }}-{{ $bankQuestions->lastItem() ?? 0 }} dari {{
                        $bankQuestions->total() }} Soal Tersedia
                    </span>

                    @if(request()->except('page'))
                    <a href="{{ route('admin.exams.soal.bank', $exam) }}"
                        class="text-rose-500 hover:text-rose-600 flex items-center gap-1 transition-colors">
                        <i class="fas fa-times-circle"></i> Reset Filter
                    </a>
                    @endif
                </div>
            </form>

            {{-- Form Submit Pilihan Soal --}}
            <form action="{{ route('admin.exams.soal.bank.store', $exam) }}" method="POST" id="formBankSoal">
                @csrf

                <div class="grid gap-4">
                    @forelse($bankQuestions as $index => $q)
                    @php
                    $types = [
                    'single_choice' => ['label' => 'Pilgan', 'icon' => 'fa-dot-circle', 'badge' => 'bg-violet-50
                    text-violet-600 border-violet-100'],
                    'complex_choice' => ['label' => 'PG Kompleks', 'icon' => 'fa-check-square', 'badge' =>
                    'bg-fuchsia-50 text-fuchsia-600 border-fuchsia-100'],
                    'true_false' => ['label' => 'Benar/Salah', 'icon' => 'fa-list-ol', 'badge' => 'bg-emerald-50
                    text-emerald-600 border-emerald-100'],
                    'matching' => ['label' => 'Menjodohkan', 'icon' => 'fa-exchange-alt', 'badge' => 'bg-amber-50
                    text-amber-600 border-amber-100'],
                    'essay' => ['label' => 'Isian Singkat', 'icon' => 'fa-keyboard', 'badge' => 'bg-blue-50
                    text-blue-600 border-blue-100'],
                    ];
                    $t = $types[$q->type] ?? ['label' => $q->type, 'icon' => 'fa-question', 'badge' => 'bg-slate-50
                    text-slate-400'];
                    @endphp

                    {{-- Label sebagai Wrapper agar seluruh Card bisa diklik --}}
                    <label class="block cursor-pointer group">
                        <input type="checkbox" name="question_ids[]" value="{{ $q->id }}" class="checkbox-custom hidden"
                            onchange="updateCount()">

                        <div
                            class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200 group-hover:border-indigo-300 transition-all flex gap-5 items-start">

                            {{-- Custom Checkbox UI --}}
                            <div
                                class="w-6 h-6 rounded-md border-2 border-slate-300 flex items-center justify-center shrink-0 mt-1 transition-all group-hover:border-indigo-400 bg-white">
                                <i
                                    class="fas fa-check text-indigo-600 text-xs opacity-0 scale-50 transition-all duration-200 check-icon"></i>
                            </div>

                            <div class="flex-1">
                                <div class="flex flex-wrap items-center gap-2 mb-3">
                                    <span
                                        class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wide border {{ $t['badge'] }}">
                                        <i class="fas {{ $t['icon'] }} mr-1"></i> {{ $t['label'] }}
                                    </span>

                                    @if($q->subject)
                                    <span
                                        class="px-3 py-1 rounded-full bg-slate-50 border border-slate-100 text-slate-500 text-[10px] font-bold uppercase">
                                        {{ $q->subject->name }}
                                    </span>
                                    @endif
                                </div>

                                <div class="prose-custom max-w-none text-slate-700 text-sm">
                                    {!! $q->content !!}
                                </div>
                            </div>
                        </div>
                    </label>
                    @empty
                    <div class="bg-white rounded-[2.5rem] p-16 text-center border border-dashed border-slate-300">
                        <div
                            class="w-24 h-24 bg-cyan-50 rounded-full flex items-center justify-center text-4xl text-cyan-300 mx-auto mb-6">
                            <i class="fas fa-database"></i>
                        </div>
                        <h3 class="text-2xl font-black text-slate-800 mb-2">Bank Soal Kosong</h3>
                        <p class="text-slate-500 font-medium">Semua soal yang ada di database sudah dimasukkan ke ujian
                            ini, atau belum ada soal yang dibuat.</p>
                    </div>
                    @endforelse
                </div>

                {{-- Paginasi --}}
                <div class="mt-6">
                    {{ $bankQuestions->links() }}
                </div>

                {{-- Action Bar Mengambang (Muncul jika ada soal yang dipilih) --}}
                <div id="actionBar"
                    class="fixed bottom-0 left-0 right-0 bg-white/90 backdrop-blur-md border-t border-slate-200 p-4 shadow-[0_-10px_20px_-10px_rgba(0,0,0,0.1)] transform translate-y-full transition-transform duration-300 ease-in-out z-50">
                    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-check-double text-indigo-600"></i>
                            </div>
                            <div>
                                <h4 class="font-black text-slate-800" id="countText">0 Soal Dipilih</h4>
                                <p class="text-xs text-slate-500 font-medium">Siap ditambahkan ke ujian</p>
                            </div>
                        </div>

                        <button type="submit" id="btnSubmit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-6 py-3 rounded-xl shadow-md shadow-indigo-200 transition-all flex items-center gap-2">
                            <i class="fas fa-plus"></i> Tambahkan ke Ujian
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/katex.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/contrib/auto-render.min.js"></script>

    <script>
        // Render Matematika (Sama seperti index.blade.php)
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof renderMathInElement !== 'undefined') {
                renderMathInElement(document.body, {
                    delimiters: [
                        {left: '$$', right: '$$', display: true},
                        {left: '$', right: '$', display: false},
                        {left: '\\(', right: '\\)', display: false},
                        {left: '\\[', right: '\\]', display: true}
                    ],
                    throwOnError: false
                });
            }
        });

        // Logika untuk menghitung checkbox dan memunculkan Action Bar
        const checkboxes = document.querySelectorAll('.checkbox-custom');
        const actionBar = document.getElementById('actionBar');
        const countText = document.getElementById('countText');
        const btnSubmit = document.getElementById('btnSubmit');

        function updateCount() {
            const checkedCount = document.querySelectorAll('.checkbox-custom:checked').length;

            countText.innerText = checkedCount + ' Soal Dipilih';

            if (checkedCount > 0) {
                // Munculkan panel bawah
                actionBar.classList.remove('translate-y-full');
            } else {
                // Sembunyikan panel bawah
                actionBar.classList.add('translate-y-full');
            }
        }
    </script>
</x-app-layout>