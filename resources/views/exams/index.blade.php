<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-900 leading-tight tracking-tight">
                    {{ __('Manajemen Ujian') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">Kelola daftar ujian dan bank soal Anda.</p>
            </div>

            <div class="flex items-center gap-3">
                <div class="relative hidden md:block">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="fas fa-search text-gray-400"></i>
                    </span>
                    <input type="text"
                        class="w-64 py-2 pl-10 pr-4 bg-white border border-gray-200 rounded-xl text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                        placeholder="Cari ujian...">
                </div>

                <a href="{{ route('admin.exams.create') }}"
                    class="bg-indigo-600 text-white px-5 py-2.5 rounded-xl hover:bg-indigo-700 text-sm font-bold shadow-lg shadow-indigo-200 transition-all active:scale-95 flex items-center gap-2">
                    <i class="fas fa-plus"></i>
                    <span>Ujian Baru</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
                class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl mb-6 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button @click="show = false" class="text-emerald-400 hover:text-emerald-600"><i
                        class="fas fa-times"></i></button>
            </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-[1.5rem] border border-gray-100">

                @if($exams->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="bg-gray-50/50 border-b border-gray-100 text-xs uppercase tracking-wider text-gray-400 font-bold">
                                <th class="px-6 py-4">Informasi Ujian</th>
                                <th class="px-6 py-4">Durasi</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-center">Aksi Utama</th>
                                <th class="px-6 py-4 text-right">Opsi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($exams as $exam)
                            <tr class="hover:bg-gray-50/80 transition-colors group">
                                <td class="px-6 py-4 align-top">
                                    <div class="flex items-start gap-4">
                                        <div
                                            class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 font-bold text-lg shrink-0">
                                            {{ substr($exam->title, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-900 text-base mb-0.5">{{ $exam->title }}
                                            </div>
                                            <div
                                                class="text-xs text-gray-400 font-mono bg-gray-100 inline-block px-1.5 py-0.5 rounded">
                                                {{ $exam->slug }}</div>
                                            <div class="mt-2 text-xs text-gray-500 flex items-center gap-1">
                                                <i class="fas fa-layer-group text-indigo-400"></i>
                                                {{ $exam->questions_count ?? 0 }} Soal
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 align-top">
                                    <div class="flex items-center gap-2 text-sm text-gray-600 font-medium">
                                        <i class="far fa-clock text-gray-400"></i>
                                        {{ $exam->duration_minutes }} Menit
                                    </div>
                                </td>
                                <td class="px-6 py-4 align-top">
                                    @php
                                    $statusColor = match($exam->status->value) {
                                    'published' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                    'draft' => 'bg-gray-100 text-gray-600 border-gray-200',
                                    'archived' => 'bg-amber-100 text-amber-700 border-amber-200',
                                    default => 'bg-gray-100 text-gray-600'
                                    };
                                    $dotColor = match($exam->status->value) {
                                    'published' => 'bg-emerald-500',
                                    'draft' => 'bg-gray-400',
                                    'archived' => 'bg-amber-500',
                                    default => 'bg-gray-400'
                                    };
                                    @endphp
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border {{ $statusColor }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $dotColor }}"></span>
                                        {{ ucfirst($exam->status->value) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 align-middle text-center">
                                    <a href="{{ route('admin.ajax.questions.index', $exam->id) }}"
                                        class="inline-flex items-center gap-2 bg-indigo-50 text-indigo-600 border border-indigo-200 hover:bg-indigo-600 hover:text-white px-4 py-2 rounded-lg text-sm font-bold transition-all shadow-sm hover:shadow-md">
                                        <i class="fas fa-edit"></i>
                                        Atur Soal
                                    </a>
                                </td>
                                <td class="px-6 py-4 align-middle text-right">
                                    <div
                                        class="flex items-center justify-end gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                                        <a href="{{ route('admin.exams.edit', $exam) }}"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-blue-50 text-gray-400 hover:text-blue-600 transition-colors"
                                            title="Edit Properti Ujian">
                                            <i class="fas fa-cog"></i>
                                        </a>

                                        <form action="{{ route('admin.exams.destroy', $exam) }}" method="POST"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus ujian ini beserta seluruh soalnya?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600 transition-colors"
                                                title="Hapus Ujian">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($exams->hasPages())
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-100">
                    {{ $exams->links() }}
                </div>
                @endif

                @else
                <div class="text-center py-16">
                    <div class="bg-gray-50 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-clipboard-list text-4xl text-gray-300"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Belum ada Ujian</h3>
                    <p class="text-gray-500 mt-2 mb-6 max-w-sm mx-auto">Mulai dengan membuat ujian baru untuk kemudian
                        menambahkan soal-soal di dalamnya.</p>
                    <a href="{{ route('admin.exams.create') }}" class="text-indigo-600 font-bold hover:underline">
                        + Buat Ujian Pertama
                    </a>
                </div>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>
