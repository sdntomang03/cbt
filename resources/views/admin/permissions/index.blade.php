<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-4">
                <div
                    class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-600 to-purple-600 flex items-center justify-center shadow-lg text-white shrink-0">
                    <i class="fas fa-fingerprint text-xl"></i>
                </div>
                <div>
                    <h2 class="font-black text-2xl text-slate-800 tracking-tight">Manajemen Permission</h2>
                    <p class="text-sm text-slate-500 font-bold">Daftar label hak akses spesifik di sistem</p>
                </div>
            </div>

            <button x-data @click="$dispatch('open-modal', { mode: 'add' })"
                class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-indigo-600 text-white rounded-xl font-bold text-sm transition-all hover:bg-indigo-700 shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-plus"></i>
                <span>Tambah Permission</span>
            </button>
        </div>
    </x-slot>

    {{-- BUNGKUS UTAMA ALPINE.JS (Semua modal harus di dalam sini) --}}
    {{-- PERBAIKAN: Menggunakan py-4 md:py-6 --}}
    <div class="py-4 md:py-6 min-h-screen" x-data="permissionManager()">

        {{-- PERBAIKAN: Menggunakan w-full 2xl:max-w-screen-2xl agar lebarnya seragam --}}
        <div class="w-full 2xl:max-w-screen-2xl mx-auto px-3 sm:px-4 lg:px-6 space-y-6">

            {{-- Alerts --}}
            @if(session('success'))
            <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-xl shadow-sm flex justify-between items-center"
                x-data="{show: true}" x-show="show">
                <p class="text-emerald-700 font-bold"><i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                </p>
                <button @click="show = false" class="text-emerald-500 hover:text-emerald-700"><i
                        class="fas fa-times"></i></button>
            </div>
            @endif
            @if(session('error'))
            <div class="bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r-xl shadow-sm flex justify-between items-center"
                x-data="{show: true}" x-show="show">
                <p class="text-rose-700 font-bold"><i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error')
                    }}
                </p>
                <button @click="show = false" class="text-rose-500 hover:text-rose-700"><i
                        class="fas fa-times"></i></button>
            </div>
            @endif

            {{-- Toolbar / Search --}}
            <div class="bg-white p-4 sm:p-5 rounded-2xl shadow-sm border border-slate-100 flex justify-end">
                <form method="GET" action="{{ route('admin.permissions.index') }}" class="flex w-full sm:w-80 gap-2">
                    <div class="relative w-full">
                        <i class="fas fa-search absolute left-4 top-3 text-slate-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari nama permission..."
                            class="w-full pl-10 pr-4 py-2 bg-slate-50 border-transparent rounded-xl focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition font-bold text-slate-600 text-sm">
                    </div>
                    <button type="submit"
                        class="bg-slate-900 text-white px-4 py-2 rounded-xl font-bold hover:bg-slate-800 transition">Cari</button>
                </form>
            </div>

            {{-- Tabel Permission --}}
            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead
                            class="bg-slate-50/50 text-slate-500 text-[10px] uppercase font-black tracking-widest border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-5 w-16 text-center rounded-tl-[2rem]">No</th>
                                <th class="px-6 py-5 w-full">Nama Permission</th>
                                <th class="px-6 py-5 text-center w-1">Pengguna (Role)</th>
                                <th class="px-6 py-5 text-right rounded-tr-[2rem] w-1">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 font-medium text-slate-700">
                            @forelse($permissions as $index => $permission)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-4 text-center text-slate-400 font-bold">
                                    {{ $permissions->firstItem() + $index }}
                                </td>
                                <td class="px-6 py-4 min-w-[200px]">
                                    <span
                                        class="bg-indigo-50 border border-indigo-100 text-indigo-700 font-black px-3 py-1.5 rounded-lg text-xs tracking-wider">
                                        {{ $permission->name }}
                                    </span>
                                    @if(in_array($permission->name, ['manage users', 'manage schools', 'create exams',
                                    'edit exams', 'delete exams', 'take exams', 'view reports']))
                                    <span
                                        class="ml-2 text-[10px] text-amber-500 font-black tracking-widest uppercase"><i
                                            class="fas fa-lock opacity-70 mr-1"></i> Sistem Inti</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span
                                        class="bg-slate-100 text-slate-500 text-xs font-bold px-3 py-1.5 rounded-lg border border-slate-200">
                                        {{ $permission->roles->count() }} Role Memiliki Akses
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        {{-- Tombol Lihat User --}}
                                        <button @click="fetchUsers('{{ $permission->id }}', '{{ $permission->name }}')"
                                            class="inline-flex w-8 h-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition"
                                            title="Lihat Daftar User">
                                            <i class="fas fa-users"></i>
                                        </button>

                                        <button @click="$dispatch('open-modal', {
                                                mode: 'edit',
                                                id: '{{ $permission->id }}',
                                                name: '{{ $permission->name }}'
                                            })"
                                            class="inline-flex w-8 h-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white transition"
                                            title="Edit Permission">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        @if(!in_array($permission->name, ['manage users', 'manage schools', 'create
                                        exams', 'edit exams', 'delete exams', 'take exams', 'view reports']))
                                        <form action="{{ route('admin.permissions.destroy', $permission->id) }}"
                                            method="POST" class="inline-block"
                                            onsubmit="return confirm('Hapus Permission ini permanen?');">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition"
                                                title="Hapus Permission">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-16 text-center text-slate-400 font-bold">
                                    <div
                                        class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-fingerprint text-3xl text-slate-300"></i>
                                    </div>
                                    <h3 class="text-lg font-black text-slate-700">Tidak Ada Permission</h3>
                                    <p class="text-sm mt-1">Sistem belum menemukan data permission.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($permissions->hasPages())
            <div class="mt-6">
                {{ $permissions->links() }}
            </div>
            @endif

        </div> {{-- AKHIR BUNGKUS LEBAR KONTEN --}}

        {{-- MODAL TAMBAH & EDIT PERMISSION --}}
        <div x-data="{
                isOpen: false,
                mode: 'add',
                permissionId: '',
                permissionName: ''
            }" @open-modal.window="
                isOpen = true;
                mode = $event.detail.mode;
                if(mode === 'edit') {
                    permissionId = $event.detail.id;
                    permissionName = $event.detail.name;
                } else {
                    permissionId = '';
                    permissionName = '';
                }
            " x-show="isOpen" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4">

            <div x-show="isOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
                @click="isOpen = false"></div>

            <div x-show="isOpen" x-transition.scale.origin.bottom
                class="bg-white rounded-[2rem] shadow-2xl max-w-md w-full relative z-50 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <div>
                        <h3 class="font-black text-lg text-slate-800"
                            x-text="mode === 'edit' ? 'Edit Permission' : 'Buat Permission Baru'"></h3>
                    </div>
                    <button @click="isOpen = false"
                        class="text-slate-400 hover:text-rose-500 w-8 h-8 rounded-full bg-white shadow-sm flex items-center justify-center transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form
                    :action="mode === 'edit' ? `/admin/permissions/${permissionId}` : '{{ route('admin.permissions.store') }}'"
                    method="POST">
                    @csrf
                    <template x-if="mode === 'edit'">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="p-6">
                        <div class="mb-4">
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Nama
                                Hak Akses</label>
                            <input type="text" name="name" x-model="permissionName" required
                                placeholder="Contoh: export excel"
                                class="w-full bg-slate-50 border-transparent focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 rounded-xl font-bold text-slate-700 py-3.5 px-4 transition-colors"
                                :readonly="['manage users', 'manage schools', 'create exams', 'edit exams', 'delete exams', 'take exams', 'view reports'].includes(permissionName) && mode === 'edit'"
                                :class="['manage users', 'manage schools', 'create exams', 'edit exams', 'delete exams', 'take exams', 'view reports'].includes(permissionName) && mode === 'edit' ? 'opacity-60 cursor-not-allowed' : ''">

                            <p class="text-[10px] text-slate-500 font-bold mt-2 ml-1">
                                <i class="fas fa-info-circle text-indigo-400 mr-1"></i> Disarankan menggunakan format
                                lowercase (huruf kecil).
                            </p>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                        <button type="button" @click="isOpen = false"
                            class="px-5 py-2.5 rounded-xl font-bold text-slate-500 hover:bg-slate-200 transition-colors">Batal</button>
                        <button type="submit"
                            class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-black shadow-lg shadow-indigo-200 transition-all active:scale-95 flex items-center gap-2">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL DAFTAR USER BERDASARKAN PERMISSION --}}
        <div x-show="isUserModalOpen" style="display: none;"
            class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div x-show="isUserModalOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
                @click="isUserModalOpen = false"></div>

            <div x-show="isUserModalOpen" x-transition.scale.origin.bottom
                class="bg-white rounded-[2rem] shadow-2xl max-w-lg w-full relative z-[70] overflow-hidden flex flex-col max-h-[85vh]">

                {{-- Header Modal User --}}
                <div
                    class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50 shrink-0">
                    <div>
                        <h3 class="font-black text-lg text-slate-800">Daftar Pengguna</h3>
                        <p class="text-[10px] font-bold text-indigo-600 uppercase tracking-widest mt-0.5">
                            Akses: <span x-text="currentPermName"></span>
                        </p>
                    </div>
                    <button @click="isUserModalOpen = false"
                        class="text-slate-400 hover:text-rose-500 w-8 h-8 rounded-full bg-white shadow-sm border border-slate-100 flex items-center justify-center transition-colors shrink-0">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                {{-- Body Modal User --}}
                <div class="p-0 overflow-y-auto custom-scrollbar flex-1 bg-slate-50/30">
                    <div x-show="isLoading" class="flex flex-col items-center justify-center py-16 text-slate-400">
                        <i class="fas fa-circle-notch fa-spin text-4xl mb-4 text-indigo-500"></i>
                        <span class="font-bold text-sm">Mengambil data pengguna...</span>
                    </div>

                    <ul x-show="!isLoading" class="divide-y divide-slate-100">
                        <template x-for="user in usersList" :key="user.id">
                            <li class="p-4 hover:bg-white transition-colors flex items-center gap-4">
                                <div
                                    class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-black shrink-0">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <div class="font-black text-slate-800 text-sm" x-text="user.name"></div>
                                    <div class="text-xs font-bold text-slate-500"
                                        x-text="user.school + ' • ' + user.username"></div>
                                </div>
                            </li>
                        </template>

                        <li x-show="!isLoading && usersList.length === 0" class="p-12 text-center">
                            <div
                                class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center text-2xl mx-auto mb-3">
                                <i class="fas fa-user-slash"></i>
                            </div>
                            <span class="font-bold text-slate-500 text-sm block">Belum ada user yang memiliki hak akses
                                ini.</span>
                        </li>
                    </ul>
                </div>

                {{-- Footer Modal User --}}
                <div class="px-6 py-4 bg-white border-t border-slate-100 flex justify-between items-center shrink-0">
                    <span class="text-xs font-bold text-slate-500">
                        Total: <span x-text="usersList.length" class="text-indigo-600 font-black"></span> Pengguna
                    </span>
                    <button @click="isUserModalOpen = false"
                        class="px-6 py-2 rounded-xl font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors text-sm">Tutup</button>
                </div>
            </div>
        </div>

    </div> {{-- AKHIR BUNGKUS UTAMA ALPINE.JS --}}

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        function permissionManager() {
            return {
                isUserModalOpen: false,
                isLoading: false,
                currentPermName: '',
                usersList: [],

                fetchUsers(id, name) {
                    this.currentPermName = name;
                    this.isUserModalOpen = true;
                    this.isLoading = true;
                    this.usersList = [];

                    axios.get(`/admin/permissions/${id}/users`)
                        .then(response => {
                            this.usersList = response.data.users;
                        })
                        .catch(error => {
                            Swal.fire('Error', 'Gagal mengambil data pengguna dari server.', 'error');
                            this.isUserModalOpen = false;
                        })
                        .finally(() => {
                            this.isLoading = false;
                        });
                }
            }
        }
    </script>
    @endpush
</x-app-layout>