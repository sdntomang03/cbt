<x-app-layout> @slot('header')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/katex.min.css">
    @endslot

    <div class="max-w-5xl mx-auto py-10 px-4 sm:px-6 lg:px-8">

        <div
            class="bg-white rounded-3xl p-8 shadow-sm border border-slate-200 mb-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-800">Pembahasan Ujian</h1>
                <p class="text-slate-500 mt-1">{{ $session->exam->title }}</p>
            </div>
            <div class="bg-emerald-50 border border-emerald-100 text-emerald-700 px-6 py-3 rounded-2xl text-center">
                <span class="block text-xs uppercase tracking-widest font-bold opacity-70">Nilai Akhir</span>
                <span class="block text-3xl font-black">{{ $session->user_score }}</span>
            </div>
        </div>

        <div class="space-y-8">
            @foreach($session->exam->questions as $index => $q)
            <div class="bg-white rounded-3xl p-6 sm:p-10 shadow-sm border border-slate-200 relative overflow-hidden">

                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-indigo-500 to-purple-500"></div>

                <div class="flex items-center gap-3 mb-6">
                    <span class="bg-indigo-600 text-white px-4 py-1.5 rounded-xl font-black shadow-md text-sm">
                        NO. {{ $index + 1 }}
                    </span>
                    <span
                        class="text-[10px] font-black text-slate-400 uppercase tracking-widest bg-slate-100 px-3 py-1 rounded-full">
                        {{ str_replace('_', ' ', $q->type) }}
                    </span>
                </div>

                <div class="prose prose-indigo max-w-none text-slate-700 mb-8">
                    {!! $q->content !!}
                </div>

                @if(in_array($q->type, ['single_choice', 'complex_choice', 'true_false']))
                <div class="space-y-3 mb-8 pl-0 sm:pl-4 border-l-2 border-slate-100">
                    @foreach($q->options as $opt)
                    <div
                        class="p-4 rounded-2xl border-2 transition-all flex items-start gap-4
                                    {{ $opt->is_correct ? 'bg-emerald-50 border-emerald-400' : 'bg-slate-50 border-slate-100 opacity-70' }}">

                        <div class="mt-1">
                            @if($opt->is_correct)
                            <i class="fas fa-check-circle text-emerald-500 text-xl"></i>
                            @else
                            <i class="fas fa-circle text-slate-300"></i>
                            @endif
                        </div>
                        <div class="prose prose-sm max-w-none text-slate-700">
                            {!! $opt->option_text !!}
                        </div>

                    </div>
                    @endforeach
                </div>
                @endif

                @if($q->explanation)
                <div
                    class="bg-gradient-to-br from-indigo-50 to-blue-50 border border-indigo-100 rounded-2xl p-6 relative">
                    <div class="flex items-center gap-2 mb-4">
                        <i class="fas fa-lightbulb text-amber-500 text-lg shadow-amber-200"></i>
                        <h4 class="font-black text-indigo-900 tracking-wide text-sm uppercase">Pembahasan Detail</h4>
                    </div>

                    <div class="prose prose-indigo max-w-none text-slate-700 text-sm md:text-base leading-relaxed">
                        {!! $q->explanation !!}
                    </div>
                </div>
                @else
                <div class="bg-slate-50 border border-slate-200 border-dashed rounded-2xl p-4 text-center">
                    <span class="text-slate-400 text-sm italic">Tidak ada pembahasan khusus untuk soal ini.</span>
                </div>
                @endif

            </div>
            @endforeach
        </div>

        <div class="mt-10 text-center">
            <a href="{{ route('student.dashboard') }}"
                class="inline-flex items-center gap-2 bg-slate-800 text-white px-8 py-3 rounded-xl font-bold hover:bg-slate-700 transition-colors">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>

    </div>
</x-app-layout>
