<x-app-layout>
    <div class="py-10 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">

        {{-- ================= HEADER & NAVIGASI ================= --}}
        <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.math.show', $examUser->math_exam_id) }}"
                    class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 transition-all shadow-sm border border-slate-200 group">
                    <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
                </a>
                <div>
                    <h2 class="text-3xl font-black text-slate-800 tracking-tight leading-none">Detail Jawaban</h2>
                    <p class="text-slate-500 font-bold mt-2 text-sm">{{ $examUser->exam->title }}</p>
                </div>
            </div>

            <div class="flex items-center gap-3">

                <button onclick="window.print()"
                    class="bg-slate-800 hover:bg-slate-900 text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-slate-200 transition-all hover:-translate-y-0.5 flex items-center gap-2">
                    <i class="fas fa-print"></i> Cetak Hasil
                </button>
            </div>
        </div>

        {{-- ================= KARTU PROFIL & HASIL ================= --}}
        <div
            class="bg-white p-6 md:p-8 rounded-[2rem] shadow-sm border border-slate-200 mb-8 relative overflow-hidden flex flex-col md:flex-row gap-8 justify-between items-center">

            {{-- Aksen Latar --}}
            <div
                class="absolute right-0 top-0 w-64 h-64 bg-gradient-to-bl from-indigo-50 to-transparent rounded-bl-full -z-10">
            </div>

            {{-- Info Siswa --}}
            <div class="flex items-center gap-6 w-full md:w-auto">
                <div
                    class="w-20 h-20 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-500 text-3xl border-4 border-white shadow-md flex-shrink-0">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div>
                    <h3 class="font-black text-2xl text-slate-800 mb-1">{{ $examUser->student->name ?? 'Siswa Terhapus'
                        }}</h3>
                    <div class="flex items-center gap-3 text-sm">
                        <span
                            class="font-bold text-indigo-600 uppercase tracking-wider bg-indigo-50 px-3 py-1 rounded-lg">
                            <i class="fas fa-school mr-1"></i> {{ $examUser->student->school->name ?? 'Tanpa Sekolah' }}
                        </span>
                        @if($examUser->status === 'completed')
                        <span class="font-bold text-emerald-600 bg-emerald-50 px-3 py-1 rounded-lg"><i
                                class="fas fa-check-circle mr-1"></i> Selesai</span>
                        @else
                        <span class="font-bold text-amber-600 bg-amber-50 px-3 py-1 rounded-lg"><i
                                class="fas fa-clock mr-1"></i> Sedang Berjalan</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Info Nilai & Analisis --}}
            <div
                class="flex flex-col md:items-end gap-3 w-full md:w-auto border-t md:border-t-0 md:border-l border-slate-100 pt-6 md:pt-0 md:pl-8">

                {{-- Nilai Akhir Besar --}}
                <div class="text-left md:text-right">
                    <span class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-1">Nilai Akhir
                        Keseluruhan</span>
                    <span
                        class="text-5xl font-black {{ $examUser->score >= 70 ? 'text-emerald-500' : 'text-rose-500' }} drop-shadow-sm">
                        {{ $examUser->score ?? 0 }}
                    </span>
                </div>

                {{-- Analisis Per Tipe --}}
                @php
                $stats = collect(['+' => 'Tambah', '-' => 'Kurang', 'x' => 'Kali', ':' => 'Bagi'])->map(function ($name,
                $op) use ($questions) {
                $soalJenisIni = $questions->where('operator', $op);
                $total = $soalJenisIni->count();
                if ($total === 0) return null;

                $benar = $soalJenisIni->where('is_correct', true)->count();
                $persen = round(($benar / $total) * 100);
                return (object) [
                'name' => $name, 'total' => $total, 'benar' => $benar, 'persen' => $persen,
                'bg' => $persen >= 70 ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : ($persen >= 40 ?
                'bg-amber-50 border-amber-200 text-amber-700' : 'bg-rose-50 border-rose-200 text-rose-700')
                ];
                })->filter();
                @endphp

                @if($stats->count() > 0)
                <div class="flex flex-wrap gap-2 mt-2">
                    @foreach($stats as $s)
                    <div class="border px-2.5 py-1 rounded-md flex items-center gap-1.5 shadow-sm cursor-help {{ $s->bg }}"
                        title="Benar {{ $s->benar }} dari {{ $s->total }} Soal">
                        <span class="text-[9px] font-black uppercase opacity-70">{{ $s->name }}</span>
                        <span class="text-xs font-black">{{ $s->persen }}%</span>
                    </div>
                    @endforeach
                </div>
                @endif

            </div>
        </div>

        {{-- ================= LEMBAR KOREKSI SOAL ================= --}}
        <div class="flex items-center justify-between mb-6 px-2 border-b border-slate-200 pb-4">
            <h3 class="font-black text-xl text-slate-800 flex items-center gap-2">
                <i class="fas fa-tasks text-indigo-500"></i> Lembar Koreksi
                <span class="bg-slate-100 text-slate-500 text-sm px-3 py-1 rounded-full">{{ $questions->count() }}
                    Soal</span>
            </h3>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @foreach($questions as $index => $q)
            @php
            $isCorrect = $q->is_correct;
            $isUnanswered = is_null($q->student_answer);

            // Styling berdasarkan status jawaban
            if ($isUnanswered) {
            $theme = ['border' => 'border-slate-300', 'bg' => 'bg-slate-50', 'text' => 'text-slate-500', 'icon' =>
            'fa-minus-circle', 'label' => 'KOSONG'];
            } elseif ($isCorrect) {
            $theme = ['border' => 'border-emerald-300', 'bg' => 'bg-emerald-50/50', 'text' => 'text-emerald-600', 'icon'
            => 'fa-check-circle', 'label' => 'BENAR'];
            } else {
            $theme = ['border' => 'border-rose-300', 'bg' => 'bg-rose-50/50', 'text' => 'text-rose-600', 'icon' =>
            'fa-times-circle', 'label' => 'SALAH'];
            }

            $opIcon = $q->operator == 'x' ? '&times;' : ($q->operator == ':' ? '&divide;' : $q->operator);
            @endphp

            <div
                class="rounded-[1.5rem] border-2 {{ $theme['border'] }} {{ $theme['bg'] }} flex flex-col overflow-hidden shadow-sm hover:shadow-md transition-shadow relative">

                {{-- Header Soal --}}
                <div class="flex justify-between items-center px-5 py-3 border-b border-black/5 bg-white/50">
                    <span class="text-[10px] font-black text-slate-400 tracking-widest uppercase">Soal #{{
                        str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                    <div
                        class="flex items-center gap-1.5 text-[10px] font-black tracking-wider uppercase {{ $theme['text'] }}">
                        <i class="fas {{ $theme['icon'] }} text-sm"></i> {{ $theme['label'] }}
                    </div>
                </div>

                {{-- Isi Pertanyaan --}}
                <div class="flex-1 flex flex-col items-center justify-center p-6">
                    <div class="text-3xl font-black text-slate-800 flex items-center justify-center gap-3">
                        <span>{{ $q->num1 }}</span>
                        <span
                            class="text-indigo-500 bg-indigo-50 w-10 h-10 rounded-xl flex items-center justify-center shadow-sm text-2xl">{!!
                            $opIcon !!}</span>
                        <span>{{ $q->num2 }}</span>
                    </div>

                    {{-- Jawaban Siswa --}}
                    <div class="mt-6 flex items-center gap-4 text-xl font-bold text-slate-400">
                        =
                        <div
                            class="min-w-[80px] text-center pb-1 border-b-4 {{ $isUnanswered ? 'border-slate-300' : ($isCorrect ? 'border-emerald-400' : 'border-rose-400') }}">
                            <span class="font-black text-4xl {{ $theme['text'] }} drop-shadow-sm">
                                {{ $isUnanswered ? '?' : $q->student_answer }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Kunci Jawaban (Hanya muncul jika salah/kosong) --}}
                @if(!$isCorrect)
                <div
                    class="bg-white border-t border-rose-100 p-3 text-center flex flex-col justify-center items-center gap-1">
                    <span class="text-[9px] font-black text-rose-400 uppercase tracking-widest">Kunci Jawaban
                        Seharusnya:</span>
                    <span class="text-xl font-black text-emerald-600">{{ $q->correct_answer }}</span>
                </div>
                @endif

            </div>
            @endforeach
        </div>

        {{-- Style untuk Print --}}
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
                .shadow-md,
                .shadow-lg {
                    box-shadow: none !important;
                }

                .bg-white,
                .bg-slate-50 {
                    background-color: transparent !important;
                }

                /* Memastikan background color pada badge ikut terprint */
                * {
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }
            }
        </style>
    </div>
</x-app-layout>