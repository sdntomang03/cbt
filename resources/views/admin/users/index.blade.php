<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
            {{-- Title Section --}}
            <div class="flex items-center gap-4">
                <div
                    class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-600 to-violet-600 flex items-center justify-center shadow-lg text-white shrink-0">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <div class="min-w-0">
                    <h2 class="font-black text-xl sm:text-2xl text-slate-800 tracking-tight truncate">
                        Manajemen User
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 font-bold truncate">
                        Kelola data siswa, guru, operator, dan admin
                    </p>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto mt-2 lg:mt-0">
                <button x-data @click="$dispatch('buka-modal-import')"
                    class="flex-1 lg:flex-none inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl font-semibold text-sm transition-all hover:bg-slate-50 hover:border-slate-300 active:scale-95 shadow-sm">
                    <i class="fas fa-file-excel text-emerald-500"></i>
                    <span>Import Excel</span>
                </button>

                <a href="{{ route('admin.users.download-template') }}"
                    class="flex-1 lg:flex-none inline-flex items-center justify-center gap-2 px-4 py-2.5 text-slate-500 hover:text-emerald-600 rounded-xl font-medium text-sm transition-colors">
                    <i class="fas fa-download"></i>
                    <span>Format</span>
                </a>

                <div class="hidden sm:block h-6 w-px bg-slate-200 mx-1"></div>

                <a href="{{ route('admin.users.create') }}"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-indigo-600 text-white rounded-xl font-bold text-sm transition-all hover:bg-indigo-700 hover:shadow-lg active:scale-95 shadow-md">
                    <i class="fas fa-plus"></i>
                    <span>Tambah User</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 mx-auto px-4 sm:px-6 lg:px-8 space-y-6" x-data="userManager()">

        {{-- Alert Success --}}
        @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
            x-transition:leave="transition ease-in duration-300"
            class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-xl shadow-sm flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <i class="fas fa-check-circle text-emerald-500 text-xl shrink-0"></i>
                <p class="text-emerald-700 font-bold text-sm sm:text-base">{{ session('success') }}</p>
            </div>
            <button @click="show = false"
                class="text-emerald-400 hover:text-emerald-700 transition w-8 h-8 rounded-full flex items-center justify-center hover:bg-emerald-100 shrink-0">
                <i class="fas fa-times"></i>
            </button>
        </div>
        @endif

        {{-- Alert Error --}}
        @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 7000)"
            x-transition:leave="transition ease-in duration-300"
            class="bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r-xl shadow-sm flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <i class="fas fa-exclamation-circle text-rose-500 text-xl shrink-0"></i>
                <p class="text-rose-700 font-bold text-sm sm:text-base">{{ session('error') }}</p>
            </div>
            <button @click="show = false"
                class="text-rose-400 hover:text-rose-700 transition w-8 h-8 rounded-full flex items-center justify-center hover:bg-rose-100 shrink-0">
                <i class="fas fa-times"></i>
            </button>
        </div>
        @endif

        {{-- Toolbar: Bulk Action & Search Form --}}
        <div
            class="bg-white p-4 sm:p-5 rounded-2xl shadow-sm border border-slate-100 flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4">

            {{-- Tombol Aksi Massal --}}
            <div class="flex items-center gap-2 w-full xl:w-auto shrink-0">
                <button x-show="selected.length > 0" x-cloak @click="deleteSelected()"
                    class="bg-rose-500 hover:bg-rose-600 text-white px-5 py-2.5 rounded-xl font-bold transition shadow-sm flex items-center justify-center gap-2 active:scale-95 flex-1 xl:flex-none">
                    <i class="fas fa-trash-alt"></i> Hapus (<span x-text="selected.length"></span>)
                </button>

                <button x-show="selected.length > 0" x-cloak @click="downloadSelected()"
                    class="bg-emerald-500 hover:bg-emerald-600 text-white px-5 py-2.5 rounded-xl font-bold transition shadow-sm flex items-center justify-center gap-2 active:scale-95 flex-1 xl:flex-none">
                    <i class="fas fa-file-export"></i> Download (<span x-text="selected.length"></span>)
                </button>
            </div>

            {{-- Form Pencarian & Filter --}}
            <form method="GET" action="{{ route('admin.users.index') }}"
                class="flex flex-col sm:flex-row w-full xl:w-auto gap-3 justify-end items-stretch sm:items-center">

                @if(auth()->user()->hasRole('admin'))
                <div class="relative w-full sm:w-48 shrink-0">
                    <select name="school_id" onchange="this.form.submit()"
                        class="w-full bg-slate-50 border-transparent rounded-xl focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition py-2.5 pl-4 pr-10 font-bold text-slate-600 appearance-none text-sm truncate">
                        <option value="">Semua Sekolah</option>
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

                <div class="relative w-full sm:w-64 xl:w-80 shrink-0">
                    <i class="fas fa-search absolute left-4 top-3.5 text-slate-400 text-sm"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari nama / username..."
                        class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border-transparent rounded-xl focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition font-bold text-slate-600 text-sm">
                </div>

                <div class="flex gap-2 w-full sm:w-auto shrink-0">
                    <button type="submit"
                        class="bg-slate-900 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-slate-800 transition shadow-md flex-1 sm:flex-none text-sm">
                        Cari
                    </button>

                    @if(request('search') || request('school_id'))
                    <a href="{{ route('admin.users.index') }}"
                        class="bg-rose-50 text-rose-500 px-4 py-2.5 rounded-xl font-bold hover:bg-rose-500 hover:text-white transition flex items-center justify-center shadow-sm"
                        title="Bersihkan Filter">
                        <i class="fas fa-times"></i>
                    </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Tabel Data --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden min-w-0">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead
                        class="bg-slate-50/50 text-slate-500 text-xs uppercase font-black tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-4 w-12 text-center">
                                <input type="checkbox" x-model="selectAll"
                                    class="rounded border-slate-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 cursor-pointer">
                            </th>
                            <th class="px-6 py-4">Nama & Email</th>
                            <th class="px-6 py-4">Username</th>
                            <th class="px-6 py-4">Nama Sekolah</th>
                            <th class="px-6 py-4 text-center">Peran (Role)</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @forelse($users as $user)
                        <tr class="hover:bg-slate-50/50 transition"
                            :class="{'bg-indigo-50/50': selected.includes('{{ $user->id }}')}">
                            <td class="px-6 py-4 text-center">
                                <input type="checkbox" x-model="selected" value="{{ $user->id }}"
                                    class="rounded border-slate-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 cursor-pointer">
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900 text-base">{{ $user->name }}</div>
                                <div class="text-xs text-slate-500">{{ $user->email ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 font-mono text-indigo-600 bg-indigo-50/30 rounded">
                                {{ $user->username }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-xs text-slate-500">{{ $user->school->name ?? '-' }}</div>
                            </td>

                            {{-- KOLOM ROLE (DROPDOWN AJAX) --}}
                            <td class="px-6 py-4 text-center"
                                x-data="roleManager('{{ $user->id }}', '{{ $user->roles->first()->name ?? 'siswa' }}')">
                                <div class="relative inline-flex items-center justify-center">
                                    <select x-model="currentRole" @change="updateRole()" :disabled="isLoading"
                                        class="appearance-none px-4 py-1.5 rounded-full text-[10px] font-black cursor-pointer border-2 focus:ring-0 outline-none transition-all text-center w-28 uppercase tracking-widest disabled:opacity-50"
                                        :class="{
                                                'bg-rose-50 text-rose-600 border-rose-200': currentRole === 'admin',
                                                'bg-purple-50 text-purple-600 border-purple-200': currentRole === 'operator',
                                                'bg-blue-50 text-blue-600 border-blue-200': currentRole === 'guru',
                                                'bg-emerald-50 text-emerald-600 border-emerald-200': currentRole === 'siswa'
                                            }">
                                        @foreach($roles as $role)
                                        <option value="{{ $role }}" class="bg-white text-slate-800 uppercase">{{ $role
                                            }}</option>
                                        @endforeach
                                    </select>
                                    <i class="fas fa-chevron-down absolute right-3 text-[10px] pointer-events-none"
                                        :class="{
                                                'text-rose-400': currentRole === 'admin',
                                                'text-purple-400': currentRole === 'operator',
                                                'text-blue-400': currentRole === 'guru',
                                                'text-emerald-400': currentRole === 'siswa'
                                            }"></i>
                                    <div x-show="isLoading" class="absolute -right-6" x-cloak>
                                        <i class="fas fa-circle-notch fa-spin text-indigo-500"></i>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.users.edit', $user->id) }}"
                                        class="inline-flex w-8 h-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white transition">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST"
                                        class="m-0" onsubmit="return confirm('Yakin ingin menghapus user ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex w-8 h-8 items-center justify-center rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-500 hover:text-white transition">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 font-bold">
                                <i class="fas fa-inbox text-4xl mb-3 opacity-20 block"></i>
                                Tidak ada data user ditemukan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $users->links() }}
            </div>
            @endif
        </div>
    </div>

    {{-- MODAL IMPORT --}}
    <div x-data="{ isModalOpen: false }" @buka-modal-import.window="isModalOpen = true" x-show="isModalOpen"
        style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center p-4">

        <div x-show="isModalOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
            @click="isModalOpen = false"></div>

        <div x-show="isModalOpen" x-transition.scale.origin.bottom
            class="bg-white rounded-[2rem] shadow-2xl max-w-md w-full relative z-[110] overflow-hidden border border-slate-100">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
                <h3 class="font-black text-slate-800">
                    <i class="fas fa-file-excel text-emerald-500 mr-2"></i> Import Data Excel
                </h3>
                <button @click="isModalOpen = false"
                    class="text-slate-400 hover:text-rose-500 w-8 h-8 rounded-full bg-white flex items-center justify-center shadow-sm">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form action="{{ route('admin.users.import') }}" method="POST" enctype="multipart/form-data"
                class="p-6 space-y-6">
                @csrf
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-wider mb-2">
                        Pilih File (.xlsx, .xls)
                    </label>
                    <input type="file" name="file_excel" accept=".xlsx, .xls, .csv" required
                        class="block w-full text-sm text-slate-500 file:mr-4 file:py-3 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-slate-200 rounded-xl cursor-pointer bg-slate-50">
                </div>
                <div
                    class="bg-amber-50 text-amber-700 p-4 rounded-xl text-xs font-bold leading-relaxed border border-amber-100">
                    <i class="fas fa-info-circle mr-1"></i> Pastikan baris pertama Excel berisi header kolom berikut
                    (huruf kecil):
                    <span
                        class="font-mono bg-white px-2 py-0.5 rounded text-amber-600 block mt-2 border border-amber-200 truncate overflow-hidden">
                        nama | username | email | password | role
                    </span>
                </div>
                <button type="submit"
                    class="w-full bg-emerald-500 hover:bg-emerald-600 text-white py-3.5 rounded-xl font-black transition shadow-lg shadow-emerald-200">
                    <i class="fas fa-upload mr-2"></i> Mulai Proses Import
                </button>
            </form>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            let token = document.head.querySelector('meta[name="csrf-token"]');

            if (token && window.axios) {
                axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
            }

            // GABUNGAN FUNGSI ALPINE UNTUK ROLE DAN USER
            Alpine.data('roleManager', (userId, initialRole) => ({
                currentRole: initialRole,
                isLoading: false,

                updateRole() {
                    this.isLoading = true;

                    axios.post(`/admin/users/${userId}/update-role`, { role: this.currentRole })
                        .then(response => {
                            setTimeout(() => { this.isLoading = false; }, 500);
                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true
                            });
                            Toast.fire({ icon: 'success', title: 'Akses diubah ke: ' + this.currentRole.toUpperCase() });
                        })
                        .catch(error => {
                            let errMsg = 'Gagal mengubah role.';
                            if (error.response?.data?.message) {
                                errMsg = error.response.data.message;
                            }
                            Swal.fire('Gagal!', errMsg, 'error');
                            this.currentRole = initialRole;
                            this.isLoading = false;
                        });
                }
            }));
        });

        function userManager() {
            return {
                selected: [],
                userIdsOnPage: @json($users->pluck('id')->toArray()),

                get selectAll() {
                    return this.userIdsOnPage.length > 0 && this.selected.length === this.userIdsOnPage.length;
                },
                set selectAll(value) {
                    if (value) {
                        this.selected = this.userIdsOnPage.map(String);
                    } else {
                        this.selected = [];
                    }
                },

                deleteSelected() {
                    if (this.selected.length === 0) return;

                    Swal.fire({
                        title: `Hapus ${this.selected.length} User?`,
                        text: "Data yang dipilih akan dihapus secara permanen!",
                        icon: 'warning',
                        background: '#ffffff',
                        color: '#1e293b',
                        showCancelButton: true,
                        confirmButtonColor: '#f43f5e',
                        cancelButtonColor: '#94a3b8',
                        confirmButtonText: 'Ya, Hapus Semua',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            axios.delete('/admin/users/bulk-delete', { data: { ids: this.selected } })
                                .then(res => {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Terhapus!',
                                        text: res.data.message || 'Data berhasil dihapus',
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                })
                                .catch(err => {
                                    let errorMsg = 'Terjadi kesalahan saat menghapus data.';
                                    if (err.response?.data?.message) {
                                        errorMsg = err.response.data.message;
                                    }
                                    Swal.fire('Gagal!', errorMsg, 'error');
                                });
                        }
                    });
                },

                downloadSelected() {
                    if (this.selected.length === 0) return;

                    Swal.fire({
                        title: 'Menyiapkan Unduhan...',
                        text: `Sedang memproses ${this.selected.length} data user.`,
                        icon: 'info',
                        timer: 2000,
                        showConfirmButton: false
                    });

                    const idsParam = this.selected.join(',');
                    window.location.href = `/admin/users/export-selected?ids=${idsParam}`;
                }
            }
        }
    </script>
    @endpush
</x-app-layout>