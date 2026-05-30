<x-public-layout>
    <div class="min-h-screen bg-slate-50 py-12 px-4 sm:px-6 lg:px-8 flex flex-col items-center justify-center">
        <div
            class="w-full max-w-2xl bg-white rounded-[2.5rem] p-8 sm:p-12 shadow-xl border border-slate-100 text-center">
            <h1 class="text-3xl font-black text-slate-800 mb-2">Ujian Selesai!</h1>
            <p class="text-slate-500 font-medium mb-8">Hasil ujian <span class="font-bold text-slate-700">{{
                    $exam->title }}</span></p>

            <div class="bg-slate-900 rounded-[2rem] p-8 text-white mb-10 shadow-lg">
                <p class="text-indigo-200 font-bold uppercase tracking-widest text-sm mb-2">Nilai Akhir Anda</p>
                <div
                    class="text-7xl font-black tracking-tighter mb-2 {{ $score >= 70 ? 'text-emerald-400' : 'text-rose-400' }}">
                    {{ $score }}</div>
            </div>

            <div class="grid grid-cols-3 gap-4 mb-10">
                <div class="bg-emerald-50 rounded-2xl p-4"><i
                        class="fas fa-check-circle text-emerald-500 text-2xl mb-2"></i>
                    <div class="text-2xl font-black text-emerald-700">{{ $correctCount }}</div>
                </div>
                <div class="bg-rose-50 rounded-2xl p-4"><i class="fas fa-times-circle text-rose-500 text-2xl mb-2"></i>
                    <div class="text-2xl font-black text-rose-700">{{ $wrongCount }}</div>
                </div>
                <div class="bg-slate-50 rounded-2xl p-4"><i
                        class="fas fa-minus-circle text-slate-400 text-2xl mb-2"></i>
                    <div class="text-2xl font-black text-slate-700">{{ $unansweredCount }}</div>
                </div>
            </div>

            <a href="{{ route('public.exams.index') }}"
                class="inline-block bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold py-3.5 px-8 rounded-xl transition-all">Kembali
                ke Katalog</a>
        </div>
    </div>
</x-public-layout>