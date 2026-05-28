<x-app-layout>
    {{-- Area Navigasi Header --}}
    <x-slot name="header">
        <div class="flex justify-between items-end w-full px-4 sm:px-6 lg:px-8">
            <div>
                <h2 class="font-extrabold text-2xl text-slate-800 tracking-tight leading-none">Manajemen Ujian</h2>
                <p class="text-[10px] text-slate-400 mt-2 font-black uppercase tracking-widest">Kelola Bank Soal & Sesi
                    Ujian</p>
            </div>
            <button @click="$store.examModule.newExam()"
                class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl hover:bg-indigo-700 text-sm font-black shadow-lg shadow-indigo-200 transition-all flex items-center gap-2 active:scale-95">
                <i class="fas fa-plus"></i> <span>Ujian Baru</span>
            </button>
        </div>
    </x-slot>

    <div class="py-8 px-4 sm:px-6 lg:px-8 w-full">

        {{-- Navigasi Jenis Ujian (Tabs) & Tombol Tipe Baru --}}
        <div class="flex items-center gap-3 mb-8 overflow-x-auto pb-2 custom-scrollbar">
            @foreach($examTypes as $type)
            <a href="{{ route('admin.exams.index', ['exam_type_id' => $type->id]) }}"
                class="px-5 py-2.5 rounded-xl text-sm font-black transition-all shrink-0 {{ $activeTypeId == $type->id ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'bg-white text-slate-500 border border-slate-200 hover:border-indigo-300' }}">
                <i class="fas fa-tag mr-2 {{ $activeTypeId == $type->id ? 'text-indigo-200' : 'text-slate-300' }}"></i>
                {{ $type->name }}
            </a>
            @endforeach

            {{-- TOMBOL TIPE BARU --}}
            <button @click="$store.examModule.newType()"
                class="px-5 py-2.5 rounded-xl border-2 border-dashed border-slate-300 text-slate-500 hover:border-indigo-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all text-sm font-black shrink-0 flex items-center gap-2">
                <i class="fas fa-plus-circle"></i> Tipe Baru
            </button>
        </div>

        {{-- Alert Notifikasi --}}
        @if(session('success'))
        <div
            class="alert-box mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl font-bold flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3"><i class="fas fa-check-circle text-xl"></i> {{ session('success') }}
            </div>
            <button type="button" onclick="this.closest('.alert-box').remove()"
                class="text-emerald-400 hover:text-emerald-600 transition"><i class="fas fa-times"></i></button>
        </div>
        @endif

        {{-- Tabel Data Ujian --}}
        <div class="bg-white shadow-sm sm:rounded-[2rem] border border-slate-100 overflow-hidden">
            <div class="p-6 border-b border-slate-50 bg-slate-50/30">
                <h3 class="font-black text-slate-700 text-sm uppercase tracking-wider">
                    Daftar Ujian: <span class="text-indigo-600">{{ $examTypes->where('id',
                        $activeTypeId)->first()?->name ?? 'Pilih Kategori' }}</span>
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr
                            class="bg-slate-50/50 border-b border-slate-100 text-[10px] uppercase tracking-widest text-slate-400 font-black">
                            <th class="px-6 py-4">Informasi Ujian</th>
                            <th class="px-6 py-4 text-center">Durasi</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 text-sm font-bold">
                        @forelse ($exams as $exam)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-black text-slate-800 text-base">{{ $exam->title }}</div>
                                <div
                                    class="text-[10px] text-slate-400 font-mono mt-0.5 tracking-tight uppercase italic">
                                    {{ $exam->slug }}</div>

                                {{-- Menampilkan Badges Level dan Subject di Tabel --}}
                                <div class="mt-2 text-[10px] flex items-center gap-2 flex-wrap">
                                    <span class="text-indigo-500 bg-indigo-50 px-2 py-0.5 rounded-md">
                                        <i class="fas fa-question-circle"></i> {{ $exam->questions_count }} Soal
                                    </span>
                                    <span class="text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md">
                                        <i class="fas fa-layer-group"></i> {{ $exam->level->name ?? 'Umum' }}
                                    </span>
                                    <span class="text-amber-600 bg-amber-50 px-2 py-0.5 rounded-md">
                                        <i class="fas fa-book"></i> {{ $exam->subject->name ?? 'Umum' }}
                                    </span>
                                    <span class="text-slate-400 ml-1">
                                        <i class="fas fa-user-edit"></i> {{ $exam->teacher->name ?? 'Guru' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center text-slate-600">
                                <i class="far fa-clock text-slate-300 mr-1"></i> {{ $exam->duration_minutes }}m
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span
                                    class="px-3 py-1 rounded-full text-[10px] font-black uppercase {{ $exam->status->value == 'published' ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-400' }}">
                                    {{ $exam->status->value }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.exams.soal.index', $exam) }}"
                                        class="p-2 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-600 hover:text-white transition"
                                        title="Input Soal">
                                        <i class="fas fa-plus-square"></i>
                                    </a>
                                    <button @click="$store.examModule.editExam({{ $exam->toJson() }})"
                                        class="p-2 bg-amber-50 text-amber-600 rounded-lg hover:bg-amber-500 hover:text-white transition"
                                        title="Edit Pengaturan Ujian">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('admin.exams.destroy', $exam) }}" method="POST"
                                        onsubmit="return confirm('Hapus ujian ini beserta seluruh soalnya?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="p-2 bg-rose-50 text-rose-600 rounded-lg hover:bg-rose-600 hover:text-white transition"
                                            title="Hapus Ujian">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-20 text-center">
                                <i class="fas fa-clipboard-list text-4xl text-slate-200 mb-4 block"></i>
                                <p class="text-slate-400 font-bold italic">Belum ada ujian di kategori ini.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100">{{ $exams->links() }}</div>
        </div>
    </div>

    {{-- MODAL CRUD UJIAN --}}
    <div x-data x-show="$store.examModule.openModal" class="fixed inset-0 z-[100] overflow-y-auto" x-cloak x-transition>
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div @click="$store.examModule.openModal = false"
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

            <div
                class="inline-block bg-white rounded-[2.5rem] overflow-hidden shadow-2xl transform transition-all w-full max-w-xl z-[110] border border-slate-100 text-left align-middle">
                <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <h3 class="text-xl font-black text-slate-800"
                        x-text="$store.examModule.isEdit ? 'Update Konfigurasi Ujian' : 'Buat Ujian Baru'"></h3>
                    <button type="button" @click="$store.examModule.openModal = false"
                        class="text-slate-300 hover:text-rose-500 transition">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form :action="$store.examModule.actionUrl" method="POST" class="p-8 space-y-6">
                    @csrf
                    <template x-if="$store.examModule.isEdit"><input type="hidden" name="_method"
                            value="PUT"></template>

                    <input type="hidden" name="exam_type_id" x-model="$store.examModule.formData.exam_type_id">

                    <div>
                        <label
                            class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Judul
                            Ujian</label>
                        <input type="text" name="title" x-model="$store.examModule.formData.title" required
                            class="w-full rounded-2xl border-slate-200 focus:ring-indigo-500 font-bold text-slate-700 py-3.5 px-4 shadow-sm">
                    </div>

                    {{-- TAMBAHAN: Dropdown Level & Mata Pelajaran --}}
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Tingkat
                                / Kelas</label>
                            <select name="level_id" x-model="$store.examModule.formData.level_id" required
                                class="w-full rounded-2xl border-slate-200 focus:ring-indigo-500 font-bold text-slate-700 py-3.5 px-4 shadow-sm bg-slate-50 text-sm">
                                <option value="">-- Pilih Level --</option>
                                @foreach($levels as $level)
                                <option value="{{ $level->id }}">{{ $level->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Mata
                                Pelajaran</label>
                            <select name="subject_id" x-model="$store.examModule.formData.subject_id" required
                                class="w-full rounded-2xl border-slate-200 focus:ring-indigo-500 font-bold text-slate-700 py-3.5 px-4 shadow-sm bg-slate-50 text-sm">
                                <option value="">-- Pilih Mapel --</option>
                                @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Jenis
                                (Kategori)</label>
                            <input type="text" disabled
                                value="{{ $examTypes->where('id', $activeTypeId)->first()?->name }}"
                                class="w-full rounded-2xl border-slate-100 bg-slate-50 text-slate-400 font-bold py-3.5 px-4 text-sm">
                        </div>
                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Durasi
                                (Menit)</label>
                            <input type="number" name="duration_minutes"
                                x-model="$store.examModule.formData.duration_minutes" required
                                class="w-full rounded-2xl border-slate-200 focus:ring-indigo-500 font-bold text-slate-700 py-3.5 px-4 shadow-sm">
                        </div>

                    </div>

                    <div class="grid grid-cols-2 gap-6 items-center">
                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Status
                                Publikasi</label>
                            <select name="status" x-model="$store.examModule.formData.status"
                                class="w-full rounded-2xl border-slate-200 font-bold text-slate-700 py-3.5 px-4 bg-slate-50 uppercase text-xs">
                                @foreach(\App\Enums\ExamStatus::cases() as $status)
                                <option value="{{ $status->value }}">{{ $status->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex flex-col gap-2 pt-2">
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="checkbox" name="random_question"
                                    :checked="$store.examModule.formData.random_question"
                                    @change="$store.examModule.formData.random_question = $event.target.checked"
                                    class="rounded text-indigo-600 border-slate-300 w-5 h-5">
                                <span
                                    class="text-[10px] font-black text-slate-500 uppercase group-hover:text-indigo-600 transition">Acak
                                    Soal</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="checkbox" name="random_answer"
                                    :checked="$store.examModule.formData.random_answer"
                                    @change="$store.examModule.formData.random_answer = $event.target.checked"
                                    class="rounded text-indigo-600 border-slate-300 w-5 h-5">
                                <span
                                    class="text-[10px] font-black text-slate-500 uppercase group-hover:text-indigo-600 transition">Acak
                                    Jawaban</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="checkbox" name="show_explanation"
                                    :checked="$store.examModule.formData.show_explanation"
                                    @change="$store.examModule.formData.show_explanation = $event.target.checked"
                                    class="rounded text-indigo-600 border-slate-300 w-5 h-5">
                                <span
                                    class="text-[10px] font-black text-slate-500 uppercase group-hover:text-indigo-600 transition">Tampilkan
                                    Pembahasan</span>
                            </label>
                        </div>
                    </div>

                    <div class="pt-8 flex justify-end gap-4 border-t border-slate-50">
                        <button type="button" @click="$store.examModule.openModal = false"
                            class="px-6 py-3 text-slate-400 font-black rounded-2xl hover:bg-slate-50 transition">Batal</button>
                        <button type="submit"
                            class="px-10 py-3 bg-indigo-600 text-white font-black rounded-2xl hover:bg-indigo-700 shadow-xl shadow-indigo-100 transition-all active:scale-95">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL TAMBAH TIPE UJIAN --}}
    <div x-data x-show="$store.examModule.openTypeModal" class="fixed inset-0 z-[100] overflow-y-auto" x-cloak
        x-transition>
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div @click="$store.examModule.openTypeModal = false"
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

            <div
                class="inline-block bg-white rounded-[2.5rem] overflow-hidden shadow-2xl transform transition-all w-full max-w-md z-[110] border border-slate-100 text-left align-middle">
                <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <h3 class="text-xl font-black text-slate-800">Tambah Tipe Ujian</h3>
                    <button type="button" @click="$store.examModule.openTypeModal = false"
                        class="text-slate-300 hover:text-rose-500 transition">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form action="{{ route('admin.exam-types.store') }}" method="POST" class="p-8">
                    @csrf
                    <div>
                        <label
                            class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Nama
                            Tipe (Misal: PTS, PAS)</label>
                        <input type="text" name="name" x-model="$store.examModule.typeFormData.name" required
                            placeholder="Contoh: Ulangan Harian"
                            class="w-full rounded-2xl border-slate-200 focus:ring-indigo-500 font-bold text-slate-700 py-3.5 px-4 shadow-sm">
                    </div>

                    <div class="pt-8 flex justify-end gap-3 mt-2">
                        <button type="button" @click="$store.examModule.openTypeModal = false"
                            class="px-6 py-3 text-slate-400 font-black rounded-2xl hover:bg-slate-50 transition">Batal</button>
                        <button type="submit"
                            class="px-8 py-3 bg-indigo-600 text-white font-black rounded-2xl hover:bg-indigo-700 shadow-xl shadow-indigo-100 transition-all active:scale-95">Simpan
                            Tipe</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- SCRIPT ALPINE GLOBAL STORE --}}
    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('examModule', {
                // State Modal Ujian Utama
                openModal: false,
                isEdit: false,
                actionUrl: '',
                formData: {
                    title: '',
                    exam_type_id: '{{ $activeTypeId }}',
                    level_id: '',
                    subject_id: '',
                    duration_minutes: 60,
                    status: 'draft',
                    random_question: false,
                    show_explanation: false,
                    random_answer: false
                },
                newExam() {
                    this.isEdit = false;
                    this.actionUrl = '{{ route('admin.exams.store') }}';
                    this.formData = {
                        title: '',
                        exam_type_id: '{{ $activeTypeId }}',
                        level_id: '',
                        subject_id: '',
                        duration_minutes: 60,
                        status: 'draft',
                        random_question: false,
                        show_explanation: false,
                        random_answer: false
                    };
                    this.openModal = true;
                },
                editExam(exam) {
                    this.isEdit = true;
                    this.actionUrl = `/admin/exams/${exam.id}`;
                    this.formData = {
                        title: exam.title,
                        exam_type_id: exam.exam_type_id,
                        level_id: exam.level_id || '',
                        subject_id: exam.subject_id || '',
                        duration_minutes: exam.duration_minutes,
                        status: exam.status,
                        random_question: !!exam.random_question,
                        show_explanation: !!exam.show_explanation,
                        random_answer: !!exam.random_answer
                    };
                    this.openModal = true;
                },

                // State Modal Tipe Ujian
                openTypeModal: false,
                typeFormData: {
                    name: ''
                },
                newType() {
                    this.typeFormData.name = '';
                    this.openTypeModal = true;
                }
            });
        });
    </script>
    @endpush
</x-app-layout>