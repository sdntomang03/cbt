<x-app-layout>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/katex.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <style>
        [x-cloak] {
            display: none !important;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8fafc;
        }

        .bounce-active:active {
            transform: scale(0.98);
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
    </style>

    <x-slot name="header">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 py-2">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.exams.index') }}"
                    class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-slate-400 hover:text-indigo-600 shadow-sm border border-slate-200 transition-all hover:-translate-x-0.5 shrink-0">
                    <i class="fas fa-arrow-left text-sm"></i>
                </a>

                <div class="flex items-center gap-3">
                    <div
                        class="w-11 h-11 rounded-xl bg-indigo-600 flex items-center justify-center shadow-md shadow-indigo-200 shrink-0">
                        <i class="fas fa-layer-group text-white text-base"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">Bank
                            Soal</p>
                        <h2
                            class="font-black text-lg text-slate-800 tracking-tight leading-tight truncate max-w-[260px] lg:max-w-[400px]">
                            {{ $exam->title }}
                        </h2>
                    </div>
                </div>

                <div
                    class="hidden sm:flex flex-col items-center justify-center bg-indigo-50 border border-indigo-100 rounded-xl px-4 py-2 ml-1">
                    <span class="text-2xl font-black text-indigo-600 leading-none">{{ $questions->total() }}</span>
                    <span
                        class="text-[10px] font-bold text-indigo-400 uppercase tracking-wider leading-none mt-0.5">Soal</span>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <form action="{{ route('admin.exams.soal.import', $exam->id) }}" method="POST"
                    enctype="multipart/form-data" id="formImportExcel" class="hidden">
                    @csrf
                    <input type="file" name="file_excel" id="fileExcel" accept=".xlsx,.xls,.csv"
                        onchange="document.getElementById('formImportExcel').submit()">
                </form>

                <a href="{{ route('admin.soal.template') }}" target="_blank" title="Download Template Excel"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-600 hover:border-slate-300 hover:bg-slate-50 font-semibold text-sm transition-all shadow-sm">
                    <i class="fas fa-file-download text-slate-400 text-xs"></i>
                    <span class="hidden md:inline">Template</span>
                </a>

                <button type="button" onclick="document.getElementById('fileExcel').click()" title="Import dari Excel"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 hover:bg-emerald-100 font-semibold text-sm transition-all shadow-sm">
                    <i class="fas fa-file-excel text-emerald-500 text-xs"></i>
                    <span class="hidden md:inline">Excel</span>
                </button>

                <a href="{{ route('admin.soal.import_json_view', $exam) }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 hover:bg-amber-100 font-semibold text-sm transition-all shadow-sm">
                    <i class="fas fa-file-code text-amber-500 text-xs"></i>
                    <span class="hidden md:inline">JSON</span>
                </a>

                <div class="w-px h-8 bg-slate-200 hidden sm:block mx-1"></div>

                <a href="{{ route('admin.exams.soal.create', $exam) }}"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm transition-all shadow-md shadow-indigo-200">
                    <i class="fas fa-plus text-xs"></i>
                    <span>Buat Soal</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Toolbar Pencarian & Filter Server-Side --}}
            <form method="GET" action="{{ route('admin.exams.soal.index', $exam) }}"
                class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200 mb-6 flex flex-col md:flex-row justify-between items-center gap-4">

                <div class="relative w-full md:w-96">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari narasi soal (Tekan Enter)..."
                        class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border-slate-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                </div>

                <div
                    class="flex items-center gap-4 w-full md:w-auto justify-between md:justify-end text-sm text-slate-500 font-semibold">
                    <span>
                        Menampilkan {{ $questions->firstItem() ?? 0 }}-{{ $questions->lastItem() ?? 0 }} dari {{
                        $questions->total() }}
                    </span>
                    <select name="per_page" onchange="this.form.submit()"
                        class="bg-slate-50 border-slate-200 rounded-xl text-sm py-2 pl-3 pr-8 focus:ring-indigo-500 cursor-pointer">
                        <option value="10" {{ request('per_page')==10 ? 'selected' : '' }}>10 baris</option>
                        <option value="25" {{ request('per_page')==25 ? 'selected' : '' }}>25 baris</option>
                        <option value="50" {{ request('per_page')==50 ? 'selected' : '' }}>50 baris</option>
                    </select>
                </div>
            </form>

            {{-- Container Daftar Soal --}}
            <div class="grid gap-6">
                @forelse($questions as $index => $q)
                @php
                $types = [
                'single_choice' => ['label' => 'Pilgan', 'icon' => 'fa-dot-circle', 'color' => 'bg-violet-400', 'badge'
                => 'bg-violet-50 text-violet-600 border-violet-100'],
                'complex_choice' => ['label' => 'PG Kompleks', 'icon' => 'fa-check-square', 'color' => 'bg-fuchsia-400',
                'badge' => 'bg-fuchsia-50 text-fuchsia-600 border-fuchsia-100'],
                'true_false' => ['label' => 'Benar/Salah', 'icon' => 'fa-list-ol', 'color' => 'bg-emerald-400', 'badge'
                => 'bg-emerald-50 text-emerald-600 border-emerald-100'],
                'matching' => ['label' => 'Menjodohkan', 'icon' => 'fa-exchange-alt', 'color' => 'bg-amber-400', 'badge'
                => 'bg-amber-50 text-amber-600 border-amber-100'],
                'essay' => ['label' => 'Isian Singkat', 'icon' => 'fa-keyboard', 'color' => 'bg-blue-400', 'badge' =>
                'bg-blue-50 text-blue-600 border-blue-100'],
                ];
                $t = $types[$q->type] ?? ['label' => $q->type, 'icon' => 'fa-question', 'color' => 'bg-slate-300',
                'badge' => 'bg-slate-50 text-slate-400'];
                @endphp

                <div
                    class="bg-white rounded-[2.5rem] p-2 pr-8 shadow-sm border border-white hover:border-indigo-100 transition-all group relative">
                    <div class="flex flex-col md:flex-row gap-6">

                        {{-- Nomor Urut Absolut --}}
                        <div
                            class="w-full md:w-20 bg-slate-50 rounded-[2rem] flex flex-row md:flex-col items-center py-4 md:py-6 px-6 md:px-0 gap-4 md:gap-2 shrink-0 self-stretch justify-between md:justify-center">
                            <span class="text-xl md:text-3xl font-black text-slate-300">
                                #{{ $questions->firstItem() + $index }}
                            </span>
                            <div class="w-12 md:w-8 h-1.5 rounded-full {{ $t['color'] }}"></div>
                        </div>

                        <div class="flex-1 py-4 md:py-8 px-4 md:px-0">
                            <div class="flex justify-between items-start mb-6">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span
                                        class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wide border-2 {{ $t['badge'] }}">
                                        <i class="fas {{ $t['icon'] }} mr-1.5"></i> {{ $t['label'] }}
                                    </span>
                                    @if($q->subject)
                                    <span
                                        class="px-3 py-1.5 rounded-full bg-slate-50 border border-slate-100 text-slate-500 text-[10px] font-bold uppercase">
                                        <i class="fas fa-book mr-1"></i> {{ $q->subject->name }}
                                    </span>
                                    @endif
                                    @if($q->level)
                                    <span
                                        class="px-3 py-1.5 rounded-full bg-slate-50 border border-slate-100 text-slate-500 text-[10px] font-bold uppercase">
                                        <i class="fas fa-layer-group mr-1"></i> {{ $q->level->name }}
                                    </span>
                                    @endif
                                </div>

                                <div
                                    class="flex gap-2 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-all duration-300">
                                    <a href="{{ route('admin.exams.soal.edit', [$exam, $q]) }}"
                                        class="w-10 h-10 rounded-full bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white transition flex items-center justify-center bounce-active shadow-sm">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    <button onclick="confirmDelete({{ $q->id }})"
                                        class="w-10 h-10 rounded-full bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition flex items-center justify-center bounce-active shadow-sm">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="prose-custom max-w-none text-slate-700 mb-6">
                                {!! $q->content !!}
                            </div>

                            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                                <h5 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Kunci
                                    Jawaban:</h5>

                                @if(in_array($q->type, ['single_choice', 'complex_choice']))
                                <div class="space-y-2">
                                    @foreach($q->options as $opt)
                                    <div
                                        class="flex items-start gap-2 {{ $opt->is_correct ? 'text-emerald-600' : 'text-slate-400 opacity-50' }}">
                                        <i
                                            class="fas mt-1 text-sm {{ $opt->is_correct ? 'fa-check-circle' : 'fa-times' }}"></i>
                                        <div class="text-sm prose-custom prose-sm prose-p:my-0">{!! $opt->option_text
                                            !!}</div>
                                    </div>
                                    @endforeach
                                </div>

                                @elseif($q->type === 'true_false')
                                <div class="space-y-2 bg-white p-3 rounded-xl border border-slate-200">
                                    @foreach($q->options as $opt)
                                    <div
                                        class="flex items-start justify-between gap-4 py-2 border-b border-slate-100 last:border-0 last:pb-0">
                                        <div class="text-sm prose-custom prose-sm prose-p:my-0 flex-1">{!!
                                            $opt->option_text ?? '- Pernyataan Kosong -' !!}</div>
                                        <div class="shrink-0">
                                            <span
                                                class="px-3 py-1 rounded-md text-[10px] font-black tracking-widest uppercase shadow-sm {{ $opt->is_correct ? 'bg-emerald-500 text-white' : 'bg-rose-500 text-white' }}">
                                                {{ $opt->is_correct ? 'BENAR' : 'SALAH' }}
                                            </span>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                                @elseif($q->type === 'matching')
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                    @foreach($q->matches ?? [] as $m)
                                    <div
                                        class="bg-white p-2 rounded-lg border border-slate-200 flex items-center justify-between text-xs shadow-sm">
                                        <span class="prose-custom truncate w-[45%]">{!! strip_tags($m->premise_text ??
                                            '') !!}</span>
                                        <i class="fas fa-arrow-right text-indigo-300"></i>
                                        <span class="prose-custom truncate w-[45%] text-right">{!!
                                            strip_tags($m->target_text ?? '') !!}</span>
                                    </div>
                                    @endforeach
                                </div>

                                @elseif($q->type === 'essay')
                                <div class="flex flex-wrap gap-2">
                                    @foreach($q->options as $opt)
                                    <div
                                        class="text-sm font-bold text-indigo-700 bg-indigo-50 px-3 py-1.5 rounded-lg border border-indigo-100 flex items-center gap-2">
                                        <i class="fas fa-check text-indigo-400 text-[10px]"></i>
                                        <span class="prose-custom prose-p:my-0">{!! $opt->option_text ?? '-' !!}</span>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="bg-white rounded-[2.5rem] p-16 text-center border border-dashed border-slate-300">
                    <div
                        class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center text-4xl text-slate-300 mx-auto mb-6">
                        <i class="fas {{ request('search') ? 'fa-search' : 'fa-folder-open' }}"></i>
                    </div>
                    <h3 class="text-2xl font-black text-slate-800 mb-2">
                        {{ request('search') ? 'Soal Tidak Ditemukan' : 'Belum Ada Soal' }}
                    </h3>
                    <p class="text-slate-500 font-medium mb-8">
                        {{ request('search') ? 'Ubah kata kunci pencarian Anda.' : 'Ujian ini masih kosong.' }}
                    </p>
                </div>
                @endforelse

                {{-- Tampilan Paginasi Laravel Tailwind --}}
                <div class="mt-6">
                    {{ $questions->links() }}
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/katex.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/contrib/auto-render.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            // 1. Render rumus yang diketik manual menggunakan $$...$$ atau $...$
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

            // 2. Render rumus dari tombol Formula Quill Editor (jika ada tag .ql-formula)
            if (typeof katex !== 'undefined') {
                document.querySelectorAll('.ql-formula').forEach(el => {
                    let exp = el.getAttribute('data-value');
                    if (exp) {
                        // Cuci karakter khusus HTML (Decoded)
                        let decodedExp = exp
                            .replace(/&gt;/g, '>')
                            .replace(/&lt;/g, '<')
                            .replace(/&amp;/g, '&')
                            .replace(/&quot;/g, '"')
                            .replace(/&#39;/g, "'")
                            .replace(/&nbsp;/g, ' ')
                            .replace(/\u00A0/g, ' ')
                            .replace(/<br\s*\/?>/gi, '\n');

                        try {
                            katex.render(decodedExp, el, {
                                throwOnError: false,
                                displayMode: el.style.display === 'block' || el.tagName === 'DIV'
                            });
                        } catch (e) {
                            console.error("Gagal render KaTeX:", e);
                        }
                    }
                });
            }
        });

        function confirmDelete(id) {
            Swal.fire({
                title: 'Hapus Pertanyaan?',
                text: "Data tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Ya, Hapus'
            }).then(r => {
                if(r.isConfirmed) {
                    axios.delete(`/admin/exams/{{ $exam->getRouteKey() }}/soal/${id}`)
                        .then(() => window.location.reload())
                        .catch(err => Swal.fire('Error', 'Gagal menghapus soal', 'error'));
                }
            });
        }
    </script>
</x-app-layout>
