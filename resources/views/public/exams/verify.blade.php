<x-public-layout>
    <div class="min-h-screen bg-slate-50 py-12 px-4 sm:px-6 lg:px-8 flex items-center justify-center">
        <div class="max-w-4xl w-full bg-white rounded-[2rem] shadow-xl overflow-hidden flex flex-col md:flex-row">

            {{-- KIRI: Info Ujian + Token --}}
            <div
                class="bg-indigo-600 w-full md:w-5/12 p-10 text-white flex flex-col justify-between relative overflow-hidden">
                <div
                    class="absolute top-[-20%] left-[-20%] w-64 h-64 bg-indigo-500 rounded-full mix-blend-multiply filter blur-3xl opacity-50">
                </div>
                <div
                    class="absolute bottom-[-10%] right-[-10%] w-48 h-48 bg-purple-500 rounded-full mix-blend-multiply filter blur-3xl opacity-50">
                </div>

                <div class="relative z-10">
                    <div
                        class="w-16 h-16 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center text-3xl mb-6 shadow-inner border border-white/20">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <h2 class="text-3xl font-black mb-2 tracking-tight">Verifikasi Peserta</h2>
                    <p class="text-indigo-200 text-sm font-medium leading-relaxed">
                        Silakan lengkapi data diri Anda dengan benar. Data ini akan digunakan untuk sertifikat dan
                        perangkingan skala nasional.
                    </p>
                </div>

                <div class="relative z-10 mt-10 space-y-4">
                    {{-- Info Ujian --}}
                    <div class="bg-indigo-900/30 p-5 rounded-xl border border-white/10 backdrop-blur-sm">
                        <div class="text-xs text-indigo-300 font-bold uppercase tracking-wider mb-1">Ujian yang dipilih
                        </div>
                        <div class="font-black text-lg truncate">{{ $exam->title }}</div>
                        <div class="flex items-center gap-4 mt-3 text-sm font-medium text-indigo-200">
                            <div><i class="fas fa-stopwatch mr-1 text-indigo-400"></i> {{ $exam->duration_minutes }}
                                Menit</div>
                            <div><i class="fas fa-list-ul mr-1 text-indigo-400"></i> {{ $exam->questions()->count() }}
                                Soal</div>
                        </div>
                    </div>

                    <div class="bg-white p-5 rounded-xl shadow-lg">
                        <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-2">
                            <i class="fas fa-key mr-1 text-indigo-400"></i> Token Ujian
                        </div>

                        <div class="font-black text-3xl text-indigo-600 tracking-widest font-mono" id="token-display">
                            {{ $token }}
                        </div>

                        <p class="text-[11px] text-slate-400 mt-3">
                            <i class="fas fa-pencil-alt mr-1"></i> Ketik token ini secara manual di kolom sebelah kanan
                        </p>
                    </div>
                </div>
            </div>

            {{-- KANAN: Form --}}
            <div class="w-full md:w-7/12 p-10">

                @if(session('error'))
                <div class="bg-rose-50 border-l-4 border-rose-500 p-4 mb-6 rounded-r-lg">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-rose-500 mr-3"></i>
                        <p class="text-sm text-rose-700 font-bold">{{ session('error') }}</p>
                    </div>
                </div>
                @endif

                <form action="{{ route('public.exams.process_verify', $exam) }}" method="POST" class="space-y-5"
                    id="verifyForm">
                    @csrf

                    <div>
                        <label for="nama_peserta" class="block text-sm font-bold text-slate-700 mb-2">Nama Lengkap
                            Peserta</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-user text-slate-400"></i>
                            </div>
                            <input type="text" name="nama_peserta" id="nama_peserta" required
                                value="{{ old('nama_peserta') }}"
                                class="pl-11 w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-3.5 transition-colors"
                                placeholder="Ketikkan nama lengkap Anda">
                        </div>
                    </div>

                    <div>
                        <label for="asal_sekolah" class="block text-sm font-bold text-slate-700 mb-2">Asal
                            Sekolah</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-school text-slate-400"></i>
                            </div>
                            <input type="text" name="asal_sekolah" id="asal_sekolah" required
                                value="{{ old('asal_sekolah') }}"
                                class="pl-11 w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-3.5 transition-colors"
                                placeholder="Contoh: SDN Tomang 03 Pagi">
                        </div>
                    </div>

                    {{-- Input Token --}}
                    <div>
                        <label for="token" class="block text-sm font-bold text-slate-700 mb-2">
                            Ketik Ulang Token Ujian <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-key text-slate-400"></i>
                            </div>
                            <input type="text" name="token" id="token" required autocomplete="off"
                                maxlength="{{ strlen($token) }}"
                                class="pl-11 pr-11 w-full bg-amber-50 border border-amber-200 text-slate-800 text-sm rounded-xl focus:ring-amber-500 focus:border-amber-500 block p-3.5 font-bold tracking-widest uppercase transition-colors"
                                placeholder="Ketik token dari kotak kiri" style="text-transform: uppercase">
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none"
                                id="token-status-icon"></div>
                        </div>
                        <p class="text-xs mt-1.5 hidden" id="token-feedback"></p>
                    </div>

                    <div class="pt-4 border-t border-slate-100 mt-6">
                        <button type="submit" id="btnSubmit" disabled
                            class="w-full text-white bg-slate-300 font-bold rounded-xl text-sm px-5 py-4 text-center transition-all flex items-center justify-center gap-2 cursor-not-allowed">
                            Mulai Mengerjakan <i class="fas fa-arrow-right text-xs"></i>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script>
        const correctToken = "{{ $token }}";
