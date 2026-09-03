<x-app-layout>
    <div class="min-h-screen py-10 bg-slate-50 font-nunito">
        <div class="max-w-5xl mx-auto px-4 sm:px-6">

            <!-- Header Halaman -->
            <div
                class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm mb-6 flex flex-wrap justify-between items-center gap-4">
                <div class="flex items-center gap-4">
                    <div
                        class="w-14 h-14 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center text-xl font-black shadow-inner">
                        {{ substr($student->name, 0, 1) }}
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-slate-800 tracking-tight">{{ $student->name }}</h2>
                        <p class="text-slate-500 font-bold text-sm">{{ $examSession->exam->title }} &bull; {{
                            $student->school->name ?? 'Sekolah Pusat' }}</p>
                    </div>
                </div>
                <div class="text-right bg-slate-50 px-6 py-3 rounded-2xl border border-slate-100">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Skor Akhir</p>
                    <p class="text-3xl font-black text-emerald-500 leading-none">{{ $examUser->score }}</p>
                </div>
            </div>

            <!-- Daftar Soal & Jawaban -->
            <div class="space-y-6">
                @forelse($answers as $index => $ans)
                @php
                $q = $ans->question;
                $isCorrect = $ans->score > 0;
                @endphp

                <div
                    class="bg-white border rounded-[2rem] p-6 sm:p-8 relative overflow-hidden {{ $isCorrect ? 'border-emerald-200 shadow-[0_4px_20px_rgba(16,185,129,0.05)]' : 'border-rose-200 shadow-[0_4px_20px_rgba(244,63,94,0.05)]' }}">

                    <!-- Pita Status -->
                    <div
                        class="absolute top-0 right-0 px-5 py-1.5 text-[10px] font-black uppercase tracking-wider rounded-bl-2xl {{ $isCorrect ? 'bg-emerald-500 text-white' : 'bg-rose-500 text-white' }}">
                        {{ $isCorrect ? 'Poin: ' . $ans->score : 'Salah (0)' }}
                    </div>

                    <!-- Konten Soal -->
                    <div class="flex gap-4 mb-6 mt-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center font-black shrink-0">
                            {{ $index + 1 }}
                        </div>
                        <div class="prose prose-slate max-w-none prose-p:leading-relaxed">
                            {!! $q->content !!}
                        </div>
                    </div>

                    <!-- Blok Jawaban Siswa -->
                    <div class="ml-14 bg-slate-50 rounded-2xl p-5 border border-slate-100">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Jawaban
                            Diberikan:</p>

                        <div class="text-sm font-bold text-slate-700">
                            {{-- SINGLE CHOICE & ESSAY --}}
                            @if($q->type === 'single_choice' || $q->type === 'essay')
                            @php
                            $selectedOpt = $q->options->where('id', $ans->formatted_answer)->first();
                            @endphp
                            @if($selectedOpt)
                            {!! $selectedOpt->option_text !!}
                            @else
                            {{ $ans->formatted_answer ?? '- Tidak Menjawab -' }}
                            @endif

                            {{-- COMPLEX CHOICE --}}
                            @elseif($q->type === 'complex_choice')
                            <ul class="list-disc list-inside space-y-1">
                                @if(is_array($ans->formatted_answer) && count($ans->formatted_answer) > 0)
                                @foreach($ans->formatted_answer as $optId)
                                @php $cOpt = $q->options->where('id', $optId)->first(); @endphp
                                <li>{!! $cOpt ? $cOpt->option_text : $optId !!}</li>
                                @endforeach
                                @else
                                <span class="text-slate-400 italic">- Tidak Menjawab -</span>
                                @endif
                            </ul>

                            {{-- TRUE / FALSE --}}
                            @elseif(in_array($q->type, ['true_false', 'true_false_multi']))
                            <div class="space-y-3">
                                @if(is_array($ans->formatted_answer) && count($ans->formatted_answer) > 0)
                                @foreach($ans->formatted_answer as $optId => $tfValue)
                                @php $tfOpt = $q->options->where('id', $optId)->first(); @endphp
                                <div class="flex items-start gap-3">
                                    <span
                                        class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider shrink-0 mt-0.5 {{ strtolower($tfValue) === 'benar' ? 'bg-emerald-500 text-white' : 'bg-rose-500 text-white' }}">
                                        {{ $tfValue }}
                                    </span>
                                    <div class="prose prose-sm text-slate-600 leading-tight">
                                        {!! $tfOpt ? $tfOpt->option_text : '- Pernyataan hilang -' !!}
                                    </div>
                                </div>
                                @endforeach
                                @else
                                <span class="text-slate-400 italic">- Tidak Menjawab -</span>
                                @endif
                            </div>

                            {{-- MATCHING / MENJODOHKAN --}}
                            @elseif($q->type === 'matching')
                            <div class="space-y-2">
                                @if(is_array($ans->formatted_answer) && count($ans->formatted_answer) > 0)
                                @foreach($ans->formatted_answer as $premiseId => $targetId)
                                @php
                                $premise = $q->matches->where('id', $premiseId)->first();
                                $target = $q->matches->where('id', $targetId)->first();
                                @endphp
                                <div
                                    class="flex items-center gap-3 bg-white p-3 rounded-xl border border-slate-200 shadow-sm text-xs">
                                    <div class="flex-1 text-slate-700">{!! $premise ? $premise->premise_text :
                                        '<i>Premis tidak ditemukan</i>' !!}</div>
                                    <div class="w-6 flex justify-center text-indigo-400 shrink-0"><i
                                            class="fas fa-arrow-right"></i></div>
                                    <div class="flex-1 text-indigo-700">{!! $target ? $target->target_text : '<i>Target
                                            tidak ditemukan</i>' !!}</div>
                                </div>
                                @endforeach
                                @else
                                <span class="text-slate-400 italic">- Tidak Menjawab -</span>
                                @endif
                            </div>

                            {{-- FALLBACK / TIPE LAIN --}}
                            @else
                            <pre
                                class="text-xs bg-slate-200 p-3 rounded-xl overflow-x-auto">{{ json_encode($ans->formatted_answer, JSON_PRETTY_PRINT) }}</pre>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="bg-white rounded-3xl p-10 text-center border border-slate-200">
                    <i class="fas fa-box-open text-4xl text-slate-300 mb-4"></i>
                    <h3 class="text-lg font-bold text-slate-700">Tidak ada rekam jejak jawaban.</h3>
                </div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>