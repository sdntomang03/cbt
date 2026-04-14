<x-app-layout>
    <div x-data="{ showAddStudentModal: false }" class="py-10 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto relative">
        {{-- ================================================================= --}}
        {{-- AREA NOTIFIKASI SUCCESS & ERROR --}}
        {{-- ================================================================= --}}
        @if(session('success'))
        <div class="mb-8 p-4 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center gap-4 text-emerald-700 font-bold shadow-sm transition-all"
            x-data="{ show: true }" x-show="show" x-transition>
            <div
                class="w-10 h-10 rounded-full bg-emerald-100 flex-shrink-0 flex items-center justify-center text-emerald-500">
                <i class="fas fa-check"></i>
            </div>
            <div class="flex-1">
                {{ session('success') }}
            </div>
            <button @click="show = false" class="text-emerald-400 hover:text-emerald-600 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        @endif

        @if(session('error'))
        <div class="mb-8 p-4 rounded-2xl bg-rose-50 border border-rose-100 flex items-center gap-4 text-rose-700 font-bold shadow-sm transition-all"
            x-data="{ show: true }" x-show="show" x-transition>
            <div
                class="w-10 h-10 rounded-full bg-rose-100 flex-shrink-0 flex items-center justify-center text-rose-500">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="flex-1">
                {{ session('error') }}
            </div>
            <button @click="show = false" class="text-rose-400 hover:text-rose-600 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        @endif
        {{-- ================= END AREA NOTIFIKASI ========================= --}}

        <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.math.index') }}"
                    class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 transition-colors shadow-sm border border-slate-100">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h2 class="text-3xl font-black text-slate-800 tracking-tight">{{ $exam->title }}</h2>
                    <p class="text-slate-500 font-bold mt-1">Rekapitulasi Nilai Ujian Matematika Siswa</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                {{-- TOMBOL TAMBAH SISWA --}}
                <button @click="showAddStudentModal = true"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-xl font-bold shadow-lg transition-transform hover:-translate-y-1 flex items-center gap-2">
                    <i class="fas fa-user-plus"></i> Tambah Siswa
                </button>

                <a href="{{ route('admin.math.recap_export', $exam->id) }}"
                    class="bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-3 rounded-xl font-bold shadow-lg transition-transform hover:-translate-y-1 flex items-center gap-2">
                    <i class="fas fa-file-excel"></i> Export Excel
                </a>

                <button onclick="window.print()"
                    class="bg-slate-800 hover:bg-black text-white px-6 py-3 rounded-xl font-bold shadow-lg transition-transform hover:-translate-y-1 flex items-center gap-2">
                    <i class="fas fa-print"></i> Cetak PDF
                </button>
            </div>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 flex items-center gap-4">
                <div class="w-14 h-14 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center text-2xl">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <span class="block text-xs font-black text-slate-400 uppercase tracking-widest">Selesai /
                        Total</span>
                    <span class="text-2xl font-black text-slate-800">{{ $stats['completed_count'] }} <span
                            class="text-sm text-slate-400">/ {{ $stats['total_students'] }}</span></span>
                </div>
            </div>
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 flex items-center gap-4">
                <div
                    class="w-14 h-14 rounded-full bg-indigo-50 text-indigo-500 flex items-center justify-center text-2xl">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div>
                    <span class="block text-xs font-black text-slate-400 uppercase tracking-widest">Rata-rata</span>
                    <span class="text-2xl font-black text-slate-800">{{ $stats['average_score'] }}</span>
                </div>
            </div>
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 flex items-center gap-4">
                <div
                    class="w-14 h-14 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center text-2xl">
                    <i class="fas fa-arrow-up"></i>
                </div>
                <div>
                    <span class="block text-xs font-black text-slate-400 uppercase tracking-widest">Tertinggi</span>
                    <span class="text-2xl font-black text-emerald-500">{{ $stats['highest_score'] }}</span>
                </div>
            </div>
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 flex items-center gap-4">
                <div class="w-14 h-14 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center text-2xl">
                    <i class="fas fa-arrow-down"></i>
                </div>
                <div>
                    <span class="block text-xs font-black text-slate-400 uppercase tracking-widest">Terendah</span>
                    <span class="text-2xl font-black text-rose-500">{{ $stats['lowest_score'] }}</span>
                </div>
            </div>
        </div>

        {{-- ================================================================= --}}
        {{-- DASHBOARD ANALISIS PENGUASAAN KELAS PER JENIS SOAL --}}
        {{-- ================================================================= --}}
        @php
        $allQuestions = $exam->questions;

        $classStats = collect(['+' => 'Penjumlahan', '-' => 'Pengurangan', 'x' => 'Perkalian', ':' => 'Pembagian'])
        ->map(function ($name, $op) use ($allQuestions) {
        $soal = $allQuestions->where('operator', $op);
        $total = $soal->count();

        if ($total === 0) return null;

        $benar = $soal->where('is_correct', true)->count();
        $persen = round(($benar / $total) * 100);

        $theme = $persen >= 70 ? 'text-emerald-700 bg-emerald-50 border-emerald-200' :
        ($persen >= 40 ? 'text-amber-700 bg-amber-50 border-amber-200' : 'text-rose-700 bg-rose-50 border-rose-200');
        $barColor = $persen >= 70 ? 'bg-emerald-500' : ($persen >= 40 ? 'bg-amber-500' : 'bg-rose-500');
        $icon = $op == '+' ? 'fa-plus' : ($op == '-' ? 'fa-minus' : ($op == 'x' ? 'fa-times' : 'fa-divide'));

        return (object) [
        'name' => $name, 'total' => $total, 'benar' => $benar, 'persen' => $persen,
        'theme' => $theme, 'barColor' => $barColor, 'icon' => $icon
        ];
        })->filter();
        @endphp

        @if($classStats->count() > 0)
        <div class="mb-8">
            <h3 class="font-black text-lg text-slate-800 mb-4 px-2 flex items-center gap-2">
                <i class="fas fa-microscope text-indigo-500"></i> Analisis Penguasaan Materi Kelas
            </h3>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($classStats as $stat)
                <div
                    class="p-5 rounded-2xl border-2 {{ $stat->theme }} flex flex-col relative overflow-hidden shadow-sm transition-transform hover:-translate-y-1">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-xs font-black uppercase tracking-wider opacity-80">{{ $stat->name }}</span>
                        <div class="w-8 h-8 rounded-full bg-white/50 flex items-center justify-center">
                            <i class="fas {{ $stat->icon }} opacity-70"></i>
                        </div>
                    </div>
                    <div>
                        <span class="text-4xl font-black">{{ $stat->persen }}%</span>
                        <span class="block text-[10px] font-bold opacity-70 mt-1">Total Benar: {{ $stat->benar }} dari
                            {{ $stat->total }} soal</span>
                    </div>
                    <div class="w-full bg-black/10 h-2 rounded-full mt-4 overflow-hidden shadow-inner">
                        <div class="{{ $stat->barColor }} h-2 rounded-full transition-all duration-1000"
                            style="width: {{ $stat->persen }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
        {{-- ================= END DASHBOARD ANALISIS ================= --}}

        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                <h3 class="font-black text-lg text-slate-800">Daftar Peserta & Nilai</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider font-black border-b border-slate-100">
                            <th class="p-5 text-center w-16">No</th>
                            <th class="p-5">Nama Siswa</th>
                            <th class="p-5">Sekolah</th>
                            <th class="p-5 text-center">Status</th>
                            <th class="p-5 text-center">Waktu Pengerjaan</th>
                            <th class="p-5 text-center">Nilai Akhir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($exam->examUsers->sortByDesc('score') as $index => $user)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="p-5 text-center font-bold text-slate-400">{{ $loop->iteration }}</td>

                            <td class="p-5">
                                <span class="block font-black text-slate-800">{{ $user->student->name ?? 'Siswa
                                    Terhapus' }}</span>
                            </td>

                            <td class="p-5">
                                <span class="text-xs font-bold text-indigo-500 uppercase">{{
                                    $user->student->school->name ?? 'Pusat' }}</span>
                            </td>

                            <td class="p-5 text-center">
                                @if($user->status === 'not_started')
                                <span
                                    class="bg-slate-100 text-slate-500 px-3 py-1 rounded-full text-xs font-black uppercase">Belum
                                    Mulai</span>
                                @elseif($user->status === 'ongoing')
                                <span
                                    class="bg-amber-100 text-amber-600 px-3 py-1 rounded-full text-xs font-black uppercase animate-pulse">Sedang
                                    Ujian</span>
                                @else
                                <span
                                    class="bg-emerald-100 text-emerald-600 px-3 py-1 rounded-full text-xs font-black uppercase">Selesai</span>
                                @endif
                            </td>

                            <td class="p-5 text-center">
                                @if($user->status === 'completed' && $user->started_at && $user->finished_at)
                                @php
                                $start = \Carbon\Carbon::parse($user->started_at);
                                $finish = \Carbon\Carbon::parse($user->finished_at);

                                $totalSeconds = $start->diffInSeconds($finish);
                                $diffInMinutes = floor($totalSeconds / 60);
                                $diffInSeconds = $totalSeconds % 60;
                                @endphp

                                <span class="text-sm font-bold text-slate-600 bg-slate-100 px-3 py-1 rounded-lg">
                                    <i class="fas fa-stopwatch mr-1 text-slate-400"></i>
                                    {{ $diffInMinutes }}m {{ $diffInSeconds }}s
                                </span>
                                @else
                                <span class="text-slate-300 font-bold">-</span>
                                @endif
                            </td>

                            <td class="p-5 text-center">
                                <div class="mb-3">
                                    @if($user->status === 'completed')
                                    <span
                                        class="text-2xl font-black {{ $user->score >= 70 ? 'text-emerald-500' : 'text-rose-500' }}">
                                        {{ $user->score }}
                                    </span>
                                    @else
                                    <span class="text-slate-300 font-bold text-xl">-</span>
                                    @endif
                                </div>

                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.math.student_result', $user->id) }}"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 text-slate-500 hover:bg-indigo-500 hover:text-white transition-colors"
                                        title="Lihat Lembar Jawaban">
                                        <i class="fas fa-search"></i>
                                    </a>

                                    <form action="{{ route('admin.math.resetStudent', $user->id) }}" method="POST"
                                        class="m-0 p-0"
                                        onsubmit="return confirm('Yakin ingin mereset ujian siswa ini? Semua jawaban sebelumnya akan hilang.');">
                                        @csrf
                                        <button type="submit"
                                            class="btn btn-warning btn-sm inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 text-amber-500 hover:bg-amber-500 hover:text-white transition-colors"
                                            title="Reset Ujian">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="p-10 text-center text-slate-400 font-bold">
                                <i class="fas fa-users-slash text-4xl mb-3 block opacity-30"></i>
                                Belum ada siswa yang ditugaskan untuk ujian ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ================================================================= --}}
        {{-- MODAL TAMBAH SISWA --}}
        {{-- ================================================================= --}}
        <div x-show="showAddStudentModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showAddStudentModal" x-transition.opacity
                    class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm transition-opacity"
                    @click="showAddStudentModal = false" aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showAddStudentModal" x-transition
                    class="inline-block align-bottom bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-slate-100">
                    <form action="{{ route('admin.math.addStudent', $exam->id) }}" method="POST">
                        @csrf
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-8 sm:pb-6">
                            <div class="sm:flex sm:items-start">
                                <div
                                    class="mx-auto flex-shrink-0 flex items-center justify-center h-14 w-14 rounded-full bg-indigo-50 sm:mx-0 sm:h-12 sm:w-12 text-indigo-600 text-xl">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-5 sm:text-left w-full">
                                    <h3 class="text-xl leading-6 font-black text-slate-800" id="modal-title">
                                        Tambah Peserta Ujian
                                    </h3>
                                    <div class="mt-2 mb-6">
                                        <p class="text-sm text-slate-500 font-medium">
                                            Pilih siswa yang akan ditambahkan ke ujian ini. Sistem akan otomatis
                                            mengacak dan membuatkan soal sesuai dengan format/pengaturan ujian.
                                        </p>
                                    </div>

                                    <div class="mt-4">
                                        <label
                                            class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Pilih
                                            Siswa (Bisa Lebih Dari Satu)</label>
                                        <select name="student_ids[]" multiple required
                                            class="w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm h-48 overflow-y-auto">
                                            @forelse($availableStudents as $student)
                                            <option value="{{ $student->id }}"
                                                class="p-2 border-b border-slate-50 hover:bg-indigo-50">
                                                {{ $student->name }} ({{ $student->school->name ?? 'Pusat' }})
                                            </option>
                                            @empty
                                            <option value="" disabled class="p-2 text-slate-400">Semua siswa sudah
                                                terdaftar di ujian ini.</option>
                                            @endforelse
                                        </select>
                                        <p class="mt-2 text-xs text-slate-500 font-medium"><i
                                                class="fas fa-info-circle text-indigo-400 mr-1"></i> Tahan tombol <kbd
                                                class="bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200 font-mono text-[10px]">Ctrl</kbd>
                                            (Windows) atau <kbd
                                                class="bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200 font-mono text-[10px]">Cmd</kbd>
                                            (Mac) untuk memilih banyak siswa sekaligus.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div
                            class="bg-slate-50 px-4 py-4 sm:px-8 sm:flex sm:flex-row-reverse border-t border-slate-100 gap-2">
                            <button type="submit"
                                class="w-full inline-flex justify-center items-center rounded-xl border border-transparent shadow-lg shadow-indigo-200 px-6 py-3 bg-indigo-600 text-base font-bold text-white hover:bg-indigo-700 hover:-translate-y-0.5 transition-all sm:ml-3 sm:w-auto sm:text-sm">
                                <i class="fas fa-save mr-2"></i> Simpan
                            </button>
                            <button type="button" @click="showAddStudentModal = false"
                                class="mt-3 w-full inline-flex justify-center items-center rounded-xl border border-slate-300 shadow-sm px-6 py-3 bg-white text-base font-bold text-slate-700 hover:bg-slate-50 transition-all sm:mt-0 sm:w-auto sm:text-sm">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        {{-- ================= END MODAL TAMBAH SISWA ====================== --}}

        <style>
            [x-cloak] {
                display: none !important;
            }

            @media print {
                body * {
                    visibility: hidden;
                }

                .max-w-7xl,
                .max-w-7xl * {
                    visibility: visible;
                }

                .max-w-7xl {
                    position: absolute;
                    left: 0;
                    top: 0;
                    width: 100%;
                    padding: 0;
                }

                button,
                a {
                    display: none !important;
                }

                .shadow-sm,
                .shadow-lg {
                    box-shadow: none !important;
                }

                .bg-white {
                    background-color: transparent !important;
                }

                .rounded-\[2rem\] {
                    border-radius: 0 !important;
                    border: none !important;
                }

                table,
                th,
                td {
                    border: 1px solid #e2e8f0;
                }
            }
        </style>
    </div>
</x-app-layout>