<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-black text-2xl text-slate-800">{{ $exam->title }}</h2>
                <p class="text-sm text-slate-500">Kelola Daftar Soal</p>
            </div>
            {{-- Tombol Link ke Halaman Create --}}
            <a href="{{ route('admin.exams.questions.create', $exam->id) }}"
                class="bg-indigo-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-indigo-700">
                <i class="fas fa-plus mr-2"></i> Buat Soal Baru
            </a>
        </div>
    </x-slot>

    <div class="py-10 max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if(session('success'))
        <div class="bg-emerald-100 text-emerald-700 p-4 rounded-xl mb-6 font-bold">
            {{ session('success') }}
        </div>
        @endif

        <div class="space-y-6">
            @forelse($questions as $index => $q)
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-200 flex gap-6">
                <div
                    class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center font-black text-2xl text-slate-400 shrink-0">
                    {{ $index + 1 }}
                </div>
                <div class="flex-1">
                    <div class="flex justify-between mb-4">
                        <span class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-full text-xs font-bold uppercase">{{
                            $q->type }}</span>

                        {{-- Tombol Edit & Delete --}}
                        <div class="flex gap-2">
                            <a href="{{ route('admin.exams.questions.edit', [$exam->id, $q->id]) }}"
                                class="w-10 h-10 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center hover:bg-amber-500 hover:text-white transition">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.exams.questions.destroy', [$exam->id, $q->id]) }}"
                                method="POST" onsubmit="return confirm('Hapus soal ini?');">
                                @csrf @method('DELETE')
                                <button
                                    class="w-10 h-10 bg-rose-50 text-rose-500 rounded-full flex items-center justify-center hover:bg-rose-500 hover:text-white transition">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="prose max-w-none text-slate-700 font-medium">{!! $q->content !!}</div>
                </div>
            </div>
            @empty
            <div class="bg-white p-12 text-center rounded-[2rem] border border-dashed border-slate-300">
                <h3 class="text-xl font-bold text-slate-500">Belum ada soal, silakan buat baru.</h3>
            </div>
            @endforelse
        </div>
    </div>
</x-app-layout>