<x-app-layout>
    <div class="py-10 min-h-screen font-sans bg-gray-50" x-data="{ search: '' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex items-center justify-between mb-8">
                <div>
                    <a href="{{ route('admin.exam-sessions.index') }}"
                        class="text-sm font-bold text-gray-400 hover:text-indigo-600 transition mb-2 inline-block">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Jadwal
                    </a>
                    <h2 class="text-3xl font-black text-gray-800">{{ $examSession->title }}</h2>
                    <p class="text-indigo-500 font-bold">Kelola Peserta Ujian</p>
                </div>
                <div class="text-right">
                    <span class="block text-xs font-black text-gray-400 uppercase">Total Peserta</span>
                    <span class="text-3xl font-black text-indigo-600">{{ $enrolledStudents->count() }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                <div class="bg-white rounded-[2.5rem] p-8 shadow-xl shadow-gray-100 border border-white">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="font-black text-xl text-gray-800">Siswa Tersedia</h3>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-300 text-sm"></i>
                            <input type="text" x-model="search" placeholder="Cari nama..."
                                class="pl-9 pr-4 py-2 bg-gray-50 border-none rounded-xl text-sm font-bold focus:ring-2 focus:ring-indigo-500 w-48 transition-all">
                        </div>
                    </div>

                    <form action="{{ route('admin.exam-sessions.students.store', $examSession->id) }}" method="POST">
                        @csrf
                        <div class="h-96 overflow-y-auto custom-scrollbar pr-2 space-y-2">
                            @forelse($availableStudents as $student)
                            <label
                                x-show="search === '' || '{{ strtolower($student->name) }}'.includes(search.toLowerCase())"
                                class="flex items-center p-4 rounded-2xl border border-gray-100 cursor-pointer hover:bg-indigo-50 hover:border-indigo-200 transition-all group">
                                <input type="checkbox" name="student_ids[]" value="{{ $student->id }}"
                                    class="w-5 h-5 text-indigo-600 rounded-lg border-gray-300 focus:ring-indigo-500 transition-all">
                                <div class="ml-4">
                                    <p class="font-bold text-gray-700 group-hover:text-indigo-700">{{ $student->name }}
                                    </p>
                                    <p class="text-xs text-gray-400 font-semibold">{{ $student->email }}</p>
                                </div>
                            </label>
                            @empty
                            <div class="text-center py-10">
                                <p class="text-gray-400 font-bold">Semua siswa sudah terdaftar.</p>
                            </div>
                            @endforelse
                        </div>

                        <div class="mt-6 pt-6 border-t border-gray-100">
                            <button type="submit"
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-4 rounded-xl font-black shadow-lg shadow-indigo-200 transition-all active:scale-95 flex justify-center items-center gap-2">
                                <i class="fas fa-user-plus"></i> Tambahkan yang Dipilih
                            </button>
                        </div>
                    </form>
                </div>

                <div
                    class="bg-slate-900 rounded-[2.5rem] p-8 shadow-2xl relative overflow-hidden flex flex-col h-full border-4 border-slate-800">

                    <div class="flex justify-between items-center mb-6 relative z-10 border-b border-slate-700 pb-4">
                        <div>
                            <h3 class="font-black text-xl text-white">Peserta Terdaftar</h3>
                            <p class="text-xs text-slate-400 mt-1">Siswa yang berhak mengikuti ujian</p>
                        </div>
                        <span class="bg-indigo-600 text-white text-sm font-bold px-4 py-2 rounded-xl shadow-lg">
                            {{ $enrolledStudents->count() }}
                        </span>
                    </div>

                    <div class="flex-1 overflow-y-auto custom-scrollbar pr-2 space-y-3 relative z-10">
                        @forelse($enrolledStudents as $student)
                        <div
                            class="flex items-center justify-between p-4 bg-slate-800 border border-slate-700 rounded-2xl hover:bg-slate-700 transition-all group shadow-md">

                            <div class="flex items-center gap-4 min-w-0">
                                <div
                                    class="w-12 h-12 rounded-xl bg-indigo-500 flex flex-shrink-0 items-center justify-center font-black text-white text-lg border-2 border-slate-600">
                                    {{ substr($student->name, 0, 1) }}
                                </div>

                                <div class="min-w-0">
                                    <p class="font-bold text-white text-sm truncate max-w-[150px]">{{ $student->name }}
                                    </p>

                                    @php
                                    $status = $student->pivot->status ?? 'not_started';
                                    $statusColor = match($status) {
                                    'not_started' => 'bg-slate-600 text-slate-200',
                                    'ongoing' => 'bg-amber-500 text-black font-black animate-pulse',
                                    'completed' => 'bg-emerald-500 text-white',
                                    default => 'bg-slate-600 text-slate-200',
                                    };
                                    $label = match($status) {
                                    'not_started' => 'Belum Mulai',
                                    'ongoing' => 'Mengerjakan...',
                                    'completed' => 'Selesai',
                                    default => '-',
                                    };
                                    @endphp
                                    <span
                                        class="inline-block mt-1 text-[10px] px-2 py-0.5 rounded {{ $statusColor }} uppercase tracking-wider">
                                        {{ $label }}
                                    </span>
                                </div>
                            </div>

                            <form
                                action="{{ route('admin.exam-sessions.students.destroy', [$examSession->id, $student->id]) }}"
                                method="POST" class="ml-2 flex-shrink-0">
                                @csrf
                                @method('DELETE')

                                <button type="button" @click="confirmRemove($event, '{{ $student->name }}')"
                                    class="w-10 h-10 flex items-center justify-center rounded-xl text-white shadow-lg border-2 border-red-800 transition-all duration-300 ease-out transform hover:scale-110 hover:-rotate-12 hover:bg-red-500 bg-red-600 hover:shadow-red-500/50 active:scale-90"
                                    title="Keluarkan Siswa">

                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                        class="w-5 h-5 transition-transform duration-300 hover:scale-110">
                                        <path fill-rule="evenodd"
                                            d="M16.5 4.478v.227a48.816 48.816 0 0 1 3.878.512.75.75 0 1 1-.256 1.478l-.209-.035-1.005 13.07a3 3 0 0 1-2.991 2.77H8.084a3 3 0 0 1-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 0 1-.256-1.478A48.567 48.567 0 0 1 7.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 0 1 3.369 0c1.603.051 2.815 1.387 2.815 2.951Zm-6.136-1.452a51.196 51.196 0 0 1 3.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 0 0-6 0v-.113c0-.794.609-1.428 1.364-1.452Zm-.355 5.945a.75.75 0 1 0-1.5.058l.347 9a.75.75 0 1 0 1.499-.058l-.346-9Zm5.48.058a.75.75 0 1 0-1.498-.058l-.347 9a.75.75 0 0 0 1.5.058l.345-9Z"
                                            clip-rule="evenodd" />
                                    </svg>

                                </button>
                            </form>
                        </div>
                        @empty
                        <div
                            class="flex flex-col items-center justify-center h-64 text-center border-2 border-dashed border-slate-700 rounded-3xl">
                            <div class="w-16 h-16 bg-slate-800 rounded-full flex items-center justify-center mb-4">
                                <i class="fas fa-user-slash text-2xl text-slate-500"></i>
                            </div>
                            <p class="text-slate-400 font-bold">Belum ada peserta.</p>
                        </div>
                        @endforelse
                    </div>
                </div>



            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmRemove(e, name) {
        Swal.fire({
            title: 'Keluarkan Siswa?',
            text: "Keluarkan " + name + " dari sesi ujian ini?",
            icon: 'warning',
            background: '#1e293b', // Dark theme alert
            color: '#fff',
            showCancelButton: true,
            confirmButtonColor: '#f43f5e',
            cancelButtonColor: '#475569',
            confirmButtonText: 'Ya, Keluarkan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Submit form terdekat dari tombol yang diklik
                e.target.closest('form').submit();
            }
        });
    }
    </script>
</x-app-layout>
