<x-public-layout :page-title="$module->title . ' - CBT Pro'">
    <div class="bg-slate-50 min-h-screen pb-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-10">

            <a href="{{ route('public.modules.index') }}"
                class="inline-flex items-center text-sm font-bold text-slate-500 hover:text-blue-600 mb-6 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Katalog
            </a>

            <article class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden">
                {{-- Header Modul --}}
                <div class="p-8 sm:p-12 border-b border-slate-100 text-center">
                    <div class="flex justify-center gap-2 mb-6">
                        <span
                            class="text-xs font-bold text-blue-600 bg-blue-50 px-3 py-1 rounded-full border border-blue-100">{{
                            $module->subject->name }}</span>
                    </div>
                    <h1 class="text-3xl md:text-4xl font-black text-slate-900 mb-4 leading-tight">{{ $module->title }}
                    </h1>
                    <div class="flex items-center justify-center gap-6 text-sm font-bold text-slate-500">
                        <span><i class="far fa-clock mr-1 text-slate-400"></i> {{ $module->estimated_time_minutes }}
                            Menit Baca</span>
                        <span><i class="far fa-eye mr-1 text-slate-400"></i> {{ $module->view_count }}x Dibaca</span>
                    </div>
                </div>

                {{-- Konten Video --}}
                @if($module->video_url)
                <div class="aspect-w-16 aspect-h-9 bg-slate-900">
                    <iframe src="{{ $module->video_url }}" frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen class="w-full h-[400px]"></iframe>
                </div>
                @endif

                {{-- Isi Teks --}}
                <div class="p-8 sm:p-12 prose prose-lg prose-blue max-w-none text-slate-600">
                    {!! $module->content !!}
                </div>

                {{-- Footer & File --}}
                <div
                    class="p-8 bg-slate-50 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                    @if($module->document_path)
                    <a href="{{ asset('storage/' . $module->document_path) }}" target="_blank"
                        class="flex items-center gap-3 bg-white border border-slate-200 hover:border-blue-500 text-slate-700 hover:text-blue-600 px-6 py-3 rounded-xl font-bold transition-all shadow-sm">
                        <i class="fas fa-file-pdf text-rose-500 text-xl"></i> Download PDF Materi
                    </a>
                    @else
                    <div></div>
                    @endif
                    <div class="text-sm text-slate-500">
                        <span>Ditulis oleh <strong>{{ $module->author->name }}</strong></span>
                        <span class="mx-2">|</span>
                        <span>Diterbitkan pada {{ $module->created_at->format('d M Y') }}</span>
                    </div>
            </article>
        </div>
    </div>


    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css">
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Cari semua elemen yang dihasilkan oleh tombol Formula Quill
            const formulas = document.querySelectorAll('.ql-formula');

            formulas.forEach(function(el) {
                const mathExpression = el.getAttribute('data-value');
                if (mathExpression) {
                    // Render string LaTeX menjadi elemen visual matematika
                    katex.render(mathExpression, el, {
                        throwOnError: false,
                        displayMode: false // Ubah ke true jika ingin rumus di tengah baris
                    });
                }
            });
        });
    </script>
</x-public-layout>