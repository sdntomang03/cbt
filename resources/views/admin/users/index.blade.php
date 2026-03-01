<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-4">
                <div
                    class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-600 to-violet-600 flex items-center justify-center shadow-lg text-white">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <div>
                    <h2 class="font-black text-2xl text-slate-800 tracking-tight">Manajemen User</h2>
                    <p class="text-sm text-slate-500 font-bold">Kelola data siswa, guru, dan admin</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3" x-data>
                <button @click="$dispatch('open-import-modal')"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl font-semibold text-sm transition-all duration-200 hover:bg-slate-50 hover:border-slate-300 active:scale-95 shadow-sm">
                    <i class="fas fa-file-excel text-emerald-500"></i>
                    <span>Import Excel</span>
                </button>

                <a href="{{ route('admin.users.download-template') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-slate-500 hover:text-emerald-600 rounded-xl font-medium text-sm transition-colors duration-200">
                    <i class="fas fa-download"></i>
                    <span>Download Format</span>
                </a>

                <div class="hidden md:block h-6 w-px bg-slate-200 mx-1"></div>

                <a href="{{ route('admin.users.create') }}"
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 text-white rounded-xl font-bold text-sm transition-all duration-200 hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-200 active:transform active:scale-95 shadow-md">
                    <i class="fas fa-plus"></i>
                    <span>Tambah User</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6" x-data="{ importModal: false }"
        @open-import-modal.window="importModal = true">

        {{-- Alert Success --}}
        @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 transform translate-x-0"
            x-transition:leave-end="opacity-0 transform translate-x-8"
            class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-xl shadow-sm flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <i class="fas fa-check-circle text-emerald-500 text-xl"></i>
                <p class="text-emerald-700 font-bold">{{ session('success') }}</p>
            </div>
            <button @click="show = false"
                class="text-emerald-400 hover:text-emerald-700 transition w-8 h-8 rounded-full flex items-center justify-center hover:bg-emerald-100">
                <i class="fas fa-times"></i>
            </button>
        </div>
        @endif

        {{-- Alert Error --}}
        @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 7000)"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 transform translate-x-0"
            x-transition:leave-end="opacity-0 transform translate-x-8"
            class="bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r-xl shadow-sm flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <i class="fas fa-exclamation-circle text-rose-500 text-xl"></i>
                <p class="text-rose-700 font-bold">{{ session('error') }}</p>
            </div>
            <button @click="show = false"
                class="text-rose-400 hover:text-rose-700 transition w-8 h-8 rounded-full flex items-center justify-center hover:bg-rose-100">
                <i class="fas fa-times"></i>
            </button>
        </div>
        @endif

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex justify-between items-center">
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex justify-between items-center">
                <form method="GET" action="{{ route('admin.users.index') }}"
                    class="flex flex-wrap w-full md:max-w-4xl gap-3">

                    {{-- DROPDOWN FILTER SEKOLAH (MUNCUL HANYA UNTUK ROLE ADMIN) --}}
                    @if(auth()->user()->hasRole('admin'))
                    <div class="relative flex-1 min-w-[200px]">
                        <select name="school_id" onchange="this.form.submit()"
                            class="w-full bg-slate-50 border-transparent rounded-xl focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition py-2.5 pl-4 pr-10 font-bold text-slate-600 appearance-none">
                            <option value="">-- Semua Sekolah --</option>
                            @foreach($schools as $school)
                            <option value="{{ $school->id }}" {{ request('school_id')==$school->id ? 'selected' : '' }}>
                                {{ $school->name }}
                            </option>
                            @endforeach
                        </select>
                        <i
                            class="fas fa-chevron-down absolute right-4 top-4 text-xs text-slate-400 pointer-events-none"></i>
                    </div>
                    @endif

                    {{-- INPUT PENCARIAN --}}
                    <div class="relative flex-[2] min-w-[250px]">
                        <i class="fas fa-search absolute left-4 top-3.5 text-slate-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari nama atau username/NISN..."
                            class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border-transparent rounded-xl focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition font-bold text-slate-600">
                    </div>

                    <button type="submit"
                        class="bg-slate-900 text-white px-8 py-2.5 rounded-xl font-bold hover:bg-slate-800 transition shadow-lg shadow-slate-200">
                        Cari
                    </button>

                    {{-- TOMBOL RESET --}}
                    @if(request('search') || request('school_id'))
                    <a href="{{ route('admin.users.index') }}"
                        class="bg-rose-50 text-rose-500 px-4 py-2.5 rounded-xl font-bold hover:bg-rose-500 hover:text-white transition flex items-center shadow-sm"
                        title="Bersihkan Filter">
                        <i class="fas fa-times"></i>
                    </a>
                    @endif
                </form>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-slate-50/50 text-slate-500 text-xs uppercase font-black tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Nama & Email</th>
                            <th class="px-6 py-4">Username / NISN</th>
                            <th class="px-6 py-4">Nama Sekolah</th>
                            <th class="px-6 py-4 text-center">Peran</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">

                        @forelse($users as $user)

                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900 text-base">{{ $user->name }}</div>
                                <div class="text-xs text-slate-500">{{ $user->email ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 font-mono text-indigo-600 bg-indigo-50/30 rounded">{{ $user->username
                                }}</td>
                            <td class="px-6 py-4">

                                <div class="text-xs text-slate-500">{{ $user->school->name ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($user->hasRole('admin'))
                                <span
                                    class="bg-rose-100 text-rose-600 px-3 py-1 rounded-full text-xs font-black uppercase">Admin</span>
                                @elseif($user->hasRole('guru'))
                                <span
                                    class="bg-blue-100 text-blue-600 px-3 py-1 rounded-full text-xs font-black uppercase">Guru</span>
                                @else
                                <span
                                    class="bg-emerald-100 text-emerald-600 px-3 py-1 rounded-full text-xs font-black uppercase">Siswa</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('admin.users.edit', $user->id) }}"
                                    class="inline-flex w-8 h-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white transition">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST"
                                    class="inline-block" onsubmit="return confirm('Yakin ingin menghapus user ini?');">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="w-8 h-8 items-center justify-center rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-500 hover:text-white transition">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-400 font-bold">
                                <i class="fas fa-inbox text-4xl mb-3 opacity-20 block"></i> Tidak ada data user
                                ditemukan.
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

        <div x-show="importModal" style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div x-show="importModal" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
                @click="importModal = false"></div>

            <div x-show="importModal" x-transition.scale.origin.bottom
                class="bg-white rounded-[2rem] shadow-2xl max-w-md w-full relative z-10 overflow-hidden border-4 border-white">
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="font-black text-slate-800"><i class="fas fa-file-excel text-emerald-500 mr-2"></i> Import
                        Data Excel</h3>
                    <button @click="importModal = false"
                        class="text-slate-400 hover:text-rose-500 w-8 h-8 rounded-full bg-white flex items-center justify-center shadow-sm"><i
                            class="fas fa-times"></i></button>
                </div>

                <form action="{{ route('admin.users.import') }}" method="POST" enctype="multipart/form-data"
                    class="p-6 space-y-6">
                    @csrf
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-wider mb-2">Pilih File
                            (.xlsx, .xls)</label>
                        <input type="file" name="file_excel" accept=".xlsx, .xls, .csv" required
                            class="block w-full text-sm text-slate-500 file:mr-4 file:py-3 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-slate-200 rounded-xl cursor-pointer bg-slate-50">
                    </div>
                    <div
                        class="bg-amber-50 text-amber-700 p-4 rounded-xl text-xs font-bold leading-relaxed border border-amber-100">
                        <i class="fas fa-info-circle mr-1"></i> Pastikan baris pertama Excel berisi tulisan persis
                        seperti ini (huruf kecil):
                        <span
                            class="font-mono bg-white px-2 py-0.5 rounded text-amber-600 block mt-2 border border-amber-200">nama
                            | username | email | password | role</span>
                    </div>
                    <button type="submit"
                        class="w-full bg-emerald-500 hover:bg-emerald-600 text-white py-3.5 rounded-xl font-black transition shadow-lg shadow-emerald-200">
                        <i class="fas fa-upload mr-2"></i> Mulai Proses Import
                    </button>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>