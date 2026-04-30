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

            .hover-lift {
                transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
            }

            .hover-lift:hover {
                transform: translateY(-4px);
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

            {{-- Kita dispatch custom event ke window agar bisa ditangkap oleh komponen Alpine utama di bawah --}}
            <button @click="$dispatch('buka-modal-sesi')"
                class="bg-slate-900 hover:bg-black text-white px-6 py-3 rounded-full shadow-xl shadow-slate-300 transition-all active:scale-95 font-bold flex items-center gap-3 shrink-0">
                <div class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center">
                    <i class="fas fa-plus text-xs"></i>
                </div>
                <span>Buat Sesi Baru</span>
            </button>
        </div>
    </x-slot>

    {{-- Kita tangkap event custom di scope utama ini --}}
    <div class="min-h-screen py-10" x-data="sessionManager()" @buka-modal-sesi.window="openModal()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- TAMPILAN 1: DAFTAR UJIAN (Muncul jika URL tidak memiliki exam_id) --}}
            @if(!request('exam_id') && !request('search'))
            <div class="mb-8">
                <h3 class="text-lg font-black text-slate-700 mb-4 px-2">Pilih Ujian untuk melihat jadwal sesinya:</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($exams as $exam)
                    <a href="{{ request()->fullUrlWithQuery(['exam_id' => $exam->id]) }}"
                        class="block bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 hover:border-indigo-400 hover:shadow-md transition-all hover-lift group">
                        <div class="flex items-center gap-4 mb-4">
                            <div
                                class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-black text-lg text-slate-800 truncate">{{ $exam->title }}</h3>
                                <p class="text-xs font-bold text-slate-400 mt-1"><i class="far fa-clock mr-1"></i> {{
                                    $exam->duration_minutes }} Menit</p>
                            </div>
                        </div>
                        <div
                            class="flex items-center justify-between text-xs font-bold text-indigo-500 bg-indigo-50/50 px-4 py-2.5 rounded-xl">
                            <span>Lihat Jadwal Sesi</span>
                            <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </a>
                    @empty
                    <div class="col-span-full py-12 text-center">
                        <i class="fas fa-folder-open text-4xl text-slate-300 mb-3 block"></i>
                        <p class="text-slate-500 font-bold">Belum ada ujian yang tersedia.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- TAMPILAN 2: DAFTAR SESI (Muncul setelah Ujian diklik) --}}
            @else
            <div
                class="bg-white p-4 sm:p-5 rounded-2xl shadow-sm border border-slate-100 mb-8 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">

                <a href="{{ route('admin.exam-sessions.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-50 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl font-bold text-sm transition-colors shrink-0">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>

                <form method="GET" action="{{ route('admin.exam-sessions.index') }}"
                    class="flex flex-wrap w-full lg:w-auto lg:max-w-3xl gap-3">
                    {{-- Pertahankan filter exam_id saat melakukan pencarian --}}
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

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($sessions as $session)
                <div
                    class="bg-white rounded-[2.5rem] p-6 shadow-sm border border-slate-100 hover:border-indigo-100 transition-all hover-lift group relative flex flex-col h-full">

                    <div class="absolute inset-0 overflow-hidden rounded-[2.5rem] pointer-events-none">
                        <div
                            class="absolute -right-6 -top-6 w-24 h-24 bg-slate-50 rounded-full group-hover:bg-indigo-50 transition-colors">
                        </div>
                    </div>

                    <div class="flex justify-between items-start mb-4 relative z-50">
                        <div class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wide border
                            {{ now()->between($session->start_time, $session->end_time)
                                ? 'bg-emerald-50 text-emerald-600 border-emerald-100'
                                : (now()->lessThan($session->start_time)
                                    ? 'bg-blue-50 text-blue-600 border-blue-100'
                                    : 'bg-slate-50 text-slate-400 border-slate-100') }}">
                            @if(now()->between($session->start_time, $session->end_time))
                            <i class="fas fa-circle text-[8px] mr-1 animate-pulse"></i> Berlangsung
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
                        @if(auth()->user()->hasRole('admin') && $session->school)
                        <div
                            class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 flex items-center">
                            <i class="fas fa-school mr-1.5 opacity-70"></i> {{ $session->school->name }}
                        </div>
                        @endif

                        <h3 class="font-black text-xl text-slate-800 mb-1 leading-tight">{{ $session->session_name }}
                        </h3>
                        <p class="text-sm font-bold text-indigo-500 mb-4 line-clamp-1">{{ $session->exam->title ??
                            'Ujian Dihapus' }}</p>

                        <div class="space-y-4">
                            <div class="flex items-center gap-3 text-sm font-semibold text-slate-500">
                                <div
                                    class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-500 shadow-sm border border-emerald-100 shrink-0">
                                    <i class="fas fa-calendar-plus"></i>
                                </div>
                                <div class="flex flex-col">
                                    <span
                                        class="text-[10px] font-black uppercase tracking-wider text-slate-400 leading-none mb-1">Mulai</span>
                                    <span class="text-slate-700 leading-none">{{
                                        \Carbon\Carbon::parse($session->start_time)->format('d M Y, H:i') }} WIB</span>
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
                                    <span class="text-slate-700 leading-none">{{
                                        \Carbon\Carbon::parse($session->end_time)->format('d M Y, H:i') }} WIB</span>
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
                    <h3 class="text-xl font-black text-slate-800">Belum Ada Sesi Ujian</h3>
                    <p class="text-slate-400 font-bold max-w-md mx-auto mt-2">Buat sesi ujian baru untuk mulai
                        menjadwalkan tes bagi siswa.</p>
                </div>
                @endforelse
            </div>

            <div class="mt-8">
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