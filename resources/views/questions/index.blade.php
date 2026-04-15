<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 py-2">
            <div class="flex items-center gap-5">
                <a href="{{ route('admin.exams.index') }}"
                    class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-slate-500 hover:text-indigo-600 shadow-sm border border-slate-100 transition-all hover:-translate-x-1">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div
                    class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-600 flex items-center justify-center shadow-lg shadow-indigo-200 rotate-3">
                    <i class="fas fa-layer-group text-white text-2xl"></i>
                </div>
                <div>
                    <span
                        class="px-3 py-1 rounded-full bg-indigo-100 text-indigo-600 text-[10px] font-black uppercase tracking-wider">Manajemen
                        Bank Soal</span>
                    <h2 class="font-black text-2xl md:text-3xl text-slate-800 tracking-tight">{{ $exam->title }}</h2>
                </div>
            </div>

            <div
                class="flex items-center gap-4 bg-white/80 backdrop-blur p-2 pl-6 rounded-full shadow-sm border border-white">
                <div class="flex flex-col items-end pr-2">
                    <span class="text-[10px] uppercase font-bold text-slate-400 leading-none mb-1">Total Soal</span>
                    <span class="text-2xl font-black text-indigo-600 leading-none">{{ $questions->count() }}</span>
                </div>
                <a href="{{ route('admin.exams.questions.create', $exam->id) }}"
                    class="bg-slate-900 hover:bg-black text-white px-8 py-3.5 rounded-full shadow-xl shadow-slate-300 transition-all bounce-active font-bold flex items-center gap-3">
                    <i class="fas fa-plus-circle text-indigo-400"></i> Buat Soal Baru
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="grid gap-6">
            @foreach($questions as $index => $q)
            <div
                class="bg-white rounded-[2.5rem] p-2 pr-8 shadow-sm border border-white hover:border-indigo-100 transition-all group relative">
                <div class="flex flex-col md:flex-row gap-6">
                    <div
                        class="w-full md:w-20 bg-slate-50 rounded-[2rem] flex flex-row md:flex-col items-center py-4 md:py-6 px-6 md:px-0 gap-4 md:gap-2 shrink-0 justify-between md:justify-center">
                        <span class="text-xl md:text-3xl font-black text-slate-300">#{{ $index + 1 }}</span>
                        <div
                            class="w-12 md:w-8 h-1.5 rounded-full {{ $q->type == 'single_choice' ? 'bg-violet-400' : 'bg-amber-400' }}">
                        </div>
                    </div>

                    <div class="flex-1 py-4 md:py-8 px-4 md:px-0">
                        <div class="flex justify-between items-start mb-6">
                            <span
                                class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wide border-2 bg-indigo-50 text-indigo-600 border-indigo-100">
                                {{ str_replace('_', ' ', $q->type) }}
                            </span>
                            <div class="flex gap-2">
                                <a href="{{ route('admin.exams.questions.edit', [$exam->id, $q->id]) }}"
                                    class="w-10 h-10 rounded-full bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white transition flex items-center justify-center shadow-sm">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                                <form action="{{ route('admin.exams.questions.destroy', [$exam->id, $q->id]) }}"
                                    method="POST" onsubmit="return confirm('Hapus soal ini?')">
                                    @csrf @method('DELETE')
                                    <button
                                        class="w-10 h-10 rounded-full bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition flex items-center justify-center shadow-sm">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="prose prose-indigo max-w-none font-bold text-slate-700 leading-relaxed">{!!
                            $q->content !!}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</x-app-layout>