<x-app-layout>
    <div class="py-10 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">

        {{-- Header & Tombol Tambah --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-8 gap-4">
            <div>
                <h2 class="text-3xl font-black text-slate-800 tracking-tight mb-2">Manajemen Kelas</h2>
                <p class="text-slate-500 font-bold text-sm">Kelola daftar kelas, wali kelas, dan tahun ajaran.</p>
            </div>
            @can('manage classrooms')
            <button @click="$store.classModule.newClassroom()"
                class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-black rounded-xl shadow-lg shadow-indigo-200 transition-all flex items-center gap-2 active:scale-95">
                <i class="fas fa-plus"></i> Tambah Kelas
            </button>
            @endcan
        </div>

        {{-- Alert Notifikasi --}}
        @if(session('success'))
        <div
            class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl font-bold flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <i class="fas fa-check-circle text-xl"></i>
                {{ session('success') }}
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-emerald-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        @endif

        {{-- Menampilkan Error Validasi (jika ada) --}}
        @if($errors->any())
        <div
            class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl font-bold flex items-start justify-between shadow-sm">
            <div class="flex items-start gap-3">
                <i class="fas fa-exclamation-circle text-xl mt-0.5"></i>
                <div>
                    <p class="mb-1">Gagal menyimpan data:</p>
                    <ul class="list-disc list-inside text-sm font-medium">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-400 hover:text-rose-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        @endif

        {{-- Tabel Data Kelas --}}
        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
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
                            <td class="py-4 px-6 font-black text-slate-800 text-base">{{ $classroom->name }}</td>
                            <td class="py-4 px-6 font-bold text-slate-500">{{ $classroom->academicYear->name ?? '-' }}
                            </td>
                            <td class="py-4 px-6 font-bold text-slate-600">
                                @if($classroom->homeroomTeacher)
                                <div class="flex items-center gap-2">
                                    <div
                                        class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                    {{ $classroom->homeroomTeacher->name }}
                                </div>
                                @else
                                <span class="text-slate-400 italic">Belum Diatur</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span
                                    class="inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-black {{ $classroom->students_count > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $classroom->students_count }} Siswa
                                </span>
                            </td>
                            <td class="py-4 px-6 text-center space-x-2">
                                {{-- Tombol Atur Siswa (Tetap ke halaman lain karena kompleksitasnya tinggi) --}}
                                <a href="{{ route('admin.classrooms.students', $classroom->id) }}"
                                    class="inline-flex w-8 h-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition tooltip"
                                    title="Atur Siswa">
                                    <i class="fas fa-users-cog"></i>
                                </a>
                                @can('manage classrooms')
                                {{-- Tombol Edit (Buka Modal) --}}
                                <button @click="$store.classModule.editClassroom({{ $classroom->toJson() }})"
                                    class="inline-flex w-8 h-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white transition tooltip"
                                    title="Edit Kelas">
                                    <i class="fas fa-edit"></i>
                                </button>

                                {{-- Tombol Hapus --}}
                                <form action="{{ route('admin.classrooms.destroy', $classroom->id) }}" method="POST"
                                    class="inline-block"
                                    onsubmit="return confirm('Yakin ingin menghapus kelas ini? Siswa di dalamnya tidak akan terhapus, hanya dikeluarkan dari kelas.');">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex w-8 h-8 items-center justify-center rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition tooltip"
                                        title="Hapus Kelas">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-16 text-center text-slate-500 font-bold">
                                <i class="fas fa-folder-open text-4xl mb-4 text-slate-300 block"></i>
                                Belum ada data kelas.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- MODAL CRUD KELAS --}}
    <div x-data x-show="$store.classModule.openModal" class="fixed inset-0 z-[100] overflow-y-auto" x-cloak
        x-transition>
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            {{-- Background Overlay --}}
            <div @click="$store.classModule.openModal = false"
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

            {{-- Panel Modal --}}
            <div
                class="inline-block bg-white rounded-[2.5rem] overflow-hidden shadow-2xl transform transition-all w-full max-w-lg z-[110] border border-slate-100 text-left align-middle">
                <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <h3 class="text-xl font-black text-slate-800"
                        x-text="$store.classModule.isEdit ? 'Update Data Kelas' : 'Buat Kelas Baru'"></h3>
                    <button @click="$store.classModule.openModal = false"
                        class="text-slate-300 hover:text-rose-500 transition">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form :action="$store.classModule.actionUrl" method="POST" class="p-8 space-y-6">
                    @csrf
                    {{-- Method spoofing untuk Edit --}}
                    <template x-if="$store.classModule.isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    {{-- Nama Kelas --}}
                    <div>
                        <label
                            class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Nama
                            Kelas <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" x-model="$store.classModule.formData.name" required
                            placeholder="Contoh: Kelas X IPA 1"
                            class="w-full rounded-2xl border-slate-200 focus:ring-indigo-500 font-bold text-slate-700 py-3.5 px-4 shadow-sm">
                    </div>

                    {{-- Tahun Ajaran --}}
                    <div>
                        <label
                            class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Tahun
                            Ajaran</label>
                        <select name="academic_year_id" x-model="$store.classModule.formData.academic_year_id"
                            class="w-full rounded-2xl border-slate-200 focus:ring-indigo-500 font-bold text-slate-700 py-3.5 px-4 bg-slate-50 shadow-sm">
                            <option value="">-- Pilih Tahun Ajaran --</option>
                            @isset($academicYears)
                            @foreach($academicYears as $year)
                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                            @endforeach
                            @endisset
                        </select>
                    </div>

                    <div>
                        <label
                            class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Level
                            / Tingkat <span class="text-rose-500">*</span></label>
                        <select name="level_id" x-model="$store.classModule.formData.level_id" required
                            class="w-full rounded-2xl border-slate-200 focus:ring-indigo-500 font-bold text-slate-700 py-3.5 px-4 bg-slate-50 shadow-sm">
                            <option value="">-- Pilih Level --</option>
                            @isset($levels)
                            @foreach($levels as $level)
                            <option value="{{ $level->id }}">{{ $level->name }}</option>
                            @endforeach
                            @endisset
                        </select>
                    </div>

                    {{-- Wali Kelas --}}
                    <div>
                        <label
                            class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Wali
                            Kelas</label>
                        <select name="homeroom_teacher_id" x-model="$store.classModule.formData.homeroom_teacher_id"
                            class="w-full rounded-2xl border-slate-200 focus:ring-indigo-500 font-bold text-slate-700 py-3.5 px-4 bg-slate-50 shadow-sm">
                            <option value="">-- Tanpa Wali Kelas --</option>
                            @isset($teachers)
                            @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                            @endforeach
                            @endisset
                        </select>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="pt-8 flex justify-end gap-3 border-t border-slate-50">
                        <button type="button" @click="$store.classModule.openModal = false"
                            class="px-6 py-3 text-slate-400 font-black rounded-2xl hover:bg-slate-50 transition">Batal</button>
                        <button type="submit"
                            class="px-8 py-3 bg-indigo-600 text-white font-black rounded-2xl hover:bg-indigo-700 shadow-xl shadow-indigo-100 transition-all active:scale-95">
                            Simpan Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- SCRIPT ALPINE GLOBAL STORE UNTUK KELAS --}}
    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('classModule', {
                openModal: false,
                isEdit: false,
                actionUrl: '',
                formData: {
                    name: '',
                    academic_year_id: '',
                    level_id: '',
                    homeroom_teacher_id: ''
                },
                newClassroom() {
                    this.isEdit = false;
                    // Pastikan route store ini sudah ada di web.php
                    this.actionUrl = '{{ route('admin.classrooms.store') }}';
                    this.formData = {
                        name: '',
                        academic_year_id: '',
                        level_id: '',
                        homeroom_teacher_id: ''
                    };
                    this.openModal = true;
                },
                editClassroom(classroom) {
                    this.isEdit = true;
                    // Pastikan route update ini sesuai dengan struktur web.php mu
                    this.actionUrl = `/admin/classrooms/${classroom.id}`;
                    this.formData = {
                        name: classroom.name,
                        // Gunakan fallback string kosong agar select option reset dengan benar jika data null
                        academic_year_id: classroom.academic_year_id || '',
                        level_id: classroom.level_id || '',
                        homeroom_teacher_id: classroom.homeroom_teacher_id || ''
                    };
                    this.openModal = true;
                }
            });
        });
    </script>
    @endpush
</x-app-layout>