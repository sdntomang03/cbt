<x-app-layout>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8fafc;
        }

        [x-cloak] {
            display: none !important;
        }

        .transition-all {
            transition: all 0.3s ease;
        }

        /* Custom Scrollbar untuk Dropdown Multi-Select */
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
    </style>

    <div class="min-h-screen py-10" x-data="proctorMonitor()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div
                class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4 bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm">

                <div class="flex items-center gap-5">
                    <a href="{{ route('admin.exam-sessions.index') }}"
                        class="w-12 h-12 rounded-full bg-slate-50 hover:bg-slate-100 text-slate-500 flex items-center justify-center transition-colors">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <h2 class="font-black text-2xl text-slate-800 tracking-tight">Ruang Monitor</h2>
                            <span
                                :class="isAutoUpdate ? 'bg-emerald-100 text-emerald-600 animate-pulse' : 'bg-slate-100 text-slate-400'"
                                class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider transition-all flex items-center">
                                <i class="fas fa-circle text-[8px] mr-1.5"></i> <span
                                    x-text="isAutoUpdate ? 'Live Updating' : 'Paused'"></span>
                            </span>
                        </div>
                        <p class="text-indigo-500 font-bold text-sm">{{ $examSession->session_name }} &bull; {{
                            $examSession->exam->title ?? 'Ujian' }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 w-full md:w-auto justify-end">

                    @if(auth()->user()->hasRole('admin'))
                    <div class="relative w-full sm:w-auto" x-data="{ openSchool: false }">
                        <button @click="openSchool = !openSchool"
                            class="w-full sm:w-48 bg-slate-50 border-none rounded-xl text-sm font-bold focus:ring-2 focus:ring-indigo-500 py-3 px-4 text-slate-600 flex justify-between items-center shadow-sm">
                            <span class="truncate pr-2"
                                x-text="selectedSchools.length > 0 ? selectedSchools.length + ' Sekolah Dipilih' : 'Semua Sekolah'"></span>
                            <i class="fas fa-chevron-down text-slate-400 text-xs transition-transform"
                                :class="openSchool ? 'rotate-180' : ''"></i>
                        </button>

                        <div x-show="openSchool" @click.outside="openSchool = false" x-transition x-cloak
                            class="absolute z-50 mt-2 w-64 bg-white border border-slate-200 rounded-xl shadow-xl max-h-64 overflow-y-auto custom-scrollbar right-0 sm:left-0 sm:right-auto origin-top">
                            <div class="p-2 space-y-1">
                                <label
                                    class="flex items-center gap-3 p-2.5 hover:bg-slate-50 rounded-lg cursor-pointer transition">
                                    <input type="checkbox" :checked="selectedSchools.length === 0"
                                        @change="selectedSchools = []"
                                        class="w-4 h-4 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500 cursor-pointer">
                                    <span class="text-sm font-bold text-slate-700">Semua Sekolah</span>
                                </label>

                                <div class="border-t border-slate-100 my-1"></div>

                                @foreach($schools as $school)
                                <label
                                    class="flex items-center gap-3 p-2.5 hover:bg-slate-50 rounded-lg cursor-pointer transition group">
                                    <input type="checkbox" value="{{ $school->id }}" x-model="selectedSchools"
                                        class="w-4 h-4 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500 cursor-pointer">
                                    <span class="text-sm font-bold text-slate-600 group-hover:text-indigo-600">{{
                                        $school->name }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="relative w-full sm:w-56">
                        <i class="fas fa-search absolute left-4 top-3.5 text-slate-400"></i>
                        <input type="text" x-model="search" placeholder="Cari nama siswa..."
                            class="w-full pl-11 pr-4 py-3 bg-slate-50 border-none rounded-xl text-sm font-bold focus:ring-2 focus:ring-indigo-500 text-slate-700 shadow-sm">
                    </div>

                    <button @click="toggleAutoUpdate"
                        :class="isAutoUpdate ? 'bg-indigo-600 text-white shadow-indigo-200' : 'bg-slate-200 text-slate-600 shadow-slate-200'"
                        class="px-5 py-3 rounded-xl font-bold transition-all shadow-lg flex items-center justify-center gap-2 whitespace-nowrap active:scale-95 w-full sm:w-auto">
                        <i class="fas" :class="isAutoUpdate ? 'fa-sync fa-spin' : 'fa-play'"></i>
                        <span x-text="isAutoUpdate ? 'Auto On' : 'Auto Off'"></span>
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-[2rem] border border-slate-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="bg-slate-50/50 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                <th class="p-4 pl-6 w-16 text-center">No</th>
                                <th class="p-4">Nama Peserta & Sekolah</th>
                                <th class="p-4">Status</th>
                                <th class="p-4 text-center">Pelanggaran</th>
                                <th class="p-4 text-center">Skor Akhir</th>
                                <th class="p-4 pr-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm font-bold text-slate-700 relative">
                            <tr x-show="filteredStudents.length === 0" x-cloak>
                                <td colspan="6" class="p-10 text-center text-slate-400">
                                    <i class="fas fa-inbox text-3xl mb-3 opacity-30"></i>
                                    <p>Tidak ada siswa yang sesuai dengan filter.</p>
                                </td>
                            </tr>

                            <template x-for="(student, index) in filteredStudents" :key="student.id">
                                <tr :class="student.pivot.is_locked ? 'bg-rose-50/50' : 'hover:bg-slate-50/50'"
                                    class="transition-colors">
                                    <td class="p-4 pl-6 text-center text-slate-400" x-text="index + 1"></td>
                                    <td class="p-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm font-black shadow-inner shrink-0"
                                                :class="student.pivot.is_locked ? 'bg-rose-500 text-white' : (student.pivot.status === 'completed' ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-500')">
                                                <i x-show="student.pivot.is_locked" class="fas fa-lock text-[12px]"></i>
                                                <span x-show="!student.pivot.is_locked"
                                                    x-text="student.name.charAt(0)"></span>
                                            </div>
                                            <div class="flex flex-col">
                                                <span
                                                    :class="student.pivot.is_locked ? 'text-rose-600' : 'text-slate-800'"
                                                    x-text="student.name"></span>
                                                @if(auth()->user()->hasRole('admin'))
                                                <span
                                                    class="text-[10px] font-black uppercase tracking-wider text-slate-400 mt-0.5"
                                                    x-text="student.school ? student.school.name : 'Pusat'"></span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <span x-show="student.pivot.is_locked"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-rose-500 text-white rounded-lg text-[10px] font-black uppercase">
                                            <i class="fas fa-ban"></i> Terkunci
                                        </span>
                                        <span
                                            x-show="!student.pivot.is_locked && student.pivot.status === 'not_started'"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 text-slate-500 rounded-lg text-[10px] font-black uppercase">
                                            Belum Mulai
                                        </span>
                                        <span x-show="!student.pivot.is_locked && student.pivot.status === 'ongoing'"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-indigo-500 text-white rounded-lg text-[10px] font-black uppercase animate-pulse">
                                            <i class="fas fa-spinner fa-spin"></i> Mengerjakan
                                        </span>
                                        <span x-show="!student.pivot.is_locked && student.pivot.status === 'completed'"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-100 text-emerald-600 rounded-lg text-[10px] font-black uppercase">
                                            <i class="fas fa-check-double"></i> Selesai
                                        </span>
                                    </td>
                                    <td class="p-4 text-center">
                                        <span
                                            :class="student.pivot.violation_count > 0 ? 'bg-amber-100 text-amber-700' : 'text-slate-400'"
                                            class="inline-flex items-center justify-center min-w-[2rem] px-2 py-1 rounded-md text-xs font-black"
                                            x-text="student.pivot.violation_count"></span>
                                    </td>
                                    <td class="p-4 text-center font-black text-lg"
                                        :class="student.pivot.status === 'completed' ? 'text-emerald-500' : 'text-slate-300'"
                                        x-text="student.pivot.status === 'completed' ? student.pivot.score : '-'"></td>
                                    <td class="p-4 pr-6">
                                        <div class="flex items-center justify-end gap-2">
                                            <button x-show="student.pivot.is_locked"
                                                @click="handleAction('unlock', student.id)"
                                                class="px-3 py-1.5 bg-emerald-500 text-white rounded-lg text-xs font-black hover:bg-emerald-600 transition shadow-sm"
                                                title="Buka Kunci">
                                                <i class="fas fa-unlock"></i>
                                            </button>
                                            <button
                                                x-show="student.pivot.status === 'ongoing' && !student.pivot.is_locked"
                                                @click="handleAction('forceFinish', student.id)"
                                                class="px-3 py-1.5 bg-amber-500 text-white rounded-lg text-xs font-black hover:bg-amber-600 transition shadow-sm"
                                                title="Selesaikan Paksa">
                                                <i class="fas fa-stop-circle"></i>
                                            </button>
                                            <button @click="handleAction('reset', student.id)"
                                                class="px-3 py-1.5 bg-slate-100 text-slate-500 rounded-lg text-xs font-black hover:bg-rose-500 hover:text-white transition shadow-sm"
                                                title="Reset Ujian">
                                                <i class="fas fa-redo"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function proctorMonitor() {
            return {
                search: '',
                selectedSchools: [], // Mengubah string menjadi Array untuk Multi-Select
                students: @json($students),
                isAutoUpdate: true,
                interval: null,

                init() {
                    this.startInterval();
                },

                // Filter Ganda (Nama & Multiple Sekolah)
                get filteredStudents() {
                    return this.students.filter(s => {
                        const matchName = s.name.toLowerCase().includes(this.search.toLowerCase());

                        // Jika array kosong (Semua Sekolah), tampilkan semua.
                        // Jika ada isinya, cek apakah ID sekolah siswa termasuk di dalam array tersebut
                        const matchSchool = this.selectedSchools.length === 0 ||
                            (s.school && this.selectedSchools.includes(String(s.school.id)));

                        return matchName && matchSchool;
                    });
                },

                startInterval() {
                    this.interval = setInterval(() => {
                        if (this.isAutoUpdate) this.fetchData();
                    }, 5000);
                },

                toggleAutoUpdate() {
                    this.isAutoUpdate = !this.isAutoUpdate;
                },

                fetchData() {
                    axios.get(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                        .then(res => {
                            this.students = res.data.students;
                        })
                        .catch(err => console.error("Update failed", err));
                },

                handleAction(action, studentId) {
                    // 1. Sesuaikan penamaan aksi dengan route web.php Anda (forceFinish -> force-finish)
                    let routeAction = action === 'forceFinish' ? 'force-finish' : action;

                    // 2. Sesuaikan Prefix URL menjadi /proctor/sessions/...
                    let url = `/proctor/sessions/{{ $examSession->id }}/${routeAction}/${studentId}`;

                    let title = action === 'reset' ? 'Reset Ujian?' : (action === 'unlock' ? 'Buka Kunci?' : 'Selesaikan Paksa?');

                    Swal.fire({
                        title: title,
                        text: "Aksi ini akan mengubah status ujian siswa secara langsung.",
                        icon: action === 'reset' ? 'error' : 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Lakukan',
                        confirmButtonColor: action === 'reset' ? '#ef4444' : '#6366f1'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // 3. Tambahkan CSRF Token untuk keamanan ekstra agar tidak ditolak (Error 419)
                            axios.post(url, {}, {
                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                            }).then(() => {
                                Swal.fire({icon: 'success', title: 'Berhasil!', timer: 1500, showConfirmButton: false});
                                this.fetchData();
                            }).catch(err => {
                                console.error(err);
                                Swal.fire('Gagal', 'Terjadi kesalahan sistem.', 'error');
                            });
                        }
                    });
                }
            }
        }
    </script>
</x-app-layout>