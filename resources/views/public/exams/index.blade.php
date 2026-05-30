<x-public-layout>
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        {{-- ... Header Halaman Tetap Sama ... --}}

        @if($publicExams->isEmpty())
        {{-- ... Tampilan Kosong Tetap Sama ... --}}
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($publicExams as $exam)
            <div
                class="bg-white rounded-[2rem] p-8 border border-slate-200 hover:shadow-2xl hover:-translate-y-1.5 transition-all duration-300 flex flex-col h-full relative overflow-hidden group">

                <div class="flex items-start justify-between mb-5 relative z-10">
                    <div
                        class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-2xl">
                        <i class="fas fa-file-signature"></i>
                    </div>
                    <span
                        class="bg-emerald-50 text-emerald-600 px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-1.5 border border-emerald-200">
                        <i class="fas fa-globe text-emerald-500"></i> Terbuka
                    </span>
                </div>

                <div class="flex-1 relative z-10">
                    <h3 class="text-xl font-black text-slate-800 mb-3">{{ $exam->title }}</h3>
                    <p class="text-sm text-slate-500 mb-8">{{ $exam->description ?? 'Tidak ada deskripsi.' }}</p>
                </div>

                {{-- ============================================== --}}
                {{-- LOGIKA TOMBOL PINTAR (SMART BUTTONS) --}}
                {{-- ============================================== --}}
                @php
                // Cek apakah session ujian ini sudah berstatus completed
                $sessionState = session('public_exam_state_' . $exam->id);
                $isCompleted = $sessionState && $sessionState['status'] === 'completed';
                @endphp

                @if($isCompleted)
                <div class="flex gap-2 relative z-10 mt-4">
                    <a href="{{ route('public.exams.result', $exam) }}"
                        class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-3.5 px-2 rounded-xl text-sm text-center transition-all flex items-center justify-center gap-1.5 shadow-md">
                        <i class="fas fa-chart-bar text-xs"></i> Hasil
                    </a>

                    <form action="{{ route('public.exams.restart', $exam) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit"
                            class="w-full bg-slate-900 hover:bg-indigo-600 text-white font-bold py-3.5 px-2 rounded-xl text-sm text-center transition-all flex items-center justify-center gap-1.5 shadow-md">
                            <i class="fas fa-redo text-xs"></i> Ulangi
                        </button>
                    </form>
                </div>
                @else
                <a href="{{ route('public.exams.show', $exam) }}"
                    class="relative z-10 w-full bg-slate-900 hover:bg-indigo-600 text-white font-bold py-4 px-4 rounded-xl text-sm text-center transition-all flex items-center justify-center gap-2 mt-4 shadow-md">
                    Mulai Ujian <i class="fas fa-arrow-right text-xs"></i>
                </a>
                @endif
                {{-- ============================================== --}}

            </div>
            @endforeach
        </div>
        @endif
    </div>
</x-public-layout>