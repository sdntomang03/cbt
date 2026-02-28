<x-app-layout>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f0f4f8;
        }

        .hover-lift {
            transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .hover-lift:hover {
            transform: translateY(-4px);
        }
    </style>

    <div class="min-h-screen py-10" x-data="sessionManager()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col md:flex-row justify-between items-center mb-10 gap-6">
                <div class="flex items-center gap-5">
                    <div
                        class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-cyan-500 flex items-center justify-center shadow-lg shadow-cyan-200 rotate-3">
                        <i class="fas fa-calendar-alt text-white text-2xl"></i>
                    </div>
                    <div>
                        <h2 class="font-black text-3xl text-slate-800 tracking-tight">Jadwal Ujian</h2>
                        <p class="text-slate-400 font-bold text-sm">Kelola sesi, waktu, dan token akses siswa</p>
                    </div>
                </div>

                <button @click="openModal()"
                    class="bg-slate-900 hover:bg-black text-white px-6 py-3 rounded-full shadow-xl shadow-slate-300 transition-all active:scale-95 font-bold flex items-center gap-3">
                    <div class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center">
                        <i class="fas fa-plus text-xs"></i>
                    </div>
                    <span>Buat Sesi Baru</span>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($sessions as $session)
                <div
                    class="bg-white rounded-[2.5rem] p-6 shadow-sm border border-slate-100 hover:border-indigo-100 transition-all hover-lift group relative flex flex-col h-full">

                    <div class="absolute inset-0 overflow-hidden rounded-[2.5rem] pointer-events-none">
                        <div
                            class="absolute -right-6 -top-6 w-24 h-24 bg-slate-50 rounded-full group-hover:bg-indigo-50 transition-colors">
                        </div>
                    </div>

                    <div class="flex justify-between items-start mb-6 relative z-50">
                        <div class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wide border
                            {{ now()->between($session->start_time, $session->end_time)
                                ? 'bg-emerald-50 text-emerald-600 border-emerald-100'
                                : (now()->lessThan($session->start_time)
                                    ? 'bg-blue-50 text-blue-600 border-blue-100'
                                    : 'bg-slate-50 text-slate-400 border-slate-100') }}">
                            @if(now()->between($session->start_time, $session->end_time))
                            <i class="fas fa-circle text-[8px] mr-1 animate-pulse"></i> Sedang Berlangsung
                            @elseif(now()->lessThan($session->start_time))
                            <i class="fas fa-clock mr-1"></i> Akan Datang
                            @else
                            <i class="fas fa-check-circle mr-1"></i> Selesai
                            @endif
                        </div>

                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" @click.outside="open = false"
                                class="text-slate-300 hover:text-slate-600 transition">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>

                            <div x-show="open" x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-slate-200 py-2 z-50 origin-top-right">

                                <a href="{{ route('admin.exam-sessions.students.index', $session->id) }}"
                                    class="block px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-50 hover:text-indigo-600">
                                    <i class="fas fa-users mr-2"></i> Kelola Peserta
                                </a>
                                <a href="#" @click.prevent="editSession({{ $session }})"
                                    class="block px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-50 hover:text-indigo-600">
                                    <i class="fas fa-edit mr-2"></i> Edit Sesi
                                </a>
                                <form action="{{ route('admin.exam-sessions.destroy', $session->id) }}" method="POST"
                                    class="block">
                                    @csrf @method('DELETE')
                                    <button type="button" @click="confirmDelete($event)"
                                        class="w-full text-left px-4 py-2 text-sm font-bold text-rose-500 hover:bg-rose-50">
                                        <i class="fas fa-trash-alt mr-2"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6 relative z-10 flex-1">
                        <h3 class="font-black text-xl text-slate-800 mb-1 leading-tight">{{ $session->session_name }}
                        </h3>
                        <p class="text-sm font-bold text-indigo-500 mb-4">{{ $session->exam->title }}</p>

                        <div class="space-y-4">
                            <div class="flex items-center gap-3 text-sm font-semibold text-slate-500">
                                <div
                                    class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-500 shadow-sm border border-emerald-100 shrink-0">
                                    <i class="fas fa-calendar-plus"></i>
                                </div>
                                <div class="flex flex-col">
                                    <span
                                        class="text-[10px] font-black uppercase tracking-wider text-slate-400 leading-none mb-1">Mulai</span>
                                    <span class="text-slate-700 leading-none">{{ $session->start_time->format('d M Y,
                                        H:i') }} WIB</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 text-sm font-semibold text-slate-500">
                                <div
                                    class="w-8 h-8 rounded-lg bg-rose-50 flex items-center justify-center text-rose-500 shadow-sm border border-rose-100 shrink-0">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <div class="flex flex-col">
                                    <span
                                        class="text-[10px] font-black uppercase tracking-wider text-slate-400 leading-none mb-1">Berakhir</span>
                                    <span class="text-slate-700 leading-none">{{ $session->end_time->format('d M Y,
                                        H:i') }} WIB</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-auto pt-4 relative z-10 border-t border-slate-50">
                        <div
                            class="bg-slate-50 rounded-2xl p-4 border border-slate-100 flex items-center justify-between group-hover:border-indigo-100 transition-colors">
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1">Token
                                    Akses</p>
                                <div class="font-mono font-black text-2xl text-slate-800 tracking-widest"
                                    id="token-{{ $session->id }}">
                                    {{ $session->token ?? '------' }}
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button @click="copyToken({{ $session->id }})"
                                    class="w-10 h-10 rounded-xl bg-white text-slate-400 hover:text-indigo-600 shadow-sm hover:shadow-md transition flex items-center justify-center border border-slate-100"
                                    title="Salin Token">
                                    <i class="fas fa-copy"></i>
                                </button>
                                <button @click="regenerateToken({{ $session->id }})"
                                    class="w-10 h-10 rounded-xl bg-white text-slate-400 hover:text-orange-500 shadow-sm hover:shadow-md transition flex items-center justify-center border border-slate-100"
                                    title="Acak Ulang Token">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
                @empty
                <div class="col-span-full flex flex-col items-center justify-center py-20 text-center">
                    <div class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-calendar-times text-4xl text-slate-300"></i>
                    </div>
                    <h3 class="text-xl font-black text-slate-800">Belum Ada Jadwal</h3>
                    <p class="text-slate-400 font-bold max-w-md mx-auto mt-2">Buat sesi ujian baru untuk mulai
                        menjadwalkan tes bagi siswa.</p>
                </div>
                @endforelse
            </div>

            <div class="mt-8">
                {{ $sessions->links() }}
            </div>
        </div>

        <div x-show="isModalOpen" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6" x-cloak>

            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="closeModal()"></div>

            <div
                class="bg-white w-full max-w-lg rounded-[2.5rem] shadow-2xl relative z-10 overflow-hidden border-4 border-white">
                <div class="px-8 py-6 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
                    <div>
                        <h3 class="font-black text-xl text-slate-800" x-text="isEdit ? 'Edit Sesi' : 'Buat Sesi Baru'">
                        </h3>
                        <p class="text-xs font-bold text-slate-400">Atur waktu pelaksanaan ujian</p>
                    </div>
                    <button @click="closeModal()"
                        class="w-8 h-8 rounded-full bg-slate-200 text-slate-500 hover:bg-slate-300 flex items-center justify-center transition">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="p-8 space-y-6">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase mb-2">Pilih Paket Soal</label>
                        <select x-model="form.exam_id"
                            class="w-full bg-slate-50 border-none rounded-xl text-sm font-bold text-slate-700 py-3 px-4 focus:ring-2 focus:ring-indigo-500"
                            :disabled="isEdit">
                            <option value="">-- Pilih Ujian --</option>
                            @foreach($exams as $exam)
                            <option value="{{ $exam->id }}">{{ $exam->title }} ({{ $exam->duration_minutes }} Menit)
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase mb-2">Nama Sesi / Kelas</label>
                        <input type="text" x-model="form.session_name"
                            class="w-full bg-slate-50 border-none rounded-xl text-sm font-bold text-slate-700 py-3 px-4 focus:ring-2 focus:ring-indigo-500 placeholder-slate-300"
                            placeholder="Contoh: UAS Matematika Kelas X-A">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase mb-2">Mulai</label>
                            <input type="datetime-local" x-model="form.start_time"
                                class="w-full bg-slate-50 border-none rounded-xl text-sm font-bold text-slate-700 py-3 px-4 focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase mb-2">Selesai</label>
                            <input type="datetime-local" x-model="form.end_time"
                                class="w-full bg-slate-50 border-none rounded-xl text-sm font-bold text-slate-700 py-3 px-4 focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" :disabled="isLoading"
                            class="w-full bg-slate-900 hover:bg-black text-white py-4 rounded-xl font-black shadow-lg shadow-slate-300 transition-all active:scale-95 flex items-center justify-center gap-2">
                            <span x-show="isLoading" class="animate-spin"><i class="fas fa-circle-notch"></i></span>
                            <span x-text="isEdit ? 'Simpan Perubahan' : 'Buat Sesi Sekarang'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function sessionManager() {
            return {
                isModalOpen: false,
                isEdit: false,
                isLoading: false,
                currentId: null,
                form: { exam_id: '', session_name: '', start_time: '', end_time: '' },

                openModal() {
                    this.isEdit = false;
                    this.form = { exam_id: '', session_name: '', start_time: '', end_time: '' };
                    this.isModalOpen = true;
                },

                closeModal() {
                    this.isModalOpen = false;
                },

                editSession(session) {
                    this.isEdit = true;
                    this.currentId = session.id;
                    let start = new Date(session.start_time).toISOString().slice(0, 16);
                    let end = new Date(session.end_time).toISOString().slice(0, 16);

                    this.form = {
                        exam_id: session.exam_id,
                        session_name: session.session_name,
                        start_time: start,
                        end_time: end
                    };
                    this.isModalOpen = true;
                },

                submitForm() {
                    if(!this.form.exam_id || !this.form.session_name || !this.form.start_time || !this.form.end_time) {
                        return Swal.fire({ icon: 'warning', title: 'Data Belum Lengkap', text: 'Mohon isi semua field yang tersedia.', confirmButtonColor: '#0f172a'});
                    }

                    this.isLoading = true;
                    const url = this.isEdit
                        ? `{{ url('admin/exam-sessions') }}/${this.currentId}`
                        : `{{ route('admin.exam-sessions.store') }}`;

                    const method = this.isEdit ? 'put' : 'post';

                    axios[method](url, this.form)
                        .then(() => {
                            this.closeModal();
                            Swal.fire({
                                icon: 'success', title: 'Berhasil!',
                                text: 'Data sesi berhasil disimpan.',
                                timer: 1500, showConfirmButton: false
                            }).then(() => location.reload());
                        })
                        .catch(err => {
                            console.error(err);
                            Swal.fire({ icon: 'error', title: 'Gagal', text: err.response?.data?.message || 'Terjadi kesalahan sistem.' });
                        })
                        .finally(() => this.isLoading = false);
                },

                confirmDelete(e) {
                    Swal.fire({
                        title: 'Hapus Sesi?', text: "Data siswa yang sudah mengerjakan mungkin akan terdampak.",
                        icon: 'warning', showCancelButton: true, confirmButtonColor: '#f43f5e', confirmButtonText: 'Ya, Hapus', cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) e.target.closest('form').submit();
                    });
                },

                // FITUR COPY TOKEN
                copyToken(id) {
                    const token = document.getElementById(`token-${id}`).innerText.trim();
                    navigator.clipboard.writeText(token);
                    const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
                    Toast.fire({ icon: 'success', title: 'Token disalin: ' + token });
                },

                // FITUR RESET TOKEN
                regenerateToken(id) {
                    Swal.fire({
                        title: 'Acak Ulang Token?', text: "Token lama tidak akan bisa digunakan lagi.",
                        icon: 'question', showCancelButton: true, confirmButtonColor: '#f97316', confirmButtonText: 'Ya, Acak'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            axios.post(`/admin/exam-sessions/${id}/regenerate-token`)
                                .then(res => {
                                    document.getElementById(`token-${id}`).innerText = res.data.token;
                                    Swal.fire({ icon: 'success', title: 'Token Baru: ' + res.data.token, timer: 1500, showConfirmButton: false });
                                });
                        }
                    });
                }
            }
        }
    </script>
</x-app-layout>