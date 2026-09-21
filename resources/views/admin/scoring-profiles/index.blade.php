<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4">
            <div>
                <h2 class="font-black text-2xl text-slate-800 tracking-tight">Profil Scoring</h2>
                <p class="text-sm text-slate-500 font-bold">Kelola aturan penilaian untuk ujian dan section.</p>
            </div>
            <button type="button" onclick="openScoringModal()"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700">
                <i class="fas fa-plus"></i> Profil Baru
            </button>
        </div>
    </x-slot>

    <div class="py-8 px-4 sm:px-6 lg:px-8">
        @if(session('success'))
        <div class="mb-6 rounded-xl border-l-4 border-emerald-500 bg-emerald-50 p-4 text-emerald-700 font-bold">
            {{ session('success') }}
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
                            <th class="p-4">Kode</th>
                            <th class="p-4">Nama</th>
                            <th class="p-4">Sekolah</th>
                            <th class="p-4">Aturan</th>
                            <th class="p-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm font-bold text-slate-700">
                        @forelse($profiles as $profile)
                        @php($rules = $profile->rules ?? [])
                        <tr class="hover:bg-slate-50">
                            <td class="p-4 font-mono text-indigo-600">{{ $profile->code }}</td>
                            <td class="p-4 text-slate-800">{{ $profile->name }}</td>
                            <td class="p-4">{{ $profile->school?->name ?? 'Global' }}</td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 text-xs">
                                    {{ ($rules['type'] ?? 'standard') === 'weighted' ? 'Berbobot' : 'Standar' }}
                                </span>
                                <span class="ml-1 px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 text-xs">
                                    {{ ($rules['result_mode'] ?? 'average') === 'total' ? 'Total Poin' : 'Rata-rata' }}
                                </span>
                                <span class="ml-2 text-xs text-slate-500">
                                    B {{ $rules['correct'] ?? 1 }} / S {{ $rules['wrong'] ?? 0 }} / K {{ $rules['empty'] ?? 0 }}
                                </span>
                            </td>
                            <td class="p-4 text-right whitespace-nowrap">
                                <button type="button" onclick='editScoringProfile(@json($profile))'
                                    class="px-3 py-2 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('admin.scoring-profiles.destroy', $profile) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Hapus profil scoring ini?')">
                                    @csrf @method('DELETE')
                                    <button class="px-3 py-2 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="p-10 text-center text-slate-400">Belum ada profil scoring.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="scoring-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl">
            <div class="flex justify-between items-center p-6 border-b">
                <h3 id="scoring-modal-title" class="font-black text-xl text-slate-800">Profil Scoring Baru</h3>
                <button type="button" onclick="closeScoringModal()" class="text-slate-400 hover:text-rose-500 text-xl"><i class="fas fa-times"></i></button>
            </div>
            <form id="scoring-form" method="POST" class="p-6 space-y-4">
                @csrf
                <input id="scoring-method" type="hidden" name="_method" value="POST">
                <div class="grid sm:grid-cols-2 gap-4">
                    <label class="block"><span class="label">Kode</span><input id="scoring-code" name="code" required class="input" placeholder="TKA"></label>
                    <label class="block"><span class="label">Nama</span><input id="scoring-name" name="name" required class="input" placeholder="Penilaian TKA"></label>
                </div>
                @if(auth()->user()->hasRole('admin'))
                <label class="block"><span class="label">Sekolah</span>
                    <select id="scoring-school" name="school_id" class="input">
                        <option value="">Global</option>
                        @foreach($schools as $school)<option value="{{ $school->id }}">{{ $school->name }}</option>@endforeach
                    </select>
                </label>
                @endif
                <label class="block"><span class="label">Jenis Scoring</span>
                    <select id="scoring-type" name="scoring_type" class="input">
                        <option value="standard">Standar</option><option value="weighted">Berbobot</option>
                    </select>
                </label>
                <label class="block"><span class="label">Tampilan Hasil</span>
                    <select id="scoring-result-mode" name="result_mode" class="input">
                        <option value="average">Nilai rata-rata / persentase</option>
                        <option value="total">Total perolehan poin</option>
                    </select>
                    <span class="text-xs text-slate-400 font-semibold">Gunakan total poin untuk model seleksi seperti CPNS.</span>
                </label>
                <div class="grid sm:grid-cols-3 gap-4">
                    <label class="block"><span class="label">Benar</span><input id="scoring-correct" name="correct" type="number" step="0.01" required class="input" value="1"></label>
                    <label class="block"><span class="label">Salah</span><input id="scoring-wrong" name="wrong" type="number" step="0.01" required class="input" value="0"></label>
                    <label class="block"><span class="label">Kosong</span><input id="scoring-empty" name="empty" type="number" step="0.01" required class="input" value="0"></label>
                </div>
                <div class="pt-2 flex justify-end gap-3">
                    <button type="button" onclick="closeScoringModal()" class="px-5 py-2.5 rounded-xl font-bold bg-slate-100 text-slate-600">Batal</button>
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
        const scoringModal = document.getElementById('scoring-modal');
        const scoringForm = document.getElementById('scoring-form');
        function openScoringModal() {
            scoringForm.reset();
            scoringForm.action = '{{ route('admin.scoring-profiles.store') }}';
            document.getElementById('scoring-method').value = 'POST';
            document.getElementById('scoring-modal-title').textContent = 'Profil Scoring Baru';
            scoringModal.classList.remove('hidden'); scoringModal.classList.add('flex');
        }
        function editScoringProfile(profile) {
            const rules = profile.rules || {};
            scoringForm.action = `/admin/scoring-profiles/${profile.id}`;
            document.getElementById('scoring-method').value = 'PUT';
            document.getElementById('scoring-modal-title').textContent = 'Edit Profil Scoring';
            document.getElementById('scoring-code').value = profile.code || '';
            document.getElementById('scoring-name').value = profile.name || '';
            document.getElementById('scoring-type').value = rules.type || 'standard';
            document.getElementById('scoring-result-mode').value = rules.result_mode || 'average';
            document.getElementById('scoring-correct').value = rules.correct ?? 1;
            document.getElementById('scoring-wrong').value = rules.wrong ?? 0;
            document.getElementById('scoring-empty').value = rules.empty ?? 0;
            const school = document.getElementById('scoring-school');
            if (school) school.value = profile.school_id || '';
            scoringModal.classList.remove('hidden'); scoringModal.classList.add('flex');
        }
        function closeScoringModal() { scoringModal.classList.add('hidden'); scoringModal.classList.remove('flex'); }
    </script>
</x-app-layout>
