<x-app-layout>
    <div class="py-10 min-h-screen font-sans bg-gray-50" x-data="{
            search: '',
            searchRight: '',
            selectAllLeft: false,
            selectAllRight: false,

            // Logika Panel Kiri (Tambah Siswa)
            get visibleLeft() {
                return Array.from(document.querySelectorAll('.checkbox-left')).filter(cb => cb.closest('label').style.display !== 'none');
            },
            toggleLeft() {
                this.visibleLeft.forEach(cb => cb.checked = this.selectAllLeft);
            },
            checkLeft() {
                const visible = this.visibleLeft;
                this.selectAllLeft = visible.length > 0 && visible.every(cb => cb.checked);
            },

            // Logika Panel Kanan (Hapus Siswa)
            get visibleRight() {
                return Array.from(document.querySelectorAll('.checkbox-right')).filter(cb => cb.closest('.item-right').style.display !== 'none');
            },
            toggleRight() {
                this.visibleRight.forEach(cb => cb.checked = this.selectAllRight);
            },
            checkRight() {
                const visible = this.visibleRight;
                this.selectAllRight = visible.length > 0 && visible.every(cb => cb.checked);
            },

            // Konfirmasi Hapus Massal
            confirmMassRemove(e) {
                const checkedCount = document.querySelectorAll('.checkbox-right:checked').length;
                if(checkedCount === 0) {
                    e.preventDefault();
                    Swal.fire({ icon: 'info', title: 'Pilih Siswa', text: 'Pilih minimal satu siswa untuk dikeluarkan.' });
                    return;
                }

                e.preventDefault();
                Swal.fire({
                    title: 'Keluarkan ' + checkedCount + ' Siswa?',
                    text: 'Siswa yang dipilih akan dihapus dari sesi ini.',
                    icon: 'warning',
                    background: '#1e293b', color: '#fff',
                    showCancelButton: true, confirmButtonColor: '#f43f5e', cancelButtonColor: '#475569',
                    confirmButtonText: 'Ya, Keluarkan', cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) e.target.submit();
                });
            }
         }">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-8 gap-4">
                <div>
                    <a href="{{ route('admin.exam-sessions.index') }}"
                        class="text-sm font-bold text-gray-400 hover:text-indigo-600 transition mb-2 inline-block">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Jadwal
                    </a>
                    <h2 class="text-3xl font-black text-gray-800">{{ $examSession->title ?? $examSession->session_name
                        }}</h2>
                    <p class="text-indigo-500 font-bold">Kelola Peserta Ujian</p>
                </div>
                <div class="text-left md:text-right bg-white px-6 py-3 rounded-2xl shadow-sm border border-gray-100">
                    <span class="block text-xs font-black text-gray-400 uppercase">Total Peserta Terdaftar</span>
                    <span class="text-3xl font-black text-indigo-600">{{ $enrolledStudents->count() }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                <div
                    class="bg-white rounded-[2.5rem] p-8 shadow-xl shadow-gray-100 border border-white flex flex-col h-[650px]">

                    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-6 gap-3">
                        <h3 class="font-black text-xl text-gray-800 shrink-0">Siswa Tersedia</h3>

                        <div class="flex flex-wrap items-center gap-2 w-full xl:w-auto justify-end">
                            @if(auth()->user()->hasRole('admin'))
                            <form method="GET"
                                action="{{ route('admin.exam-sessions.students.index', $examSession->id) }}">
                                <select name="school_id" onchange="this.form.submit()"
                                    class="bg-gray-50 border-none rounded-xl text-sm font-bold focus:ring-2 focus:ring-indigo-500 py-2.5 pl-4 pr-8 text-gray-600 cursor-pointer">
                                    <option value="">Semua Sekolah</option>
                                    @foreach($schools as $school)
                                    <option value="{{ $school->id }}" {{ request('school_id')==$school->id ? 'selected'
                                        : '' }}>{{ $school->name }}</option>
                                    @endforeach
                                </select>
                            </form>
                            @endif

                            <div class="relative w-full sm:w-auto">
                                <i class="fas fa-search absolute left-4 top-3.5 text-gray-400 text-sm"></i>
                                <input type="text" x-model="search" @input="checkLeft" placeholder="Cari nama..."
                                    class="pl-10 pr-4 py-2.5 bg-gray-50 border-none rounded-xl text-sm font-bold focus:ring-2 focus:ring-indigo-500 w-full sm:w-48 transition-all text-gray-700">
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('admin.exam-sessions.students.store', $examSession->id) }}" method="POST"
                        class="flex flex-col flex-1 overflow-hidden">
                        @csrf

                        @if($availableStudents->count() > 0)
                        <div class="mb-3 pb-3 border-b border-gray-100 flex items-center justify-between px-2 shrink-0">
                            <label class="flex items-center cursor-pointer group">
                                <input type="checkbox" x-model="selectAllLeft" @change="toggleLeft"
                                    class="w-5 h-5 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                                <span class="ml-3 font-black text-sm text-gray-700 group-hover:text-indigo-600">Pilih
                                    Semua Filter</span>
                            </label>
                            <span class="text-xs font-bold text-gray-400 bg-gray-50 px-2 py-1 rounded"
                                x-text="visibleLeft.length + ' Muncul'"></span>
                        </div>
                        @endif

                        <div class="flex-1 overflow-y-auto custom-scrollbar pr-2 space-y-2">
                            @forelse($availableStudents as $student)
                            <label
                                x-show="search === '' || '{{ strtolower($student->name) }}'.includes(search.toLowerCase())"
                                class="flex items-center p-4 rounded-2xl border border-gray-100 cursor-pointer hover:bg-indigo-50 hover:border-indigo-200 transition-all group">
                                <input type="checkbox" name="student_ids[]" value="{{ $student->id }}"
                                    @change="checkLeft"
                                    class="checkbox-left w-5 h-5 text-indigo-600 rounded-lg border-gray-300 focus:ring-indigo-500">
                                <div class="ml-4 flex-1">
                                    <div class="flex items-center justify-between">
                                        <p class="font-bold text-gray-700 group-hover:text-indigo-700">{{ $student->name
                                            }}</p>
                                        @if(auth()->user()->hasRole('admin'))
                                        <span
                                            class="text-[9px] font-black uppercase tracking-wider bg-gray-100 text-gray-500 px-2 py-1 rounded-md">{{
                                            $student->school->name ?? 'Pusat' }}</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-400 font-semibold mt-0.5">{{ $student->username }}</p>
                                </div>
                            </label>
                            @empty
                            <div class="flex flex-col items-center justify-center h-full text-center">
                                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4"><i
                                        class="fas fa-check-circle text-2xl text-emerald-400"></i></div>
                                <p class="text-gray-500 font-bold">Semua siswa sudah terdaftar.</p>
                            </div>
                            @endforelse
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-100 shrink-0">
                            <button type="submit"
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-4 rounded-xl font-black shadow-lg shadow-indigo-200 transition-all active:scale-95 flex justify-center items-center gap-2">
                                <i class="fas fa-user-plus"></i> Tambahkan Terpilih
                            </button>
                        </div>
                    </form>
                </div>


                <div
                    class="bg-slate-900 rounded-[2.5rem] p-8 shadow-2xl relative overflow-hidden flex flex-col h-[650px] border-4 border-slate-800">

                    <div class="flex justify-between items-center mb-6 relative z-10">
                        <div>
                            <h3 class="font-black text-xl text-white">Peserta Terdaftar</h3>
                            <p class="text-xs text-slate-400 mt-1">Siswa yang berhak mengikuti ujian</p>
                        </div>

                        @if($enrolledStudents->count() > 0)
                        <div class="relative w-40 sm:w-48">
                            <i class="fas fa-search absolute left-3 top-2 text-slate-500 text-sm"></i>
                            <input type="text" x-model="searchRight" @input="checkRight" placeholder="Cari terdaftar..."
                                class="pl-9 pr-3 py-1.5 bg-slate-800 border-none rounded-lg text-sm font-bold text-white focus:ring-2 focus:ring-rose-500 w-full transition-all placeholder-slate-500">
                        </div>
                        @endif
                    </div>

                    <form action="{{ route('admin.exam-sessions.students.destroyMass', $examSession->id) }}"
                        method="POST" @submit="confirmMassRemove"
                        class="flex flex-col flex-1 overflow-hidden relative z-10">
                        @csrf
                        @method('DELETE')

                        @if($enrolledStudents->count() > 0)
                        <div
                            class="mb-3 pb-3 border-b border-slate-700 flex items-center justify-between px-2 shrink-0">
                            <label class="flex items-center cursor-pointer group">
                                <input type="checkbox" x-model="selectAllRight" @change="toggleRight"
                                    class="w-5 h-5 text-rose-500 rounded border-slate-600 focus:ring-rose-500 bg-slate-800">
                                <span
                                    class="ml-3 font-black text-sm text-slate-300 group-hover:text-rose-400 transition-colors">Pilih
                                    Semua Filter</span>
                            </label>
                        </div>
                        @endif

                        <div class="flex-1 overflow-y-auto custom-scrollbar pr-2 space-y-3">
                            @forelse($enrolledStudents as $student)
                            <div x-show="searchRight === '' || '{{ strtolower($student->name) }}'.includes(searchRight.toLowerCase())"
                                class="item-right flex items-center justify-between p-3 bg-slate-800 border border-slate-700 rounded-2xl hover:bg-slate-700/80 transition-all group">

                                <div class="flex items-center gap-4 flex-1 overflow-hidden">
                                    <input type="checkbox" name="student_ids[]" value="{{ $student->id }}"
                                        @change="checkRight"
                                        class="checkbox-right w-5 h-5 text-rose-500 rounded-md border-slate-600 focus:ring-rose-500 bg-slate-900 shrink-0 ml-1">

                                    <div
                                        class="w-10 h-10 rounded-xl bg-indigo-500/20 text-indigo-400 flex flex-shrink-0 items-center justify-center font-black text-base border border-indigo-500/30">
                                        {{ substr($student->name, 0, 1) }}
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <p class="font-bold text-white text-sm truncate">{{ $student->name }}</p>
                                            @if(auth()->user()->hasRole('admin'))
                                            <span
                                                class="text-[9px] bg-slate-700 text-slate-400 px-2 py-0.5 rounded truncate max-w-[80px]">{{
                                                $student->school->name ?? 'Pusat' }}</span>
                                            @endif
                                        </div>
                                        <p class="text-[10px] text-slate-400 font-medium">{{ $student->username }}</p>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div
                                class="flex flex-col items-center justify-center h-full text-center border-2 border-dashed border-slate-700 rounded-3xl p-6">
                                <div class="w-16 h-16 bg-slate-800 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-user-slash text-2xl text-slate-500"></i></div>
                                <p class="text-slate-400 font-bold">Belum ada peserta terdaftar.</p>
                            </div>
                            @endforelse
                        </div>

                        @if($enrolledStudents->count() > 0)
                        <div class="mt-4 pt-4 border-t border-slate-700 shrink-0">
                            <button type="submit"
                                class="w-full bg-rose-600 hover:bg-rose-500 text-white py-4 rounded-xl font-black shadow-lg shadow-rose-900/50 transition-all active:scale-95 flex justify-center items-center gap-2">
                                <i class="fas fa-trash-alt"></i> Keluarkan Terpilih
                            </button>
                        </div>
                        @endif
                    </form>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>