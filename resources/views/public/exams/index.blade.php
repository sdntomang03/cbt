<x-public-layout>
    {{-- MENYUNTIKKAN TITLE & META SEO --}}
    @section('title', 'Daftar Simulasi Tryout TKA SD')

    @push('meta')
    <meta name="description" content="Daftar simulasi tryout TKA SD kelas 6 online secara gratis">
    <meta property="og:title" content="Daftar Simulasi Tryout TKA SD">
    <meta property="og:description" content="Daftar simulasi tryout TKA SD kelas 6 online secara gratis">
    <meta property="og:url" content="{{ url()->current() }}">
    <link rel="canonical" href="{{ url()->current() }}">
    @endpush

    {{-- HERO / HEADER SECTION --}}
    <div class="w-full bg-slate-900 relative overflow-hidden pt-16 pb-24">
        <div class="absolute inset-0 bg-gradient-to-r from-indigo-800 to-slate-900 opacity-90"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <span
                class="inline-block px-4 py-1.5 rounded-full bg-indigo-500/20 text-indigo-300 text-xs font-black tracking-widest uppercase mb-4 border border-indigo-400/30">
                <i class="fas fa-list-ul mr-1"></i> Simulasi TKA SD
            </span>
            <h1 class="text-4xl md:text-5xl font-black text-white tracking-tight mb-4">
                Daftar Try Out & Simulasi
            </h1>
            <p class="text-lg text-slate-300 max-w-2xl mx-auto font-medium">
                Pilih dan ikuti simulasi ujian atau Try Out yang tersedia di bawah ini untuk mengukur kemampuan Anda.
            </p>
        </div>
    </div>

    {{-- KONTEN UTAMA --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10 relative z-20 pb-20">

        {{-- ========================================== --}}
        {{-- BAGIAN FILTER (MATA PELAJARAN SAJA) --}}
        {{-- ========================================== --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 mb-8">
            <form action="{{ route('public.exams.index') }}" method="GET" id="filterForm"
                class="flex flex-col md:flex-row gap-4 items-end">

                {{-- 1. Filter Mata Pelajaran --}}
                <div class="flex-1 w-full">
                    <label for="subject" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
                        <i class="fas fa-book mr-1"></i> Pilih Mata Pelajaran
                    </label>
                    <select name="subject" id="subject" onchange="document.getElementById('filterForm').submit()"
                        class="w-full border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 text-sm bg-slate-50 cursor-pointer">
                        <option value="">-- Semua Mata Pelajaran --</option>
                        @foreach($subjects as $sub)
                        <option value="{{ $sub->id }}" {{ request('subject')==$sub->id ? 'selected' : '' }}>
                            {{ $sub->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- 2. Tombol Reset (Muncul HANYA jika filter subject aktif) --}}
                @if(request('subject'))
                <div>
                    <a href="{{ route('public.exams.index') }}"
                        class="bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold py-2.5 px-5 rounded-xl text-sm transition-colors flex items-center justify-center h-[42px] border border-rose-200"
                        title="Reset Filter">
                        <i class="fas fa-sync-alt mr-2"></i> Reset
                    </a>
                </div>
                @endif

            </form>
        </div>
        {{-- ========================================== --}}

        @if($publicExams->isEmpty())

        {{-- TAMPILAN JIKA TIDAK ADA UJIAN --}}
        <div
            class="bg-white rounded-[2rem] shadow-sm border border-slate-200 p-12 text-center flex flex-col items-center justify-center">
            <div
                class="w-24 h-24 bg-slate-50 text-slate-400 rounded-full flex items-center justify-center text-4xl mb-6">
                <i class="fas fa-folder-open"></i>
            </div>
            <h2 class="text-2xl font-bold text-slate-800 mb-2">Belum Ada Ujian</h2>
            <p class="text-slate-500 max-w-md mx-auto">
                Saat ini belum ada simulasi ujian sesuai filter tersebut. Silakan cek kembali nanti atau ubah filter
                pencarian.
            </p>
        </div>

        @else

        {{-- GRID DAFTAR UJIAN --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($publicExams as $exam)
            <article
                class="bg-white rounded-[2rem] p-8 border border-slate-200 shadow-sm hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 flex flex-col h-full relative overflow-hidden group">
                <div class="flex items-start justify-between mb-5 relative z-10">
                    <div
                        class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-2xl">
                        <i class="fas fa-file-signature"></i>
                    </div>
                    <span
                        class="bg-emerald-50 text-emerald-600 px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-1.5 border border-emerald-200">
                        <i class="fas fa-globe text-emerald-500"></i> Terbuka
                    </span>
                </div>

                <div class="flex-1 relative z-10">
                    <h2 class="text-xl font-black text-slate-800 mb-3 leading-snug">
                        <a href="{{ route('public.exams.detail', $exam->slug) }}"
                            class="hover:text-indigo-600 transition-colors">
                            {{ $exam->title }}
                        </a>
                    </h2>

                    {{-- BADGE KELAS DAN MATA PELAJARAN --}}
                    <div class="flex flex-wrap gap-2 mb-4">
                        @if($exam->subject)
                        <span
                            class="bg-blue-50 text-blue-700 px-2.5 py-1 rounded-lg text-xs font-bold border border-blue-100 flex items-center gap-1.5">
                            <i class="fas fa-book text-blue-500"></i> {{ $exam->subject->name }}
                        </span>
                        @endif

                        @if($exam->level)
                        <span
                            class="bg-orange-50 text-orange-700 px-2.5 py-1 rounded-lg text-xs font-bold border border-orange-100 flex items-center gap-1.5">
                            <i class="fas fa-layer-group text-orange-500"></i> {{ $exam->level->name }}
                        </span>
                        @endif
                    </div>

                    <p class="text-sm text-slate-500 mb-8 line-clamp-3">
                        {{ $exam->description ?? 'Tidak ada deskripsi untuk ujian ini.' }}
                    </p>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3 relative z-10">
                    <a href="{{ route('public.exams.ranking', $exam) }}"
                        aria-label="Lihat klasemen/ranking ujian {{ $exam->title }}"
                        title="Lihat ranking ujian {{ $exam->title }}"
                        class="w-full bg-amber-50 hover:bg-amber-500 text-amber-600 hover:text-white border border-amber-200 hover:border-amber-500 font-bold py-3.5 px-4 rounded-xl text-sm text-center transition-all flex items-center justify-center gap-2 shadow-sm group">
                        <i class="fas fa-trophy group-hover:scale-110 transition-transform"></i>
                        Ranking
                    </a>
                    <a href="{{ route('public.exams.detail', $exam->slug) }}"
                        aria-label="Lihat detail dan kerjakan ujian {{ $exam->title }}"
                        title="Lihat detail ujian {{ $exam->title }}"
                        class="w-full bg-slate-900 hover:bg-indigo-600 text-white font-bold py-3.5 px-4 rounded-xl text-sm text-center transition-all flex items-center justify-center gap-2 shadow-md group">
                        Kerjakan <i
                            class="fas fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>

                    </a>


                </div>
            </article>
            @endforeach
        </div>

        {{-- ========================================== --}}
        {{-- LINK PAGINATION SERVER SIDE --}}
        {{-- ========================================== --}}
        <div class="mt-12">
            {{ $publicExams->withQueryString()->links() }}
        </div>
        {{-- ========================================== --}}

        @endif

        {{-- ========================================== --}}
        {{-- ARTIKEL SEO: SIMULASI TRYOUT TKA SD --}}
        {{-- ========================================== --}}
        <article class="mt-20 bg-white rounded-[2rem] shadow-sm border border-slate-200 p-8 sm:p-12 overflow-hidden">
            <div class="max-w-4xl mx-auto">
                <header class="mb-8">
                    <h2 class="text-3xl font-black text-slate-800 mb-4 tracking-tight">
                        Mengapa Simulasi Tryout TKA SD Sangat Penting?
                    </h2>
                    <div class="w-20 h-1.5 bg-indigo-500 rounded-full"></div>
                </header>

                <div class="space-y-6 text-slate-600 leading-relaxed">
                    <p>
                        Menghadapi <strong>Tes Kemampuan Akademik (TKA)</strong> seringkali menjadi momen yang
                        menegangkan bagi siswa Sekolah Dasar (SD), terutama bagi mereka yang berada di kelas 6. TKA
                        bukan sekadar ujian biasa; tes ini dirancang untuk mengukur tingkat pemahaman, logika, dan
                        literasi akademik siswa secara mendalam sebagai bekal menuju jenjang pendidikan selanjutnya.
                    </p>
                    <p>
                        Untuk membantu siswa mempersiapkan diri secara optimal, CBT Pro menghadirkan <strong>Simulasi
                            Tryout TKA SD Online</strong> secara gratis. Melalui platform ini, siswa dapat merasakan
                        pengalaman ujian yang sesungguhnya dengan format <em>Computer Based Test</em> (CBT) yang kini
                        menjadi standar evaluasi pendidikan modern.
                    </p>

                    <h3 class="text-xl font-bold text-slate-800 mt-8 mb-3">Manfaat Mengikuti Simulasi Tryout di CBT Pro
                    </h3>
                    <ul class="list-disc ml-5 space-y-3 marker:text-indigo-500">
                        <li><strong>Adaptasi Teknologi CBT:</strong> Siswa menjadi lebih terbiasa dan tidak gagap
                            teknologi saat menghadapi antarmuka ujian berbasis komputer yang sebenarnya.</li>
                        <li><strong>Manajemen Waktu:</strong> Fitur <em>timer</em> yang berjalan *real-time* melatih
                            siswa untuk mengalokasikan waktu dengan bijak pada setiap butir soal.</li>
                        <li><strong>Evaluasi Instan:</strong> Setelah menyelesaikan tryout, sistem akan langsung
                            menampilkan skor dan hasil evaluasi, memungkinkan siswa mengetahui area mana yang perlu
                            ditingkatkan.</li>
                        <li><strong>Mengurangi Kecemasan:</strong> Pengalaman simulasi yang berulang-ulang terbukti
                            secara psikologis mampu menurunkan tingkat stres dan kepanikan anak saat hari ujian tiba.
                        </li>
                        <li><strong>Ranking Nasional:</strong> Siswa yang telah melaksanakan tryout dapat melihat
                            peringkatnya secara nasional, memberikan motivasi tambahan untuk
                            belajar lebih giat dan bersaing secara sehat dengan teman-teman sebayanya di seluruh
                            Indonesia.
                        </li>
                    </ul>

                    <div class="bg-indigo-50 border-l-4 border-indigo-500 p-5 rounded-r-xl mt-8">
                        <p class="text-indigo-900 font-medium m-0">
                            <i class="fas fa-lightbulb text-indigo-500 mr-2"></i> <strong>Tips Lulus:</strong> Pastikan
                            koneksi internet Anda stabil sebelum menekan tombol mulai, siapkan alat tulis untuk
                            coret-coretan berhitung, dan bacalah setiap soal literasi dengan cermat dan tenang. Selamat
                            belajar dan semoga sukses!
                        </p>
                    </div>
                </div>
            </div>
        </article>
        {{-- ========================================== --}}

    </div>
</x-public-layout>