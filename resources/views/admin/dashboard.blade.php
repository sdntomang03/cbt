<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <div
                class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-600 to-purple-600 flex items-center justify-center shadow-lg text-white shrink-0">
                <i class="fas fa-home text-xl"></i>
            </div>
            <div>
                <h2 class="font-black text-2xl text-slate-800 tracking-tight">Ikhtisar Sistem</h2>
                <p class="text-sm text-slate-500 font-bold">Dashboard Pusat & Pemantauan Akademik</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8 mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- ========================================== --}}
        {{-- BANNER SELAMAT DATANG (DINAMIS) --}}
        {{-- ========================================== --}}
        <div
            class="bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-800 rounded-[2rem] p-8 sm:p-10 shadow-xl shadow-indigo-200 relative overflow-hidden text-white">
            <div
                class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-white opacity-10 rounded-full blur-3xl pointer-events-none">
            </div>
            <div
                class="absolute bottom-0 left-0 -mb-10 -ml-10 w-48 h-48 bg-white opacity-10 rounded-full blur-2xl pointer-events-none">
            </div>

            <div class="relative z-10">
                {{-- Badge Status & Role --}}
                <div
                    class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-sm border border-white/30 px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-widest mb-4">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Sistem Aktif <span class="mx-1 opacity-50">•</span>
                    <span class="text-white">{{ $user->roles->first()->name ?? 'Pengguna' }}</span>
                </div>

                {{-- Sapaan Nama Panggilan --}}
                <h1 class="text-3xl sm:text-4xl font-black mb-3 tracking-tight">
                    Halo, {{ explode(' ', trim($user->name))[0] }}! 👋
                </h1>

                {{-- Teks Deskripsi Dinamis Berdasarkan Role --}}
                @if($isAdmin)
                <p class="text-indigo-100 font-medium text-sm sm:text-base max-w-xl leading-relaxed">
                    Selamat datang di Pusat Kendali Utama CBT SDN Tomang 03 Pagi. Pantau seluruh statistik akademik,
                    kelola hak akses pengguna, dan pastikan sistem ujian berjalan lancar dari satu layar.
                </p>
                @elseif($user->hasRole('operator'))
                <p class="text-indigo-100 font-medium text-sm sm:text-base max-w-xl leading-relaxed">
                    Selamat datang di Ruang Tata Usaha Digital SDN Tomang 03 Pagi. Anda dapat mengelola data pengajar,
                    pendaftaran siswa baru, dan mengatur jadwal sesi ujian dengan mudah di sini.
                </p>
                @else
                <p class="text-indigo-100 font-medium text-sm sm:text-base max-w-xl leading-relaxed">
                    Selamat datang di Dashboard Akademik Guru. Mari siapkan materi ujian, pantau progres evaluasi harian
                    kelas 4B, dan pastikan kelancaran ujian siswa-siswi SDN Tomang 03 Pagi secara terpadu.
                </p>
                @endif
            </div>
        </div>

        {{-- ========================================== --}}
        {{-- GRID STATISTIK UTAMA (BENTO BOX) --}}
        {{-- ========================================== --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">

            <div
                class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col justify-between hover:shadow-md transition-shadow group relative overflow-hidden">
                <div
                    class="absolute -right-4 -top-4 w-20 h-20 bg-emerald-50 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500">
                </div>
                <div class="flex justify-between items-start mb-4 relative z-10">
                    <div
                        class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                </div>
                <div class="relative z-10">
                    <h3 class="text-3xl font-black text-slate-800 tracking-tight">{{
                        number_format($stats['total_siswa']) }}</h3>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Siswa Terdaftar</p>
                </div>
            </div>

            <div
                class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col justify-between hover:shadow-md transition-shadow group relative overflow-hidden">
                <div
                    class="absolute -right-4 -top-4 w-20 h-20 bg-blue-50 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500">
                </div>
                <div class="flex justify-between items-start mb-4 relative z-10">
                    <div
                        class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-lg">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                </div>
                <div class="relative z-10">
                    <h3 class="text-3xl font-black text-slate-800 tracking-tight">{{ number_format($stats['total_guru'])
                        }}</h3>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Guru Pengajar</p>
                </div>
            </div>

            <div
                class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col justify-between hover:shadow-md transition-shadow group relative overflow-hidden">
                <div
                    class="absolute -right-4 -top-4 w-20 h-20 bg-rose-50 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500">
                </div>
                <div class="flex justify-between items-start mb-4 relative z-10">
                    <div
                        class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center text-lg">
                        <i class="fas fa-user-shield"></i>
                    </div>
                </div>
                <div class="relative z-10">
                    <h3 class="text-3xl font-black text-slate-800 tracking-tight">{{
                        number_format($stats['total_staff']) }}</h3>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">
                        {{ $isAdmin ? 'Super Admin' : 'Staf Operator' }}
                    </p>
                </div>
            </div>

            @if($isAdmin)
            <div
                class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col justify-between hover:shadow-md transition-shadow group relative overflow-hidden">
                <div
                    class="absolute -right-4 -top-4 w-20 h-20 bg-amber-50 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500">
                </div>
                <div class="flex justify-between items-start mb-4 relative z-10">
                    <div
                        class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-lg">
                        <i class="fas fa-school"></i>
                    </div>
                </div>
                <div class="relative z-10">
                    <h3 class="text-3xl font-black text-slate-800 tracking-tight">{{
                        number_format($stats['total_sekolah']) }}</h3>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Total Sekolah</p>
                </div>
            </div>
            @else
            <div
                class="bg-indigo-50/50 rounded-2xl border border-indigo-100 border-dashed flex items-center justify-center p-6 text-indigo-300">
                <i class="fas fa-chart-line text-3xl opacity-50"></i>
            </div>
            @endif

        </div>

        {{-- ========================================== --}}
        {{-- PINTASAN MENU AKADEMIK UMUM --}}
        {{-- ========================================== --}}
        <h3 class="font-black text-lg text-slate-800 mt-8 mb-4">Akses Cepat Akademik</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">

            <a href="{{ route('admin.classrooms.index') }}"
                class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4 hover:border-indigo-300 hover:shadow-md transition-all group">
                <div
                    class="w-12 h-12 bg-slate-50 text-indigo-500 rounded-xl flex items-center justify-center text-xl group-hover:bg-indigo-500 group-hover:text-white transition-colors shrink-0">
                    <i class="fas fa-chalkboard"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800 text-sm">Kelas</h4>
                    <p class="text-[11px] text-slate-400 font-bold mt-0.5">Manajemen Ruang Kelas</p>
                </div>
            </a>

            <a href="{{ route('admin.exams.index') }}"
                class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4 hover:border-blue-300 hover:shadow-md transition-all group">
                <div
                    class="w-12 h-12 bg-slate-50 text-blue-500 rounded-xl flex items-center justify-center text-xl group-hover:bg-blue-500 group-hover:text-white transition-colors shrink-0">
                    <i class="fas fa-folder-open"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800 text-sm">Bank Soal</h4>
                    <p class="text-[11px] text-slate-400 font-bold mt-0.5">Kelola Ujian Umum</p>
                </div>
            </a>

            <a href="{{ route('admin.math.index') }}"
                class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4 hover:border-emerald-300 hover:shadow-md transition-all group">
                <div
                    class="w-12 h-12 bg-slate-50 text-emerald-500 rounded-xl flex items-center justify-center text-xl group-hover:bg-emerald-500 group-hover:text-white transition-colors shrink-0">
                    <i class="fas fa-calculator"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800 text-sm">Ujian Matematika</h4>
                    <p class="text-[11px] text-slate-400 font-bold mt-0.5">Soal Logika & Hitungan</p>
                </div>
            </a>

            <a href="{{ route('proctor.index') }}"
                class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4 hover:border-rose-300 hover:shadow-md transition-all group">
                <div
                    class="w-12 h-12 bg-slate-50 text-rose-500 rounded-xl flex items-center justify-center text-xl group-hover:bg-rose-500 group-hover:text-white transition-colors shrink-0">
                    <i class="fas fa-desktop"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800 text-sm">Pengawas</h4>
                    <p class="text-[11px] text-slate-400 font-bold mt-0.5">Monitor Sesi Aktif</p>
                </div>
            </a>

        </div>

        {{-- ========================================== --}}
        {{-- PINTASAN KEAMANAN (HANYA ADMIN) --}}
        {{-- ========================================== --}}
        @if($isAdmin)
        <h3 class="font-black text-lg text-slate-800 mt-8 mb-4">Navigasi Keamanan & Sistem</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 mb-8">

            <a href="{{ route('admin.users.index') }}"
                class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4 hover:border-indigo-300 hover:shadow-md transition-all group">
                <div
                    class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center text-xl group-hover:bg-indigo-600 group-hover:text-white transition-colors shrink-0">
                    <i class="fas fa-users-cog"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800">Manajemen Users</h4>
                    <p class="text-xs text-slate-400 font-medium mt-0.5">Atur Akun Pengguna</p>
                </div>
            </a>

            <a href="{{ route('admin.roles.index') }}"
                class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4 hover:border-purple-300 hover:shadow-md transition-all group">
                <div
                    class="w-12 h-12 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center text-xl group-hover:bg-purple-600 group-hover:text-white transition-colors shrink-0">
                    <i class="fas fa-key"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800">Roles</h4>
                    <p class="text-xs text-slate-400 font-medium mt-0.5"><span class="text-purple-600 font-bold">{{
                            $stats['total_roles'] }} Role</span> tersedia</p>
                </div>
            </a>

            <a href="{{ route('admin.permissions.index') }}"
                class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4 hover:border-emerald-300 hover:shadow-md transition-all group">
                <div
                    class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-xl group-hover:bg-emerald-600 group-hover:text-white transition-colors shrink-0">
                    <i class="fas fa-fingerprint"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800">Permissions</h4>
                    <p class="text-xs text-slate-400 font-medium mt-0.5"><span class="text-emerald-600 font-bold">{{
                            $stats['total_permissions'] }} Label</span> terdaftar</p>
                </div>
            </a>
        </div>
        @endif

    </div>
</x-app-layout>