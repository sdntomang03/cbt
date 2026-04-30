<x-app-layout>
    {{-- UBAH DI SINI: Hapus max-w-6xl mx-auto, ganti menjadi w-full --}}
    <div class="py-8 px-4 sm:px-6 lg:px-8 w-full">

        {{-- Header & Tombol Kembali --}}
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.classrooms.index') }}"
                    class="w-10 h-10 flex items-center justify-center rounded-full bg-white shadow-sm border border-slate-200 text-slate-500 hover:text-indigo-600 transition shrink-0">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h2 class="text-3xl font-black text-slate-800 tracking-tight">Data Siswa Kelas</h2>
                    <p class="text-slate-500 font-bold text-sm">Kelas: <span class="text-indigo-600">{{ $classroom->name
                            }}</span> | Wali: {{ $classroom->homeroomTeacher->name ?? '-' }}</p>
                </div>
            </div>

            {{-- Tombol Buka Modal --}}
            <button type="button" onclick="openModal()"
                class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-black rounded-xl shadow-lg shadow-indigo-200 transition-all flex items-center gap-2 shrink-0">
                <i class="fas fa-user-plus"></i> Tambah Siswa Baru
            </button>
        </div>

        {{-- Notifikasi Sukses --}}
        @if(session('success'))
        <div
            class="alert-box mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl shadow-sm flex items-start justify-between transition-all duration-300">
            <div class="flex items-center gap-3 font-bold">
                <i class="fas fa-check-circle text-xl shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
            {{-- Tombol Close --}}
            <button type="button" onclick="this.closest('.alert-box').remove()"
                class="text-emerald-400 hover:text-emerald-600 transition p-1 shrink-0">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        @endif

        {{-- Notifikasi Error --}}
        @if(session('error'))
        <div
            class="alert-box mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl shadow-sm flex items-start justify-between transition-all duration-300">
            <div class="flex items-center gap-3 font-bold">
                <i class="fas fa-exclamation-circle text-xl shrink-0 text-rose-500"></i>
                <span>{{ session('error') }}</span>
            </div>
            {{-- Tombol Close --}}
            <button type="button" onclick="this.closest('.alert-box').remove()"
                class="text-rose-400 hover:text-rose-600 transition p-1 shrink-0">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        @endif

        {{-- Notifikasi Validasi Error (Jika ada input form yang salah) --}}
        @if($errors->any())
        <div
            class="alert-box mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl shadow-sm flex items-start justify-between transition-all duration-300">
            <div class="flex items-start gap-3 font-bold">
                <i class="fas fa-times-circle text-xl shrink-0 text-rose-500 mt-0.5"></i>
                <div>
                    <span class="block mb-1">Terdapat kesalahan pada input Anda:</span>
                    <ul class="list-disc list-inside text-sm font-medium text-rose-600">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            {{-- Tombol Close --}}
            <button type="button" onclick="this.closest('.alert-box').remove()"
                class="text-rose-400 hover:text-rose-600 transition p-1 shrink-0">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        @endif

        {{-- DATA TABLE SISWA YANG ADA DI KELAS --}}
        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="font-black text-slate-800 text-lg">Daftar Anggota Saat Ini (<span class="text-indigo-600">{{
                        $classroom->students->count() }}</span>)</h3>
            </div>
            <div class="overflow-x-auto w-full">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-200">
                            <th
                                class="py-4 px-6 text-xs font-black text-slate-500 uppercase tracking-widest w-16 text-center">
                                No</th>
                            <th class="py-4 px-6 text-xs font-black text-slate-500 uppercase tracking-widest">Nama Siswa
                            </th>
                            <th class="py-4 px-6 text-xs font-black text-slate-500 uppercase tracking-widest">Username /
                                Email</th>
                            <th
                                class="py-4 px-6 text-xs font-black text-slate-500 uppercase tracking-widest text-center w-32">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($classroom->students as $index => $student)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-4 px-6 text-center font-bold text-slate-500">{{ $index + 1 }}</td>
                            <td class="py-4 px-6 font-black text-slate-800">{{ $student->name }}</td>
                            <td class="py-4 px-6 font-bold text-slate-500">{{ $student->username ?? $student->email }}
                            </td>
                            <td class="py-4 px-6 text-center">
                                <form
                                    action="{{ route('admin.classrooms.detach-student', ['classroom' => $classroom->id, 'student' => $student->id]) }}"
                                    method="POST" class="inline-block"
                                    onsubmit="return confirm('Keluarkan siswa ini dari kelas?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex px-3 py-1.5 items-center justify-center rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition font-bold text-xs gap-1.5">
                                        <i class="fas fa-sign-out-alt shrink-0"></i> Keluarkan
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-12 text-center text-slate-500 font-bold">
                                <i class="fas fa-users-slash text-3xl mb-3 text-slate-300 block"></i>
                                Belum ada siswa di kelas ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- MODAL TAMBAH SISWA --}}
    <div id="addStudentModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        {{-- Background Overlay --}}
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity opacity-0" id="modalOverlay"
            onclick="closeModal()"></div>

        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                {{-- Modal Panel: Diubah menjadi max-w-4xl agar tabel siswa di dalam modal bisa lebih lebar --}}
                <div id="modalPanel"
                    class="relative transform overflow-hidden rounded-[2rem] bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-4xl opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95 flex flex-col max-h-[90vh]">

                    <div
                        class="px-6 py-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50 shrink-0">
                        <div>
                            <h3 class="text-xl font-black text-slate-800" id="modal-title">Siswa Belum Mendapat Kelas
                            </h3>
                            <p class="text-sm font-bold text-slate-500 mt-1">Pilih siswa untuk dimasukkan ke kelas {{
                                $classroom->name }}</p>
                        </div>
                        <button type="button" onclick="closeModal()"
                            class="text-slate-400 hover:text-rose-500 transition">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    <form action="{{ route('admin.classrooms.attach-students', $classroom->id) }}" method="POST"
                        class="flex flex-col flex-1 overflow-hidden">
                        @csrf
                        <div class="px-6 py-4 flex-1 overflow-hidden flex flex-col">
                            @if($unassignedStudents->isEmpty())
                            <div class="text-center py-10">
                                <i class="fas fa-check-circle text-4xl text-emerald-400 mb-3 block"></i>
                                <p class="font-bold text-slate-600">Bagus!</p>
                                <p class="text-sm text-slate-500 font-medium">Semua siswa di sekolah ini sudah
                                    mendapatkan kelas.</p>
                            </div>
                            @else
                            <div class="mb-4 shrink-0">
                                <input type="text" id="searchInput" placeholder="Cari nama siswa..."
                                    class="block w-full rounded-xl border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 font-bold text-slate-700 shadow-sm text-sm">
                            </div>
                            {{-- Tabel di dalam modal dibuat flex-1 agar merespons sisa layar --}}
                            <div class="flex-1 overflow-y-auto custom-scrollbar border border-slate-100 rounded-xl">
                                <table class="w-full text-left border-collapse whitespace-nowrap" id="studentTable">
                                    <thead class="sticky top-0 bg-slate-50 border-b border-slate-200 z-10 shadow-sm">
                                        <tr>
                                            <th class="py-3 px-4 text-center w-12">
                                                <input type="checkbox" id="selectAll"
                                                    class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                                            </th>
                                            <th
                                                class="py-3 px-4 text-xs font-black text-slate-500 uppercase tracking-widest">
                                                Nama Lengkap</th>
                                            <th
                                                class="py-3 px-4 text-xs font-black text-slate-500 uppercase tracking-widest">
                                                Username</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach($unassignedStudents as $student)
                                        <tr class="hover:bg-indigo-50/50 transition cursor-pointer student-row"
                                            onclick="toggleCheckbox('checkbox-{{ $student->id }}')">
                                            <td class="py-3 px-4 text-center">
                                                <input type="checkbox" name="student_ids[]" value="{{ $student->id }}"
                                                    id="checkbox-{{ $student->id }}"
                                                    class="student-checkbox rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer pointer-events-none">
                                            </td>
                                            <td class="py-3 px-4 font-black text-slate-800 text-sm student-name">{{
                                                $student->name }}</td>
                                            <td class="py-3 px-4 font-bold text-slate-500 text-xs">{{ $student->username
                                                }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif
                        </div>

                        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 shrink-0">
                            <button type="button" onclick="closeModal()"
                                class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition">Batal</button>
                            @if($unassignedStudents->isNotEmpty())
                            <button type="submit"
                                class="px-5 py-2.5 bg-indigo-600 text-white font-black rounded-xl hover:bg-indigo-700 shadow-md shadow-indigo-200 transition">
                                Simpan Terpilih
                            </button>
                            @endif
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    {{-- Script untuk Modal, Select All, dan Search (Tetap Sama) --}}
    <script>
        const modal = document.getElementById('addStudentModal');
        const overlay = document.getElementById('modalOverlay');
        const panel = document.getElementById('modalPanel');

        // Buka Modal
        function openModal() {
            modal.classList.remove('hidden');
            setTimeout(() => {
                overlay.classList.remove('opacity-0');
                panel.classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
            }, 10);
        }

        // Tutup Modal
        function closeModal() {
            overlay.classList.add('opacity-0');
            panel.classList.add('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        // Toggle Checkbox saat baris di-klik
        function toggleCheckbox(id) {
            const checkbox = document.getElementById(id);
            checkbox.checked = !checkbox.checked;
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Logika Select All
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.student-checkbox');

            if(selectAll) {
                selectAll.addEventListener('change', function() {
                    checkboxes.forEach(cb => {
                        // Hanya centang yang sedang terlihat (tidak kena filter pencarian)
                        if(cb.closest('tr').style.display !== 'none') {
                            cb.checked = this.checked;
                        }
                    });
                });
            }

            // Logika Search/Filter
            const searchInput = document.getElementById('searchInput');
            if(searchInput) {
                searchInput.addEventListener('keyup', function() {
                    const filter = this.value.toLowerCase();
                    const rows = document.querySelectorAll('.student-row');

                    rows.forEach(row => {
                        const name = row.querySelector('.student-name').textContent.toLowerCase();
                        if (name.includes(filter)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }
        });
    </script>
</x-app-layout>