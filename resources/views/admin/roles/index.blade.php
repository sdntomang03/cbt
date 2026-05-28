<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-4">
                <div
                    class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-600 to-blue-600 flex items-center justify-center shadow-lg text-white shrink-0">
                    <i class="fas fa-key text-xl"></i>
                </div>
                <div>
                    <h2 class="font-black text-2xl text-slate-800 tracking-tight">Role & Permission</h2>
                    <p class="text-sm text-slate-500 font-bold">Atur hak akses untuk masing-class jabatan</p>
                </div>
            </div>

            <button x-data @click="$dispatch('open-modal', { mode: 'add' })"
                class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-indigo-600 text-white rounded-xl font-bold text-sm transition-all hover:bg-indigo-700 shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-plus"></i>
                <span>Buat Role Baru</span>
            </button>
        </div>
    </x-slot>

    <div class="py-8 mx-auto px-4 sm:px-6 lg:px-8 space-y-6" x-data="roleManager()">

        {{-- Alert Notification --}}
        @if(session('success'))
        <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-xl shadow-sm mb-4">
            <p class="text-emerald-700 font-bold"><i class="fas fa-check-circle mr-2"></i> {{ session('success') }}</p>
        </div>
        @endif
        @if(session('error'))
        <div class="bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r-xl shadow-sm mb-4">
            <p class="text-rose-700 font-bold"><i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
            </p>
        </div>
        @endif

        {{-- Grid Daftar Role --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach($roles as $role)
            <div
                class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col h-full hover:shadow-md transition-shadow relative overflow-hidden group">

                {{-- Proteksi Badge untuk Role Inti --}}
                @if(in_array($role->name, ['admin', 'guru', 'siswa', 'operator']))
                <div
                    class="absolute -right-8 top-4 bg-amber-100 text-amber-700 text-[10px] font-black uppercase tracking-widest px-8 py-1 rotate-45 shadow-sm">
                    Sistem
                </div>
                @endif

                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-black text-slate-800 uppercase tracking-wider">{{ $role->name }}</h3>
                    <span class="bg-slate-100 text-slate-500 text-xs font-bold px-3 py-1 rounded-full">
                        {{ $role->permissions->count() }} Akses
                    </span>
                </div>

                <div class="flex-1 mb-6">
                    <div class="flex flex-wrap gap-2">
                        @forelse($role->permissions->take(8) as $permission)
                        <span
                            class="bg-indigo-50 border border-indigo-100 text-indigo-600 text-[10px] font-bold px-2.5 py-1 rounded-md">
                            {{ $permission->name }}
                        </span>
                        @empty
                        <span class="text-xs text-slate-400 font-bold italic">Belum ada hak akses.</span>
                        @endforelse

                        @if($role->permissions->count() > 8)
                        <span class="bg-slate-50 text-slate-500 text-[10px] font-bold px-2.5 py-1 rounded-md">
                            +{{ $role->permissions->count() - 8 }} lainnya
                        </span>
                        @endif
                    </div>
                </div>

                <div class="flex gap-2 pt-4 border-t border-slate-100 mt-auto">
                    <button @click="$dispatch('open-modal', {
                                mode: 'edit',
                                id: '{{ $role->id }}',
                                name: '{{ $role->name }}',
                                permissions: {{ json_encode($role->permissions->pluck('name')) }}
                            })"
                        class="flex-1 bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white py-2 rounded-xl text-sm font-bold transition-colors">
                        <i class="fas fa-user-edit mr-1"></i> Edit Akses
                    </button>

                    @if(!in_array($role->name, ['admin', 'guru', 'siswa', 'operator']))
                    <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" class="inline-block"
                        onsubmit="return confirm('Hapus Role ini secara permanen?');">
                        @csrf @method('DELETE')
                        <button type="submit"
                            class="w-10 h-10 flex items-center justify-center bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white rounded-xl transition-colors">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        {{-- MODAL TAMBAH & EDIT ROLE --}}
        <div x-data="{
                isOpen: false,
                mode: 'add',
                roleId: '',
                roleName: '',
                selectedPermissions: []
            }" @open-modal.window="
                isOpen = true;
                mode = $event.detail.mode;
                if(mode === 'edit') {
                    roleId = $event.detail.id;
                    roleName = $event.detail.name;
                    selectedPermissions = $event.detail.permissions;
                } else {
                    roleId = '';
                    roleName = '';
                    selectedPermissions = [];
                }
            " x-show="isOpen" style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6">

            <div x-show="isOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
                @click="isOpen = false"></div>

            <div x-show="isOpen" x-transition.scale.origin.bottom
                class="bg-white rounded-[2rem] shadow-2xl max-w-3xl w-full relative z-50 flex flex-col max-h-[90vh]">

                {{-- Header Modal --}}
                <div
                    class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50 rounded-t-[2rem]">
                    <h3 class="font-black text-xl text-slate-800"
                        x-text="mode === 'edit' ? 'Edit Hak Akses Role' : 'Buat Role Baru'"></h3>
                    <button @click="isOpen = false"
                        class="text-slate-400 hover:text-rose-500 w-8 h-8 rounded-full bg-white shadow-sm flex items-center justify-center transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                {{-- Body Form --}}
                <form :action="mode === 'edit' ? `/admin/roles/${roleId}` : '{{ route('admin.roles.store') }}'"
                    method="POST" class="flex flex-col overflow-hidden">
                    @csrf
                    <template x-if="mode === 'edit'">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="p-6 overflow-y-auto custom-scrollbar flex-1">
                        {{-- Input Nama Role --}}
                        <div class="mb-6">
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Nama
                                Role</label>
                            <input type="text" name="name" x-model="roleName" required placeholder="Contoh: pengawas"
                                class="w-full bg-slate-50 border-transparent focus:border-indigo-500 focus:bg-white focus:ring-0 rounded-xl font-bold text-slate-700 py-3 px-4"
                                :readonly="['admin', 'guru', 'siswa', 'operator'].includes(roleName) && mode === 'edit'"
                                :class="['admin', 'guru', 'siswa', 'operator'].includes(roleName) && mode === 'edit' ? 'opacity-60 cursor-not-allowed' : ''">
                            <p x-show="['admin', 'guru', 'siswa', 'operator'].includes(roleName) && mode === 'edit'"
                                class="text-[10px] text-amber-500 font-bold mt-1">
                                <i class="fas fa-lock"></i> Nama role inti tidak dapat diubah. Anda hanya bisa mengubah
                                hak aksesnya.
                            </p>
                        </div>

                        {{-- Pilihan Permissions --}}
                        <div>
                            <label
                                class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3 border-b border-slate-100 pb-2">Pilih
                                Hak Akses (Permissions)</label>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($permissions as $permission)
                                <label
                                    class="relative flex items-center p-3 rounded-xl border-2 cursor-pointer transition-all"
                                    :class="selectedPermissions.includes('{{ $permission->name }}') ? 'bg-indigo-50 border-indigo-500 shadow-sm' : 'bg-white border-slate-100 hover:border-indigo-200'">

                                    <div class="flex items-center h-5">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                            x-model="selectedPermissions"
                                            class="w-5 h-5 text-indigo-600 bg-slate-100 border-slate-300 rounded focus:ring-indigo-500 focus:ring-2">
                                    </div>
                                    <div class="ml-3">
                                        <span class="block text-sm font-bold"
                                            :class="selectedPermissions.includes('{{ $permission->name }}') ? 'text-indigo-900' : 'text-slate-700'">
                                            {{ $permission->name }}
                                        </span>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Footer Action --}}
                    <div
                        class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 rounded-b-[2rem]">
                        <button type="button" @click="isOpen = false"
                            class="px-6 py-2.5 rounded-xl font-bold text-slate-500 hover:bg-slate-200 transition-colors">Batal</button>
                        <button type="submit"
                            class="px-8 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-black shadow-lg shadow-indigo-200 transition-all active:scale-95 flex items-center gap-2">
                            <i class="fas fa-save"></i> <span
                                x-text="mode === 'edit' ? 'Simpan Perubahan' : 'Buat Role'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        function roleManager() {
            return {
                // State logika dikelola langsung di dalam x-data modal
            }
        }
    </script>
    @endpush
</x-app-layout>