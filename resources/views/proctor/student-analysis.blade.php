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
                <div
                    class="text-right bg-slate-50 px-6 py-3 rounded-2xl border border-slate-100 flex gap-4 items-center">
                    <div class="text-right">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Soal</p>
                        <p class="text-lg font-black text-slate-700 leading-none">{{ count($answers) }}</p>
                    </div>
                    <div class="w-px h-8 bg-slate-200"></div>
                    <div class="text-right">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Skor Akhir</p>
                        <p class="text-3xl font-black text-emerald-500 leading-none">{{ $examUser->score }}</p>
                    </div>
                </div>
            </div>

            <!-- Legenda -->
            <div class="flex gap-4 mb-6 px-2">
                <div class="flex items-center gap-2 text-xs font-bold text-slate-600">
                    <div
                        class="w-4 h-4 rounded-md border-2 border-emerald-500 bg-emerald-50 flex items-center justify-center">
                        <i class="fas fa-check text-[8px] text-emerald-600"></i></div>
                    Kunci Jawaban Benar
                </div>
                <div class="flex items-center gap-2 text-xs font-bold text-slate-600">
                    <div class="w-4 h-4 rounded-md bg-slate-800 flex items-center justify-center"><i
                            class="fas fa-times text-[8px] text-white"></i></div>
                    Jawaban Siswa (Salah)
                </div>
                <div class="flex items-center gap-2 text-xs font-bold text-slate-600">
                    <div class="w-4 h-4 rounded-md bg-emerald-500 flex items-center justify-center"><i
                            class="fas fa-check text-[8px] text-white"></i></div>
                    Jawaban Siswa (Benar)
                </div>
            </div>

            <!-- Daftar Soal & Jawaban -->
            <div class="space-y-6">
                @forelse($answers as $index => $ans)
                @php
                $q = $ans->question;
                $isCorrect = $ans->score > 0;
                $studentAns = $ans->formatted_answer;
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
                            class="w-10 h-10 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center font-black shrink-0 shadow-sm border border-slate-200">
                            {{ $index + 1 }}
                        </div>
                        <div class="prose prose-slate max-w-none prose-p:leading-relaxed text-slate-800">
                            {!! $q->content !!}
                        </div>
                    </div>

                    <!-- Blok Opsi Jawaban (Visual Ujian) -->
                    <div class="ml-14 mt-6">

                        {{-- TIPE: PILIHAN GANDA (SINGLE & COMPLEX) --}}
                        @if($q->type === 'single_choice' || $q->type === 'complex_choice')
                        <div class="space-y-3">
                            @php
                            $abjad = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
                            // Pastikan studentAns menjadi array untuk memudahkan pengecekan, baik single maupun complex
                            $studentSelectedIds = is_array($studentAns) ? $studentAns : [$studentAns];
                            @endphp

                            @foreach($q->options as $optIndex => $opt)
                            @php
                            $isKey = $opt->is_correct;
                            $isChosen = in_array($opt->id, $studentSelectedIds);

                            // Styling kelas berdasarkan status pilihan
                            $boxClass = "border-slate-200 bg-white text-slate-600"; // Default
                            $iconHtml = "";

                            if($isKey && $isChosen) {
                            $boxClass = "border-emerald-500 bg-emerald-50 text-emerald-800 ring-1 ring-emerald-500";
                            $iconHtml = "<div
                                class='absolute -right-2 -top-2 w-6 h-6 bg-emerald-500 rounded-full flex items-center justify-center border-2 border-white shadow-sm'>
                                <i class='fas fa-check text-[10px] text-white'></i></div>";
                            } elseif($isKey && !$isChosen) {
                            $boxClass = "border-emerald-400 bg-emerald-50/50 text-emerald-700 border-dashed";
                            $iconHtml = "<div
                                class='absolute -right-2 -top-2 w-6 h-6 bg-white border-2 border-emerald-400 rounded-full flex items-center justify-center shadow-sm'>
                                <i class='fas fa-check text-[10px] text-emerald-500'></i></div>";
                            } elseif(!$isKey && $isChosen) {
                            $boxClass = "border-rose-400 bg-rose-50 text-rose-800";
                            $iconHtml = "<div
                                class='absolute -right-2 -top-2 w-6 h-6 bg-slate-800 rounded-full flex items-center justify-center border-2 border-white shadow-sm'>
                                <i class='fas fa-times text-[10px] text-white'></i></div>";
                            }
                            @endphp

                            <div
                                class="relative p-4 rounded-xl border-2 transition-all flex gap-4 items-center {{ $boxClass }}">
                                <!-- Huruf Abjad -->
                                <div
                                    class="w-8 h-8 rounded-lg flex items-center justify-center font-black text-sm shrink-0
                                            {{ ($isChosen && $isKey) ? 'bg-emerald-500 text-white' :
                                               (($isChosen && !$isKey) ? 'bg-slate-800 text-white' :
                                               ($isKey ? 'bg-emerald-100 text-emerald-600 border border-emerald-300' : 'bg-slate-100 text-slate-500')) }}">
                                    {{ $abjad[$optIndex] ?? '*' }}
                                </div>

                                <!-- Teks Opsi -->
                                <div class="prose prose-sm max-w-none flex-1 leading-snug">
                                    {!! $opt->option_text !!}
                                </div>

                                <!-- Ikon Status -->
                                {!! $iconHtml !!}
                            </div>
                            @endforeach
                        </div>

                        {{-- TIPE: ESSAY --}}
                        @elseif($q->type === 'essay')
                        <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Jawaban
                                Siswa:</p>
                            <div
                                class="text-sm font-bold text-slate-800 bg-white p-4 rounded-xl border border-slate-200 mb-4">
                                {{ $studentAns ?? '- Tidak Menjawab -' }}
                            </div>

                            <p class="text-[10px] font-black text-emerald-500 uppercase tracking-widest mb-2">Kunci /
                                Referensi Guru:</p>
                            <div
                                class="text-sm text-emerald-700 bg-emerald-50 p-4 rounded-xl border border-emerald-200 border-dashed">
                                {!! $q->options->first()->option_text ?? '<i>Tidak ada referensi kunci</i>' !!}
                            </div>
                        </div>

                        {{-- TIPE: TRUE / FALSE --}}
                        @elseif(in_array($q->type, ['true_false', 'true_false_multi']))
                        <div class="space-y-3">
                            @foreach($q->options as $opt)
                            @php
                            $isKeyTrue = $opt->is_correct; // True = 'benar', False = 'salah' di database
                            $studentPicked = isset($studentAns[$opt->id]) ? strtolower($studentAns[$opt->id]) : null;

                            $answeredCorrectly = ($isKeyTrue && $studentPicked === 'benar') || (!$isKeyTrue &&
                            $studentPicked === 'salah');
                            @endphp
                            <div
                                class="p-4 rounded-xl border-2 flex flex-col sm:flex-row sm:items-center gap-4 {{ $answeredCorrectly ? 'border-emerald-200 bg-emerald-50/50' : 'border-rose-200 bg-rose-50/50' }}">

                                <!-- Teks Pernyataan -->
                                <div class="prose prose-sm flex-1 text-slate-700">
                                    {!! $opt->option_text !!}
                                </div>

                                <!-- Pilihan -->
                                <div class="flex gap-2 shrink-0">
                                    <div
                                        class="px-3 py-1.5 rounded-lg border-2 text-xs font-black uppercase
                                                {{ $studentPicked === 'benar' ? ($answeredCorrectly ? 'bg-emerald-500 border-emerald-500 text-white' : 'bg-slate-800 border-slate-800 text-white') :
                                                   ($isKeyTrue ? 'bg-emerald-50 border-emerald-400 text-emerald-600 border-dashed' : 'bg-white border-slate-200 text-slate-400 opacity-50') }}">
                                        Benar
                                    </div>
                                    <div
                                        class="px-3 py-1.5 rounded-lg border-2 text-xs font-black uppercase
                                                {{ $studentPicked === 'salah' ? ($answeredCorrectly ? 'bg-emerald-500 border-emerald-500 text-white' : 'bg-slate-800 border-slate-800 text-white') :
                                                   (!$isKeyTrue ? 'bg-emerald-50 border-emerald-400 text-emerald-600 border-dashed' : 'bg-white border-slate-200 text-slate-400 opacity-50') }}">
                                        Salah
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        {{-- TIPE: MATCHING --}}
                        @elseif($q->type === 'matching')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Jawaban
                                    Siswa</p>
                                @if(is_array($studentAns) && count($studentAns) > 0)
                                @foreach($studentAns as $premiseId => $targetId)
                                @php
                                $premise = $q->matches->where('id', $premiseId)->first();
                                $target = $q->matches->where('id', $targetId)->first();
                                $isMatchCorrect = ($premiseId == $targetId);
                                @endphp
                                <div
                                    class="flex flex-col gap-1 p-3 rounded-xl border-2 {{ $isMatchCorrect ? 'border-emerald-200 bg-emerald-50' : 'border-rose-200 bg-rose-50' }} text-xs">
                                    <div class="text-slate-700 font-bold">{!! $premise ? $premise->premise_text : '?'
                                        !!}</div>
                                    <div class="text-slate-400 flex items-center gap-2"><i
                                            class="fas fa-link text-[10px]"></i> Dipasangkan ke:</div>
                                    <div class="{{ $isMatchCorrect ? 'text-emerald-700' : 'text-rose-700' }} font-bold">
                                        {!! $target ? $target->target_text : '?' !!}</div>
                                </div>
                                @endforeach
                                @else
                                <div class="p-3 text-sm text-slate-400 italic border border-slate-200 rounded-xl">-
                                    Kosong -</div>
                                @endif
                            </div>
                            <div class="space-y-2">
                                <p class="text-[10px] font-black text-emerald-500 uppercase tracking-widest mb-2">Kunci
                                    Seharusnya</p>
                                @foreach($q->matches as $match)
                                <div
                                    class="flex flex-col gap-1 p-3 rounded-xl border-2 border-emerald-100 border-dashed bg-white text-xs">
                                    <div class="text-slate-600">{!! $match->premise_text !!}</div>
                                    <div class="text-emerald-300 flex items-center gap-2"><i
                                            class="fas fa-check text-[10px]"></i> Target:</div>
                                    <div class="text-emerald-600 font-bold">{!! $match->target_text !!}</div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- FALLBACK --}}
                        @else
                        <pre
                            class="text-xs bg-slate-200 p-3 rounded-xl overflow-x-auto">{{ json_encode($studentAns, JSON_PRETTY_PRINT) }}</pre>
                        @endif

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