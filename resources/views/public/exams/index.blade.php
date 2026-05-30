<x-public-layout>
    {{-- MENYUNTIKKAN TITLE & META SEO --}}
    @section('title', 'Daftar Ujian - CBT Pro')

    @push('meta')
    <meta name="description" content="Daftar simulasi ujian dan Try Out yang dapat diikuti secara online di CBT Pro.">
    <meta property="og:title" content="Daftar Ujian - CBT Pro">
    <meta property="og:description"
        content="Daftar simulasi ujian dan Try Out yang dapat diikuti secara online di CBT Pro.">
    <meta property="og:url" content="{{ url()->current() }}">
    <link rel="canonical" href="{{ url()->current() }}">
    @endpush

    {{-- HERO / HEADER SECTION --}}
    <div class="w-full bg-slate-900 relative overflow-hidden pt-16 pb-24">
        <div class="absolute inset-0 bg-gradient-to-r from-indigo-800 to-slate-900 opacity-90"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <span
                class="inline-block px-4 py-1.5 rounded-full bg-indigo-500/20 text-indigo-300 text-xs font-black tracking-widest uppercase mb-4 border border-indigo-400/30">
                <i class="fas fa-list-ul mr-1"></i> Ujian Publik
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
        {{-- BAGIAN FILTER (BERTINGKAT) --}}
        {{-- ========================================== --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 mb-8">
            <form action="{{ route('public.exams.index') }}" method="GET" id="filterForm"
                class="flex flex-col md:flex-row gap-4 items-end">

                {{-- 1. Filter Kelas / Level --}}
                <div class="flex-1 w-full">
                    <label for="level" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
                        <i class="fas fa-chalkboard-teacher mr-1"></i> Pilih Kelas
                    </label>
                    <select name="level" id="level" onchange="document.getElementById('filterForm').submit()"
                        class="w-full border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 text-sm bg-slate-50 cursor-pointer">
                        <option value="">-- Semua Kelas --</option>
                        @foreach($levels as $lvl)
                        <option value="{{ $lvl->id }}" {{ request('level')==$lvl->id ? 'selected' : '' }}>
                            {{ $lvl->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- 2. Filter Mata Pelajaran (Muncul HANYA jika Kelas sudah dipilih) --}}
                @if(request('level'))
                <div class="flex-1 w-full animate-fade-in-up">
                    <label for="subject" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
                        <i class="fas fa-book mr-1"></i> Mata Pelajaran
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
                @endif

                {{-- 3. Tombol Reset (Muncul jika ada filter yang aktif) --}}
                @if(request('level') || request('subject'))
                <div>
                    <a href="{{ route('public.exams.index') }}"
                        class="bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold py-2.5 px-5 rounded-xl text-sm transition-colors flex items-center justify-center h-[42px] border border-rose-200"
                        title="Reset Semua Filter">
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
                {{-- ... (Isi card ujian Anda tetap sama seperti sebelumnya) ... --}}
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
                    <a href="{{ route('public.exams.detail', $exam->slug) }}"
                        aria-label="Lihat detail dan kerjakan ujian {{ $exam->title }}"
                        title="Lihat detail ujian {{ $exam->title }}"
                        class="w-full bg-slate-900 hover:bg-indigo-600 text-white font-bold py-3.5 px-4 rounded-xl text-sm text-center transition-all flex items-center justify-center gap-2 shadow-md group">
                        Detail
                        <i class="fas fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
                    </a>

                    <a href="{{ route('public.exams.ranking', $exam) }}"
                        aria-label="Lihat klasemen/ranking ujian {{ $exam->title }}"
                        title="Lihat ranking ujian {{ $exam->title }}"
                        class="w-full bg-amber-50 hover:bg-amber-500 text-amber-600 hover:text-white border border-amber-200 hover:border-amber-500 font-bold py-3.5 px-4 rounded-xl text-sm text-center transition-all flex items-center justify-center gap-2 shadow-sm group">
                        <i class="fas fa-trophy group-hover:scale-110 transition-transform"></i>
                        Ranking
                    </a>
                </div>
            </article>
            @endforeach
        </div>

        {{-- ========================================== --}}
        {{-- LINK PAGINATION SERVER SIDE --}}
        {{-- ========================================== --}}
        <div class="mt-12">
            {{-- withQueryString() sangat penting agar saat pindah page, filter tidak hilang --}}
            {{ $publicExams->withQueryString()->links() }}
        </div>
        {{-- ========================================== --}}

        @endif
    </div>
</x-public-layout>