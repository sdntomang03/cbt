<x-app-layout>
    <div class="py-10 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
            <div>
                <h2 class="text-3xl font-black text-slate-800">Manajemen Ujian Matematika</h2>
                <p class="text-slate-500 font-bold mt-1">Pantau ujian yang telah Anda buat untuk siswa.</p>
            </div>
            <a href="{{ route('admin.math.create') }}"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-xl font-bold shadow-lg transition-transform hover:-translate-y-1 flex items-center gap-2">
                <i class="fas fa-plus"></i> Generate Ujian Baru
            </a>
        </div>

        {{-- Alert Success --}}
        @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 transform translate-x-0"
            x-transition:leave-end="opacity-0 transform translate-x-8"
            class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-xl shadow-sm flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <i class="fas fa-check-circle text-emerald-500 text-xl"></i>
                <p class="text-emerald-700 font-bold">{{ session('success') }}</p>
            </div>
            <button @click="show = false"
                class="text-emerald-400 hover:text-emerald-700 transition w-8 h-8 rounded-full flex items-center justify-center hover:bg-emerald-100">
                <i class="fas fa-times"></i>
            </button>
        </div>
        @endif

        {{-- Alert Error --}}
        @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 7000)"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 transform translate-x-0"
            x-transition:leave-end="opacity-0 transform translate-x-8"
            class="bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r-xl shadow-sm flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <i class="fas fa-exclamation-circle text-rose-500 text-xl"></i>
                <p class="text-rose-700 font-bold">{{ session('error') }}</p>
            </div>
            <button @click="show = false"
                class="text-rose-400 hover:text-rose-700 transition w-8 h-8 rounded-full flex items-center justify-center hover:bg-rose-100">
                <i class="fas fa-times"></i>
            </button>
        </div>
        @endif

        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider font-black border-b border-slate-100">
                            <th class="p-5">Tanggal</th>
                            <th class="p-5">Judul Ujian</th>
                            <th class="p-5">Tipe Operasi</th>
                            <th class="p-5 text-center">Soal & Waktu</th>
                            <th class="p-5 text-center">Peserta</th>
                            <th class="p-5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($exams as $exam)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="p-5">
                                <span class="block font-bold text-slate-700">{{ $exam->created_at->format('d M Y')
                                    }}</span>
                                <span class="text-xs text-slate-400 font-bold">{{ $exam->created_at->format('H:i') }}
                                    WIB</span>
                            </td>

                            <td class="p-5">
                                <span class="block font-black text-lg text-indigo-700">{{ $exam->title }}</span>
                            </td>

                            <td class="p-5">
                                <div class="flex gap-1">
                                    @php $types = is_array($exam->types) ? $exam->types : json_decode($exam->types,
                                    true) ?? []; @endphp
                                    @if(in_array('addition', $types)) <span
                                        class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center font-black text-sm"
                                        title="Penjumlahan">+</span> @endif
                                    @if(in_array('subtraction', $types)) <span
                                        class="w-7 h-7 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center font-black text-sm"
                                        title="Pengurangan">-</span> @endif
                                    @if(in_array('multiplication', $types)) <span
                                        class="w-7 h-7 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center font-black text-sm"
                                        title="Perkalian">x</span> @endif
                                    @if(in_array('division', $types)) <span
                                        class="w-7 h-7 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center font-black text-sm"
                                        title="Pembagian">:</span> @endif
                                </div>
                            </td>

                            <td class="p-5 text-center">
                                <span class="block font-black text-slate-700">{{ $exam->total_questions }} Soal</span>
                                <span class="text-xs font-bold text-slate-400">{{ $exam->duration_minutes }}
                                    Menit</span>
                            </td>

                            <td class="p-5 text-center">
                                <span class="bg-indigo-50 text-indigo-600 px-3 py-1.5 rounded-xl font-black text-sm">
                                    <i class="fas fa-users mr-1"></i> {{ $exam->exam_users_count }} Siswa
                                </span>
                            </td>

                            <td class="p-5">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.math.show', $exam->id) }}"
                                        class="bg-slate-100 hover:bg-indigo-500 text-slate-500 hover:text-white px-4 py-2 rounded-xl font-bold text-xs transition-all shadow-sm">
                                        <i class="fas fa-list-ol mr-1"></i> Rekap
                                    </a>

                                    <form action="{{ route('admin.math.destroy', $exam->id) }}" method="POST"
                                        onsubmit="return confirm('AWAS! Yakin ingin menghapus ujian ini? Seluruh data soal dan nilai siswa terkait akan terhapus permanen.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="bg-rose-50 hover:bg-rose-500 text-rose-500 hover:text-white w-8 h-8 rounded-xl flex items-center justify-center transition-all shadow-sm"
                                            title="Hapus Ujian">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="p-10 text-center text-slate-400 font-bold">
                                <i class="fas fa-folder-open text-4xl mb-3 block opacity-30"></i>
                                Belum ada data ujian matematika.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>