<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.users.index') }}"
                class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-sm text-slate-500 hover:text-indigo-600 transition">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h2 class="font-black text-2xl text-slate-800 tracking-tight">Edit Data User</h2>
        </div>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
            <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="p-8 space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2">Nama
                            Lengkap</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                            class="w-full bg-slate-50 border-transparent focus:border-indigo-500 focus:bg-white focus:ring-0 rounded-xl font-bold text-slate-700 py-3 px-4">
                        @error('name') <p class="text-rose-500 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- DROPDOWN SEKOLAH: HANYA MUNCUL UNTUK SUPER ADMIN --}}
                    @if(auth()->user()->hasRole('admin'))
                    <div>
                        <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2">Asal
                            Sekolah</label>
                        <div class="relative">
                            <select name="school_id" required
                                class="w-full bg-slate-50 border-transparent focus:border-indigo-500 focus:bg-white focus:ring-0 rounded-xl font-bold text-slate-700 py-3 pl-4 pr-10 appearance-none">
                                <option value="">-- Pilih Sekolah --</option>
                                @foreach($schools as $school)
                                <option value="{{ $school->id }}" {{ old('school_id', $user->school_id) == $school->id ?
                                    'selected' : '' }}>
                                    {{ $school->name }}
                                </option>
                                @endforeach
                            </select>
                            <i
                                class="fas fa-chevron-down absolute right-4 top-4 text-xs text-slate-400 pointer-events-none"></i>
                        </div>
                        @error('school_id') <p class="text-rose-500 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                    </div>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label
                            class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2">Username
                            / NISN</label>
                        <input type="text" name="username" value="{{ old('username', $user->username) }}" required
                            class="w-full bg-slate-50 border-transparent focus:border-indigo-500 focus:bg-white focus:ring-0 rounded-xl font-bold text-slate-700 py-3 px-4">
                        @error('username') <p class="text-rose-500 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label
                            class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}"
                            class="w-full bg-slate-50 border-transparent focus:border-indigo-500 focus:bg-white focus:ring-0 rounded-xl font-bold text-slate-700 py-3 px-4">
                        @error('email') <p class="text-rose-500 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2">Peran
                            (Role)</label>
                        <select name="role" required
                            class="w-full bg-slate-50 border-transparent focus:border-indigo-500 focus:bg-white focus:ring-0 rounded-xl font-bold text-slate-700 py-3 px-4">
                            @php $currentRole = $user->roles->first()->name ?? 'student'; @endphp
                            <option value="student" {{ old('role', $currentRole)=='student' ? 'selected' : '' }}>Siswa
                            </option>
                            <option value="teacher" {{ old('role', $currentRole)=='teacher' ? 'selected' : '' }}>Guru
                            </option>
                            <option value="admin" {{ old('role', $currentRole)=='admin' ? 'selected' : '' }}>Admin
                            </option>
                        </select>
                        @error('role') <p class="text-rose-500 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2">Ganti
                            Password <span class="text-rose-400 normal-case font-medium">*Kosongkan jika tidak
                                diganti</span></label>
                        <input type="password" name="password" minlength="6" placeholder="******"
                            class="w-full bg-slate-50 border-transparent focus:border-indigo-500 focus:bg-white focus:ring-0 rounded-xl font-bold text-slate-700 py-3 px-4 placeholder:font-normal">
                        @error('password') <p class="text-rose-500 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-100 flex justify-end">
                    <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3.5 rounded-xl font-black transition shadow-xl shadow-indigo-200 flex items-center gap-2">
                        <i class="fas fa-check"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>