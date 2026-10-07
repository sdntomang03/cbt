<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4">
            <div>
                <h2 class="font-black text-2xl text-slate-800 tracking-tight">Tahun Pelajaran</h2>
                <p class="text-sm text-slate-500 font-bold">{{ $school->name }} &middot; Kelola tahun pelajaran sekolah.</p>
            </div>
            <button type="button" onclick="openAcademicYearModal()"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700">
                <i class="fas fa-plus"></i> Tahun Pelajaran Baru
            </button>
        </div>
    </x-slot>

    <div class="py-8 px-4 sm:px-6 lg:px-8">
        @if(session('success'))
        <div class="mb-6 rounded-xl border-l-4 border-emerald-500 bg-emerald-50 p-4 text-emerald-700 font-bold">
            {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="mb-6 rounded-xl border-l-4 border-rose-500 bg-rose-50 p-4 text-rose-700 font-bold">
            {{ session('error') }}
        </div>
        @endif
        @if($errors->any())
        <div class="mb-6 rounded-xl border-l-4 border-rose-500 bg-rose-50 p-4 text-rose-700">
            <p class="font-black mb-1">Data belum dapat disimpan:</p>
            <ul class="list-disc list-inside text-sm font-bold">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 text-[10px] uppercase tracking-widest text-slate-400 font-black">
                        <tr>
                            <th class="p-4">Nama Tahun Pelajaran</th>
                            <th class="p-4">Status</th>
                            <th class="p-4">Jumlah Kelas</th>
                            <th class="p-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm font-bold text-slate-700">
                        @forelse($academicYears as $academicYear)
                        <tr class="hover:bg-slate-50">
                            <td class="p-4 text-slate-800">{{ $academicYear->name }}</td>
                            <td class="p-4">
                                @if($academicYear->is_active)
                                <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-xs">Aktif</span>
                                @else
                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-500 text-xs">Nonaktif</span>
                                @endif
                            </td>
                            <td class="p-4">{{ $academicYear->classrooms_count }}</td>
                            <td class="p-4 text-right whitespace-nowrap">
                                <button type="button"
                                    data-id="{{ $academicYear->id }}"
                                    data-name="{{ $academicYear->name }}"
                                    data-active="{{ $academicYear->is_active ? '1' : '0' }}"
                                    onclick="editAcademicYear(this)"
                                    class="px-3 py-2 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white"
                                    aria-label="Edit {{ $academicYear->name }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('admin.academic-years.destroy', $academicYear) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Hapus tahun pelajaran {{ $academicYear->name }}?')">
                                    @csrf @method('DELETE')
                                    <button class="px-3 py-2 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white"
                                        aria-label="Hapus {{ $academicYear->name }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="p-10 text-center text-slate-400">Belum ada tahun pelajaran.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="academic-year-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl">
            <div class="flex justify-between items-center p-6 border-b">
                <h3 id="academic-year-modal-title" class="font-black text-xl text-slate-800">Tahun Pelajaran Baru</h3>
                <button type="button" onclick="closeAcademicYearModal()" class="text-slate-400 hover:text-rose-500 text-xl"
                    aria-label="Tutup"><i class="fas fa-times"></i></button>
            </div>
            <form id="academic-year-form" method="POST" action="{{ route('admin.academic-years.store') }}" class="p-6 space-y-4">
                @csrf
                <input id="academic-year-method" type="hidden" name="_method" value="POST">
                <input id="academic-year-id" type="hidden" name="academic_year_id" value="{{ old('academic_year_id') }}">
                <label class="block">
                    <span class="label">Nama Tahun Pelajaran</span>
                    <input id="academic-year-name" name="name" value="{{ old('name') }}" required maxlength="255"
                        class="input" placeholder="Contoh: 2026/2027">
                </label>
                <input type="hidden" name="is_active" value="0">
                <label class="flex items-center gap-3 font-bold text-slate-700">
                    <input id="academic-year-active" type="checkbox" name="is_active" value="1"
                        @checked(old('is_active') == '1')
                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    Jadikan tahun pelajaran aktif
                </label>
                <p class="text-xs text-slate-400 font-semibold">Hanya satu tahun pelajaran yang dapat aktif di sekolah ini.</p>
                <div class="pt-2 flex justify-end gap-3">
                    <button type="button" onclick="closeAcademicYearModal()"
                        class="px-5 py-2.5 rounded-xl font-bold bg-slate-100 text-slate-600">Batal</button>
                    <button class="px-5 py-2.5 rounded-xl font-bold bg-indigo-600 text-white hover:bg-indigo-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .label { display:block; margin-bottom:.5rem; font-size:.7rem; font-weight:900; text-transform:uppercase; letter-spacing:.08em; color:#94a3b8 }
        .input { width:100%; border:1px solid #e2e8f0; border-radius:.75rem; padding:.65rem .8rem; font-weight:700 }
    </style>
    <script>
        const academicYearModal = document.getElementById('academic-year-modal');
        const academicYearForm = document.getElementById('academic-year-form');
        const academicYearStoreUrl = @json(route('admin.academic-years.store'));
        const academicYearBaseUrl = @json(url('/admin/academic-years'));

        function openAcademicYearModal() {
            academicYearForm.reset();
            academicYearForm.action = academicYearStoreUrl;
            document.getElementById('academic-year-method').value = 'POST';
            document.getElementById('academic-year-id').value = '';
            document.getElementById('academic-year-modal-title').textContent = 'Tahun Pelajaran Baru';
            academicYearModal.classList.remove('hidden');
            academicYearModal.classList.add('flex');
        }

        function editAcademicYear(button) {
            academicYearForm.action = `${academicYearBaseUrl}/${button.dataset.id}`;
            document.getElementById('academic-year-method').value = 'PUT';
            document.getElementById('academic-year-id').value = button.dataset.id;
            document.getElementById('academic-year-modal-title').textContent = 'Edit Tahun Pelajaran';
            document.getElementById('academic-year-name').value = button.dataset.name;
            document.getElementById('academic-year-active').checked = button.dataset.active === '1';
            academicYearModal.classList.remove('hidden');
            academicYearModal.classList.add('flex');
        }

        function closeAcademicYearModal() {
            academicYearModal.classList.add('hidden');
            academicYearModal.classList.remove('flex');
        }

        @if($errors->any())
            @if(old('academic_year_id'))
                academicYearForm.action = `${academicYearBaseUrl}/${@json(old('academic_year_id'))}`;
                document.getElementById('academic-year-method').value = 'PUT';
                document.getElementById('academic-year-modal-title').textContent = 'Edit Tahun Pelajaran';
            @endif
            academicYearModal.classList.remove('hidden');
            academicYearModal.classList.add('flex');
        @endif
    </script>
</x-app-layout>
