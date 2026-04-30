<x-app-layout>
    <div class="py-10 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">

        <div class="flex justify-between items-end mb-8">
            <div>
                <h2 class="text-3xl font-black text-slate-800 tracking-tight mb-2">Manajemen Kelas</h2>
                <p class="text-slate-500 font-bold text-sm">Kelola daftar kelas dan wali kelas.</p>
            </div>
            <a href="{{ route('admin.classrooms.create') }}"
                class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-black rounded-xl shadow-lg shadow-indigo-200 transition-all flex items-center gap-2">
                <i class="fas fa-plus"></i> Tambah Kelas
            </a>
        </div>

        @if(session('success'))
        <div
            class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl font-bold flex items-center gap-3">
            <i class="fas fa-check-circle text-xl"></i>
            {{ session('success') }}
        </div>
        @endif

        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-200">
                            <th class="py-4 px-6 text-xs font-black text-slate-500 uppercase tracking-widest">Nama Kelas
                            </th>
                            <th class="py-4 px-6 text-xs font-black text-slate-500 uppercase tracking-widest">Tahun
                                Ajaran</th>
                            <th class="py-4 px-6 text-xs font-black text-slate-500 uppercase tracking-widest">Wali Kelas
                            </th>
                            <th
                                class="py-4 px-6 text-xs font-black text-slate-500 uppercase tracking-widest text-center">
                                Jml Siswa</th>
                            <th
                                class="py-4 px-6 text-xs font-black text-slate-500 uppercase tracking-widest text-center">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($classrooms as $classroom)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-4 px-6 font-black text-slate-800">{{ $classroom->name }}</td>
                            <td class="py-4 px-6 font-bold text-slate-500">{{ $classroom->academicYear->name ?? '-' }}
                            </td>
                            <td class="py-4 px-6 font-bold text-slate-600">
                                <i class="fas fa-user-tie text-indigo-400 mr-1"></i>
                                {{ $classroom->homeroomTeacher->name ?? 'Belum Diatur' }}
                                <!-- Sesuaikan -->
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span
                                    class="inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-black {{ $classroom->students_count > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $classroom->students_count }} Siswa
                                </span>
                            </td>
                            <td class="py-4 px-6 text-center space-x-2">
                                {{-- Tombol Atur Siswa --}}
                                <a href="{{ route('admin.classrooms.students', $classroom->id) }}"
                                    class="inline-flex w-8 h-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition tooltip"
                                    title="Atur Siswa">
                                    <i class="fas fa-users-cog"></i>
                                </a>
                                {{-- Tombol Edit --}}
                                <a href="{{ route('admin.classrooms.edit', $classroom->id) }}"
                                    class="inline-flex w-8 h-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white transition tooltip"
                                    title="Edit Kelas">
                                    <i class="fas fa-edit"></i>
                                </a>
                                {{-- Tombol Hapus --}}
                                <form action="{{ route('admin.classrooms.destroy', $classroom->id) }}" method="POST"
                                    class="inline-block"
                                    onsubmit="return confirm('Yakin ingin menghapus kelas ini? Siswa di dalamnya tidak akan terhapus, hanya dikeluarkan dari kelas.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex w-8 h-8 items-center justify-center rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition tooltip"
                                        title="Hapus Kelas">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-500 font-bold">
                                <i class="fas fa-folder-open text-3xl mb-3 text-slate-300 block"></i>
                                Belum ada data kelas.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>