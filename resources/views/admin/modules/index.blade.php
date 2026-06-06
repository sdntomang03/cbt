<x-app-layout>
    <div class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- HEADER DASHBOARD --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl font-black text-slate-800 tracking-tight">Manajemen Modul Belajar</h1>
                    <p class="text-sm font-medium text-slate-500 mt-1">Kelola materi literasi, numerasi, dan TKA untuk
                        bimbingan belajar siswa.</p>
                </div>
                <div>
                    <a href="{{ route('admin.modules.create') }}"
                        class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-5 rounded-xl text-sm transition-all shadow-md shadow-indigo-100 transform hover:-translate-y-0.5">
                        <i class="fas fa-plus text-xs"></i> Tambah Modul Baru
                    </a>
                </div>
            </div>

            {{-- NOTIFIKASI SUKSES (FLASH MESSAGE) --}}
            @if(session('success'))
            <div
                class="bg-emerald-50 border-l-4 border-emerald-500 p-4 mb-6 rounded-r-xl shadow-sm flex items-center gap-3">
                <i class="fas fa-check-circle text-emerald-500 text-lg"></i>
                <p class="text-sm text-emerald-700 font-bold">{{ session('success') }}</p>
            </div>
            @endif

            {{-- KARTU UTAMA TABEL --}}
            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="p-5 text-xs font-black text-slate-400 uppercase tracking-wider w-[40%]">
                                    Informasi Modul</th>
                                <th class="p-5 text-xs font-black text-slate-400 uppercase tracking-wider">Kategori</th>
                                <th class="p-5 text-xs font-black text-slate-400 uppercase tracking-wider text-center">
                                    Akses</th>
                                <th class="p-5 text-xs font-black text-slate-400 uppercase tracking-wider text-center">
                                    Status</th>
                                <th class="p-5 text-xs font-black text-slate-400 uppercase tracking-wider text-center">
                                    Statistik</th>
                                <th class="p-5 text-xs font-black text-slate-400 uppercase tracking-wider text-right">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @if($modules->isEmpty())
                            <tr>
                                <td colspan="6" class="p-12 text-center">
                                    <div
                                        class="w-16 h-16 bg-slate-50 text-slate-400 rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
                                        <i class="fas fa-book-open text-slate-300"></i>
                                    </div>
                                    <h3 class="text-base font-bold text-slate-700">Belum Ada Modul</h3>
                                    <p class="text-xs text-slate-400 mt-1">Silakan klik tombol "Tambah Modul Baru" untuk
                                        membuat materi pertama.</p>
                                </td>
                            </tr>
                            @else
                            @foreach($modules as $module)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="p-5">
                                    <div class="flex items-center gap-4">
                                        {{-- Mini Cover --}}
                                        <div
                                            class="w-14 h-14 rounded-xl bg-slate-100 flex-shrink-0 overflow-hidden border border-slate-200 flex items-center justify-center">
                                            @if($module->thumbnail)
                                            <img src="{{ asset('storage/' . $module->thumbnail) }}"
                                                class="w-full h-full object-cover">
                                            @else
                                            <i class="fas fa-book text-slate-400 text-lg"></i>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <h4 class="font-black text-slate-800 text-sm truncate">{{ $module->title }}
                                            </h4>
                                            <p class="text-xs text-slate-400 mt-1 truncate max-w-xs">{{
                                                $module->description ?? 'Tidak ada deskripsi singkat.' }}</p>
                                            <div
                                                class="flex items-center gap-3 mt-2 text-[10px] font-bold text-slate-400">
                                                <span><i class="far fa-user mr-1"></i> {{ $module->author->name ??
                                                    'Admin' }}</span>
                                                <span><i class="far fa-clock mr-1"></i> {{
                                                    $module->estimated_time_minutes }} mnt</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-5">
                                    <div class="flex flex-col gap-1.5">
                                        <span
                                            class="inline-block px-2 py-0.5 text-[10px] font-black text-blue-700 bg-blue-50 border border-blue-100 rounded-md w-fit uppercase">{{
                                            $module->subject->name }}</span>
                                        <span
                                            class="inline-block px-2 py-0.5 text-[10px] font-black text-orange-700 bg-orange-50 border border-orange-100 rounded-md w-fit uppercase">{{
                                            $module->level->name }}</span>
                                    </div>
                                </td>
                                <td class="p-5 text-center">
                                    @if($module->is_premium)
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-1 text-[10px] font-black text-amber-700 bg-amber-50 border border-amber-200 rounded-full uppercase tracking-wider shadow-sm">
                                        <i class="fas fa-crown text-amber-500"></i> Premium
                                    </span>
                                    @else
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-1 text-[10px] font-black text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-full uppercase tracking-wider shadow-sm">
                                        <i class="fas fa-globe text-emerald-500"></i> Gratis
                                    </span>
                                    @endif
                                </td>
                                <td class="p-5 text-center">
                                    @if($module->status === 'published')
                                    <span
                                        class="px-2.5 py-1 text-[10px] font-bold text-emerald-700 bg-emerald-100/70 rounded-full uppercase">Aktif</span>
                                    @elseif($module->status === 'draft')
                                    <span
                                        class="px-2.5 py-1 text-[10px] font-bold text-slate-500 bg-slate-100 rounded-full uppercase">Draft</span>
                                    @else
                                    <span
                                        class="px-2.5 py-1 text-[10px] font-bold text-rose-700 bg-rose-100 rounded-full uppercase">Arsip</span>
                                    @endif
                                </td>
                                <td class="p-5 text-center">
                                    <div class="text-xs font-bold text-slate-600">
                                        <span class="block text-sm font-black text-slate-800">{{
                                            number_format($module->view_count) }}</span>
                                        <span class="text-[10px] text-slate-400 uppercase">Dilihat</span>
                                    </div>
                                </td>
                                <td class="p-5 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.modules.edit', $module) }}"
                                            class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-indigo-50 text-slate-600 hover:text-indigo-600 transition flex items-center justify-center border border-slate-200 hover:border-indigo-200"
                                            title="Ubah Modul">
                                            <i class="fas fa-pencil-alt text-sm"></i>
                                        </a>
                                        <form action="{{ route('admin.modules.destroy', $module) }}" method="POST"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus modul ini secara permanen?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-600 transition flex items-center justify-center border border-slate-200 border-transparent hover:border-rose-200"
                                                title="Hapus Modul">
                                                <i class="fas fa-trash-alt text-sm"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>

                {{-- PAGINATION --}}
                @if($modules->hasPages())
                <div class="p-5 border-t border-slate-100 bg-slate-50/50">
                    {{ $modules->links() }}
                </div>
                @endif

            </div>

        </div>
    </div>
</x-app-layout>