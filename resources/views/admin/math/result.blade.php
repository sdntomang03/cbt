<x-app-layout>
    <div class="py-10 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">

        <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.math.show', $examUser->math_exam_id) }}"
                    class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 transition-colors shadow-sm border border-slate-100">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h2 class="text-3xl font-black text-slate-800 tracking-tight">Detail Jawaban</h2>
                    <p class="text-slate-500 font-bold mt-1">{{ $examUser->exam->title }}</p>
                </div>
            </div>

            <button onclick="window.print()"
                class="bg-slate-800 hover:bg-black text-white px-6 py-3 rounded-xl font-bold shadow-lg transition-transform hover:-translate-y-1 flex items-center gap-2">
                <i class="fas fa-print"></i> Cetak Hasil
            </button>
        </div>

        <div
            class="bg-white p-6 md:p-8 rounded-[2rem] shadow-sm border border-slate-100 mb-8 flex flex-col md:flex-row md:items-center justify-between gap-6 relative overflow-hidden">
            <div class="absolute right-0 top-0 w-48 h-48 bg-indigo-50 rounded-bl-full -z-10 opacity-50"></div>

            <div class="flex items-center gap-6">
                <div
                    class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 text-2xl border-4 border-white shadow-md">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div>
                    <h3 class="font-black text-2xl text-slate-800">{{ $examUser->student->name ?? 'Siswa Terhapus' }}
                    </h3>
                    <p class="text-sm font-bold text-indigo-500 uppercase tracking-wider">{{
                        $examUser->student->school->name ?? 'Tanpa Sekolah' }}</p>
                </div>
            </div>

            <div class="flex gap-4">
                <div class="bg-slate-50 border border-slate-100 px-6 py-3 rounded-2xl text-center">
                    <span class="block text-xs font-black text-slate-400 uppercase">Status</span>
                    <span class="font-bold text-slate-700">
                        @if($examUser->status === 'completed') Selesai @elseif($examUser->status === 'ongoing') Berjalan
                        @else Belum Mulai @endif
                    </span>
                </div>
                <div class="bg-indigo-50 border border-indigo-100 px-6 py-3 rounded-2xl text-center">
                    <span class="block text-xs font-black text-indigo-400 uppercase">Nilai Akhir</span>
                    <span
                        class="text-2xl font-black {{ $examUser->score >= 70 ? 'text-emerald-500' : 'text-rose-500' }}">{{
                        $examUser->score ?? 0 }}</span>
                </div>
            </div>
        </div>

        <h3 class="font-black text-lg text-slate-700 mb-4 px-2">Lembar Koreksi ({{ $questions->count() }} Soal)</h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($questions as $index => $q)
            @php
            // Tentukan warna border dan background berdasarkan benar/salah
            $isCorrect = $q->is_correct;
            $isUnanswered = is_null($q->student_answer);

            if ($isUnanswered) {
            $borderColor = 'border-slate-300';
            $bgColor = 'bg-slate-50';
            $icon = '<i class="fas fa-minus-circle text-slate-400"></i>';
            $statusText = 'Kosong';
            } elseif ($isCorrect) {
            $borderColor = 'border-emerald-200';
            $bgColor = 'bg-emerald-50/30';
            $icon = '<i class="fas fa-check-circle text-emerald-500"></i>';
            $statusText = 'Benar';
            } else {
            $borderColor = 'border-rose-200';
            $bgColor = 'bg-rose-50/30';
            $icon = '<i class="fas fa-times-circle text-rose-500"></i>';
            $statusText = 'Salah';
            }

            // Tampilan Operator
            $opIcon = $q->operator;
            if($opIcon == 'x') $opIcon = '&times;';
            if($opIcon == ':') $opIcon = '&divide;';
            @endphp

            <div class="p-5 rounded-2xl border-2 {{ $borderColor }} {{ $bgColor }} relative flex flex-col">

                <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-200/50">
                    <span class="text-xs font-black text-slate-400">SOAL #{{ $index + 1 }}</span>
                    <div class="flex items-center gap-1.5 text-sm font-bold">
                        {!! $icon !!} <span
                            class="{{ $isCorrect ? 'text-emerald-600' : ($isUnanswered ? 'text-slate-500' : 'text-rose-600') }}">{{
                            $statusText }}</span>
                    </div>
                </div>

                <div class="flex-1 flex flex-col items-center justify-center text-center py-2">
                    <div class="text-2xl font-black text-slate-800 flex items-center justify-center gap-3">
                        <span>{{ $q->num1 }}</span>
                        <span class="text-indigo-500">{!! $opIcon !!}</span>
                        <span>{{ $q->num2 }}</span>
                    </div>

                    <div class="mt-3 text-lg font-bold text-slate-500">
                        = <span
                            class="font-black text-3xl {{ $isCorrect ? 'text-emerald-500' : ($isUnanswered ? 'text-slate-400' : 'text-rose-500') }}">{{
                            $isUnanswered ? '?' : $q->student_answer }}</span>
                    </div>
                </div>

                @if(!$isCorrect)
                <div class="mt-4 pt-3 border-t border-rose-100/50 bg-white rounded-xl p-2 text-center shadow-sm">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider block mb-1">Kunci
                        Jawaban</span>
                    <span class="text-lg font-black text-emerald-500">{{ $q->correct_answer }}</span>
                </div>
                @endif

            </div>
            @endforeach
        </div>

        <style>
            @media print {
                body * {
                    visibility: hidden;
                }

                .max-w-7xl,
                .max-w-7xl * {
                    visibility: visible;
                }

                .max-w-7xl {
                    position: absolute;
                    left: 0;
                    top: 0;
                    width: 100%;
                    padding: 0;
                }

                button,
                a {
                    display: none !important;
                }

                .shadow-sm,
                .shadow-md {
                    box-shadow: none !important;
                }

                .bg-white,
                .bg-slate-50 {
                    background-color: transparent !important;
                }
            }
        </style>
    </div>
</x-app-layout>