const tokenInput   = document.getElementById('token');
const btnSubmit    = document.getElementById('btnSubmit');
const feedback     = document.getElementById('token-feedback');
const statusIcon   = document.getElementById('token-status-icon');

tokenInput.addEventListener('input', function () {
    const typed = this.value.toUpperCase();
    this.value  = typed;

    if (typed.length === 0) {
        resetTokenUI();
        return;
    }

    if (typed === correctToken) {
        setInputState('success');
        feedback.textContent = '✓ Token cocok! Anda dapat melanjutkan.';
        feedback.className   = 'text-xs mt-1.5 text-emerald-600 font-semibold';
        statusIcon.innerHTML = '<i class="fas fa-check-circle text-emerald-500 text-base"></i>';
        enableButton();
    } else {
        setInputState('error');
        feedback.textContent = typed.length < correctToken.length
            ? `${typed.length} / ${correctToken.length} karakter...`
            : '✗ Token tidak cocok, periksa kembali.';
        feedback.className   = 'text-xs mt-1.5 text-rose-500 font-semibold';
        statusIcon.innerHTML = typed.length < correctToken.length
            ? `<span class="text-xs text-slate-400 font-mono">${typed.length}/${correctToken.length}</span>`
            : '<i class="fas fa-times-circle text-rose-400 text-base"></i>';
        disableButton();
    }
});
        // Blokir paste
        tokenInput.addEventListener('paste', function (e) {
            e.preventDefault();
            feedback.textContent = '⚠ Tempel (paste) tidak diizinkan. Ketik token secara manual.';
            feedback.className   = 'text-xs mt-1.5 text-amber-600 font-semibold';
        });

        function setInputState(state) {
            tokenInput.classList.remove(
                'border-amber-200', 'bg-amber-50',
                'border-emerald-400', 'bg-emerald-50',
                'border-rose-400', 'bg-rose-50'
            );
            if (state === 'success') {
                tokenInput.classList.add('border-emerald-400', 'bg-emerald-50');
            } else if (state === 'error') {
                tokenInput.classList.add('border-rose-400', 'bg-rose-50');
            } else {
                tokenInput.classList.add('border-amber-200', 'bg-amber-50');
            }
        }

        function enableButton() {
            btnSubmit.disabled = false;
            btnSubmit.classList.remove('bg-slate-300', 'cursor-not-allowed');
            btnSubmit.classList.add('bg-slate-900', 'hover:bg-indigo-600', 'shadow-md', 'hover:shadow-lg', 'cursor-pointer');
        }

        function disableButton() {
            btnSubmit.disabled = true;
            btnSubmit.classList.remove('bg-slate-900', 'hover:bg-indigo-600', 'shadow-md', 'hover:shadow-lg', 'cursor-pointer');
            btnSubmit.classList.add('bg-slate-300', 'cursor-not-allowed');
        }

        function resetTokenUI() {
            setInputState('default');
            feedback.textContent = '';
            feedback.className   = 'text-xs mt-1.5 hidden';
            statusIcon.innerHTML = '';
            tokenChars.forEach(span => {
                span.classList.remove('bg-emerald-100', 'border-emerald-400', 'text-emerald-700',
                                      'bg-rose-100', 'border-rose-400', 'text-rose-600');
                span.classList.add('bg-indigo-50', 'border-indigo-200', 'text-indigo-700');
            });
            disableButton();
        }

        // Loading saat submit
        document.getElementById('verifyForm').addEventListener('submit', function () {
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Menyiapkan Soal...';
        });
    </script>
</x-public-layout>