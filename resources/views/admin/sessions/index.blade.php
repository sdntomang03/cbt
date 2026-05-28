<x-app-layout>
    <x-slot name="header">
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap"
            rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
        <style>
            [x-cloak] {
                display: none !important;
            }

            body {
                font-family: 'Nunito', sans-serif;
                background-color: #f0f4f8;
            }

            /* Custom Scrollbar untuk Table */
            .custom-scrollbar::-webkit-scrollbar {
                height: 8px;
                width: 8px;
            }

            .custom-scrollbar::-webkit-scrollbar-thumb {
                background: #cbd5e1;
                border-radius: 10px;
            }
        </style>

        <div class="flex flex-col md:flex-row justify-between items-center w-full gap-6 py-2 px-2" x-data>
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

            <button @click="$dispatch('buka-modal-sesi')"
                class="bg-slate-900 hover:bg-black text-white px-6 py-3 rounded-full shadow-xl shadow-slate-300 transition-all active:scale-95 font-bold flex items-center gap-3 shrink-0">
                <div class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center">
                    <i class="fas fa-plus text-xs"></i>
                </div>
                <span>Buat Sesi Baru</span>
            </button>
        </div>
    </x-slot>

    <div class="min-h-screen py-10" x-data="sessionManager()" @buka-modal-sesi.window="openModal()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- TAMPILAN 1: DAFTAR UJIAN (DIUBAH MENJADI DATATABLE) --}}
            @if(!request('exam_id') && !request('search'))
            <div class="mb-8">
                <h3 class="text-lg font-black text-slate-700 mb-4 px-2">Pilih Ujian untuk melihat jadwal sesinya:</h3>

                <div class="bg-white shadow-sm sm:rounded-[2rem] border border-slate-100 overflow-hidden mb-6">
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse whitespace-nowrap">
                            <thead>
                                <tr
                                    class="bg-slate-50/80 border-b border-slate-100 text-[10px] uppercase tracking-widest text-slate-400 font-black">
                                    <th class="px-6 py-5 rounded-tl-[2rem]">Informasi Ujian</th>
                                    <th class="px-6 py-5 text-center">Durasi</th>
                                    <th class="px-6 py-5 text-right rounded-tr-[2rem]">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 text-sm">
                                @forelse($exams as $exam)
                                {{-- Baris tabel bisa langsung diklik --}}
                                <tr class="hover:bg-slate-50/50 transition-colors group cursor-pointer"
                                    onclick="window.location='{{ request()->fullUrlWithQuery(['exam_id' => $exam->id]) }}'">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <div
                                                class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg shrink-0 group-hover:scale-110 transition-transform">
                                                <i class="fas fa-file-alt"></i>
                                            </div>
                                            <div>
                                                <div class="font-black text-slate-800 text-base mb-1">{{ $exam->title }}
                                                </div>
                                                <div
                                                    class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1 flex flex-wrap gap-2 items-center">
                                                    <span class="bg-slate-100 px-2 py-0.5 rounded-md"><i
                                                            class="fas fa-layer-group mr-1 opacity-70"></i> {{
                                                        $exam->level->name ?? 'Umum' }}</span>
                                                    <span class="bg-slate-100 px-2 py-0.5 rounded-md"><i
                                                            class="fas fa-book mr-1 opacity-70"></i> {{
                                                        $exam->subject->name ?? 'Umum' }}</span>
                                                    <span class="bg-indigo-50 text-indigo-500 px-2 py-0.5 rounded-md"><i
                                                            class="fas fa-question-circle mr-1"></i> {{
                                                        $exam->questions_count }} Soal</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div
                                            class="inline-flex items-center gap-2 text-xs font-bold text-slate-600 bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200/60">
                                            <i class="far fa-clock text-slate-400"></i> {{ $exam->duration_minutes }}
                                            Menit
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div
                                            class="inline-flex items-center justify-between text-xs font-bold text-indigo-600 bg-indigo-50 group-hover:bg-indigo-600 group-hover:text-white px-4 py-2.5 rounded-xl transition-colors">
                                            <span>Lihat Sesi</span>
                                            <i
                                                class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="py-20 text-center">
                                        <div
                                            class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                            <i class="fas fa-folder-open text-3xl text-slate-300"></i>
                                        </div>
                                        <h3 class="text-lg font-black text-slate-700">Belum Ada Ujian</h3>
                                        <p class="text-slate-400 font-bold text-sm mt-1">Belum ada ujian yang tersedia
                                            untuk dikelola sesinya.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- TAMPILAN 2: DAFTAR SESI (DATATABLE) --}}
            @else
            <div
                class="bg-white p-4 sm:p-5 rounded-2xl shadow-sm border border-slate-100 mb-6 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">

                <a href="{{ route('admin.exam-sessions.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-50 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl font-bold text-sm transition-colors shrink-0">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>

                <form method="GET" action="{{ route('admin.exam-sessions.index') }}"
                    class="flex flex-wrap w-full lg:w-auto lg:max-w-3xl gap-3">
                    <input type="hidden" name="exam_id" value="{{ request('exam_id') }}">

                    @if(auth()->user()->hasRole('admin'))
                    <div class="relative flex-1 min-w-[200px]">
                        <select name="school_id" onchange="this.form.submit()"
                            class="w-full bg-slate-50 border-transparent rounded-xl focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition py-2.5 pl-4 pr-8 font-bold text-slate-600 text-sm">
                            <option value="">-- Semua Sekolah --</option>
                            @foreach($schools as $school)
                            <option value="{{ $school->id }}" {{ request('school_id')==$school->id ? 'selected' : '' }}>
                                {{ $school->name }}
                            </option>
                            @endforeach
                        </select>
                        <i
                            class="fas fa-chevron-down absolute right-4 top-3.5 text-xs text-slate-400 pointer-events-none"></i>
                    </div>
                    @endif

                    <div class="relative flex-1 min-w-[200px]">
                        <i class="fas fa-search absolute left-4 top-3.5 text-slate-400 text-sm"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama sesi..."
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border-transparent rounded-xl focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition font-bold text-slate-600 text-sm">
                    </div>

                    <button type="submit"
                        class="bg-slate-900 text-white px-6 py-2.5 rounded-xl font-black shadow-md hover:bg-slate-800 transition active:scale-95 text-sm">
                        Cari
                    </button>

                    @if(request('search') || request('school_id'))
                    <a href="{{ route('admin.exam-sessions.index', ['exam_id' => request('exam_id')]) }}"
                        class="bg-rose-50 text-rose-500 px-4 py-2.5 rounded-xl font-bold hover:bg-rose-500 hover:text-white transition flex items-center shadow-sm"
                        title="Reset Filter">
                        <i class="fas fa-times"></i>
                    </a>
                    @endif
                </form>
            </div>

            <div class="bg-white shadow-sm sm:rounded-[2rem] border border-slate-100 overflow-hidden mb-6">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead>
                            <tr
                                class="bg-slate-50/80 border-b border-slate-100 text-[10px] uppercase tracking-widest text-slate-400 font-black">
                                {{-- PERBAIKAN: Tambahkan w-full agar kolom ini mendorong kolom lain ke kanan --}}
                                <th class="px-6 py-5 rounded-tl-[2rem] w-full">Informasi Sesi</th>
                                <th class="px-6 py-5">Jadwal Pelaksanaan</th>
                                {{-- PERBAIKAN: Tambahkan w-1 agar kolom-kolom ini mengecil dan merapat --}}
                                <th class="px-6 py-5 text-center w-1">Status</th>
                                <th class="px-6 py-5 w-1">Token Akses</th>
                                <th class="px-6 py-5 text-right rounded-tr-[2rem] w-1">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 text-sm">
                            @forelse($sessions as $session)
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-6 py-4 whitespace-normal min-w-[250px]">
                                    <div class="font-black text-slate-800 text-base mb-1">{{ $session->session_name }}
                                    </div>
                                    <div class="text-xs font-bold text-indigo-500 line-clamp-1">{{ $session->exam->title
                                        ?? 'Ujian Dihapus' }}</div>
                                    @if(auth()->user()->hasRole('admin') && $session->school)
                                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">
                                        <i class="fas fa-school mr-1 opacity-70"></i> {{ $session->school->name }}
                                    </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1.5">
                                        <div
                                            class="flex items-center gap-2 text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-md w-fit">
                                            <i class="fas fa-play-circle opacity-70"></i>
                                            {{ \Carbon\Carbon::parse($session->start_time)->format('d M Y, H:i') }}
                                        </div>
                                        <div
                                            class="flex items-center gap-2 text-xs font-bold text-rose-600 bg-rose-50 px-2 py-1 rounded-md w-fit">
                                            <i class="fas fa-stop-circle opacity-70"></i>
                                            {{ \Carbon\Carbon::parse($session->end_time)->format('d M Y, H:i') }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="inline-flex px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wide border
                                        {{ now()->between($session->start_time, $session->end_time)
                                            ? 'bg-emerald-50 text-emerald-600 border-emerald-100'
                                            : (now()->lessThan($session->start_time)
                                                ? 'bg-blue-50 text-blue-600 border-blue-100'
                                                : 'bg-slate-50 text-slate-400 border-slate-100') }}">
                                        @if(now()->between($session->start_time, $session->end_time))
                                        <i class="fas fa-circle text-[8px] mr-1.5 animate-pulse"></i> Berlangsung
                                        @elseif(now()->lessThan($session->start_time))
                                        <i class="fas fa-clock mr-1.5"></i> Akan Datang
                                        @else
                                        <i class="fas fa-check-circle mr-1.5"></i> Selesai
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    {{-- PERBAIKAN: Gunakan flex-col agar letaknya atas-bawah --}}
                                    <div class="flex flex-col gap-2">
                                        {{-- Kotak Token --}}
                                        <div class="font-mono font-black text-lg text-center text-slate-800 tracking-widest bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200"
                                            id="token-{{ $session->id }}">
                                            {{ $session->token ?? '------' }}
                                        </div>

                                        {{-- Tombol Aksi Token (Selalu Tampil) --}}
                                        <div class="flex gap-1.5 w-full">
                                            <button @click="copyToken({{ $session->id }})"
                                                class="flex-1 h-8 rounded-md bg-white text-slate-400 hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-200 shadow-sm border border-slate-200 flex items-center justify-center transition"
                                                title="Salin Token">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                            <button @click="regenerateToken({{ $session->id }})"
                                                class="flex-1 h-8 rounded-md bg-white text-slate-400 hover:bg-orange-50 hover:text-orange-500 hover:border-orange-200 shadow-sm border border-slate-200 flex items-center justify-center transition"
                                                title="Acak Ulang Token">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.exam-sessions.students.index', $session->id) }}"
                                            class="px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-600 hover:text-white transition font-bold text-xs flex items-center gap-1"
                                            title="Kelola Peserta">
                                            <i class="fas fa-users"></i>
                                        </a>
                                        <button @click="editSession({{ $session }})"
                                            class="w-8 h-8 bg-amber-50 text-amber-600 rounded-lg hover:bg-amber-500 hover:text-white transition flex items-center justify-center"
                                            title="Edit Sesi">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('admin.exam-sessions.destroy', $session->id) }}"
                                            method="POST" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="button" @click="confirmDelete($event)"
                                                class="w-8 h-8 bg-rose-50 text-rose-500 rounded-lg hover:bg-rose-600 hover:text-white transition flex items-center justify-center"
                                                title="Hapus Sesi">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-20 text-center">
                                    <div
                                        class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-calendar-times text-3xl text-slate-300"></i>
                                    </div>
                                    <h3 class="text-lg font-black text-slate-700">Belum Ada Sesi Ujian</h3>
                                    <p class="text-slate-400 font-bold text-sm mt-1">Buat sesi ujian baru untuk
                                        menjadwalkan tes.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div>
                {{ $sessions->links() }}
            </div>
            @endif
        </div>

        {{-- MODAL CRUD SESI --}}
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

    </div> {{-- End Main Container for Alpine --}}

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('sessionManager', () => ({
                isModalOpen: false,
                isEdit: false,
                isLoading: false,
                currentId: null,
                form: { exam_id: '', session_name: '', start_time: '', end_time: '' },

                init() {
                    let token = document.head.querySelector('meta[name="csrf-token"]');
                    if (token && window.axios) {
                        axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
                    }
                },

                openModal() {
                    this.isEdit = false;
                    const urlParams = new URLSearchParams(window.location.search);
                    const currentExamId = urlParams.get('exam_id');

                    this.form = { exam_id: currentExamId || '', session_name: '', start_time: '', end_time: '' };
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

                copyToken(id) {
                    const token = document.getElementById(`token-${id}`).innerText.trim();
                    navigator.clipboard.writeText(token);
                    const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
                    Toast.fire({ icon: 'success', title: 'Token disalin: ' + token });
                },

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
            }));
        });
    </script>
    @endpush
</x-app-layout>