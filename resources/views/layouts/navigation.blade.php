<div class="flex items-center justify-between h-20 px-6 border-b border-slate-100 shrink-0">
    <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
        <div
            class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-indigo-200">
            <i class="fas fa-graduation-cap text-xl"></i>
        </div>
        <span class="font-black text-2xl tracking-tight text-slate-800">
            CBT<span class="text-indigo-600">Pro</span>
        </span>
    </a>

    <button @click="sidebarOpen = false" class="text-slate-400 hover:text-rose-500 lg:hidden focus:outline-none p-2">
        <i class="fas fa-times text-2xl"></i>
    </button>
</div>

<nav @click="if(window.innerWidth < 1024) sidebarOpen = false"
    class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto overflow-x-hidden custom-scrollbar">

    @php
    $navClass = "flex items-center gap-3 px-4 py-3.5 rounded-xl font-bold transition-all duration-200
    whitespace-nowrap";
    $activeClass = "bg-indigo-600 text-white shadow-md shadow-indigo-200";
    $inactiveClass = "text-slate-500 hover:bg-indigo-50 hover:text-indigo-600";
    @endphp

    <a href="{{ route('dashboard') }}"
        class="{{ $navClass }} {{ request()->routeIs('dashboard') ? $activeClass : $inactiveClass }}">
        <i class="fas fa-home w-6 text-center text-lg"></i>
        <span>Dashboard</span>
    </a>

    {{-- KELOMPOK ADMINISTRASI (Muncul jika punya salah satu permission di bawah) --}}
    @canany(['manage schools', 'view users'])
    <div class="text-xs font-black text-slate-400 uppercase tracking-widest mt-8 mb-3 px-4">Administrasi Sekolah</div>

    @can('manage schools')
    <a href="{{ route('admin.schools.index') }}"
        class="{{ $navClass }} {{ request()->routeIs('admin.schools.*') ? $activeClass : $inactiveClass }}">
        <i class="fas fa-school w-6 text-center text-lg"></i>
        <span>Data Sekolah</span>
    </a>
    @endcan

    @can('view users')
    <a href="{{ route('admin.users.index') }}"
        class="{{ $navClass }} {{ request()->routeIs('admin.users.*') ? $activeClass : $inactiveClass }}">
        <i class="fas fa-users w-6 text-center text-lg"></i>
        <span>Data Users</span>
    </a>
    @endcan

    @can('manage schools')
    <a href="{{ route('admin.settings.registration') }}"
        class="{{ $navClass }} {{ request()->routeIs('admin.settings.*') ? $activeClass : $inactiveClass }}">
        <i class="fas fa-cogs w-6 text-center text-lg"></i>
        <span>Pengaturan Sistem</span>
    </a>
    @endcan
    @endcanany

    {{-- KELOMPOK KEAMANAN (Khusus Super Admin) --}}
    @can('manage access')
    <div class="text-xs font-black text-slate-400 uppercase tracking-widest mt-8 mb-3 px-4">Keamanan Sistem</div>

    <a href="{{ route('admin.roles.index') }}"
        class="{{ $navClass }} {{ request()->routeIs('admin.roles.*') ? $activeClass : $inactiveClass }}">
        <i class="fas fa-key w-6 text-center text-lg"></i>
        <span>Roles</span>
    </a>

    <a href="{{ route('admin.permissions.index') }}"
        class="{{ $navClass }} {{ request()->routeIs('admin.permissions.*') ? $activeClass : $inactiveClass }}">
        <i class="fas fa-fingerprint w-6 text-center text-lg"></i>
        <span>Permissions</span>
    </a>
    @endcan

    {{-- KELOMPOK MANAJEMEN UJIAN & AKADEMIK --}}
    @canany(['view classrooms', 'view exams', 'manage exam sessions', 'proctor exams', 'manage math exams'])
    <div class="text-xs font-black text-slate-400 uppercase tracking-widest mt-8 mb-3 px-4">Manajemen Ujian</div>

    @can('view classrooms')
    <a href="{{ route('admin.classrooms.index') }}"
        class="{{ $navClass }} {{ request()->routeIs('admin.classrooms.*') ? $activeClass : $inactiveClass }}">
        <i class="fas fa-chalkboard w-6 text-center text-lg"></i>
        <span>Manajemen Kelas</span>
    </a>
    @endcan

    @can('view exams')
    <a href="{{ route('admin.exams.index') }}"
        class="{{ $navClass }} {{ request()->routeIs('admin.exams.*') ? $activeClass : $inactiveClass }}">
        <i class="fas fa-folder-open w-6 text-center text-lg"></i>
        <span>Ujian & Bank Soal</span>
    </a>
    @endcan

    @can('manage exam sessions')
    <a href="{{ route('admin.exam-sessions.index') }}"
        class="{{ $navClass }} {{ request()->routeIs('admin.exam-sessions.*') ? $activeClass : $inactiveClass }}">
        <i class="fas fa-calendar-alt w-6 text-center text-lg"></i>
        <span>Jadwal Ujian</span>
    </a>
    @endcan

    @can('proctor exams')
    <a href="{{ route('proctor.index') }}"
        class="{{ $navClass }} {{ request()->routeIs('proctor.*') ? $activeClass : $inactiveClass }}">
        <i class="fas fa-desktop w-6 text-center text-lg"></i>
        <span>Monitoring Ujian</span>
    </a>
    @endcan

    @can('manage math exams')
    <a href="{{ route('admin.math.index') }}"
        class="{{ $navClass }} {{ request()->routeIs('admin.math.*') ? $activeClass : $inactiveClass }}">
        <i class="fas fa-calculator w-6 text-center text-lg"></i>
        <span>Math Exams</span>
    </a>
    @endcan
    @endcanany

    {{-- KELOMPOK SISWA --}}
    @can('take exams')
    <div class="text-xs font-black text-slate-400 uppercase tracking-widest mt-8 mb-3 px-4">Menu Siswa</div>

    <a href="{{ route('student.index') }}"
        class="{{ $navClass }} {{ request()->routeIs('student.index') ? $activeClass : $inactiveClass }}">
        <i class="fas fa-door-open w-6 text-center text-lg"></i>
        <span>Ruang Ujian</span>
    </a>


    <a href="{{ route('student.math.index') }}"
        class="{{ $navClass }} {{ request()->routeIs('student.math.*') ? $activeClass : $inactiveClass }}">
        <i class="fas fa-superscript w-6 text-center text-lg"></i>
        <span>Latihan Hitung</span>
    </a>


    <div class="text-xs font-black text-slate-400 uppercase tracking-widest mt-8 mb-3 px-4">Modul Interaktif</div>

    <a href="{{ route('hitung.index') }}"
        class="{{ $navClass }} {{ request()->routeIs('hitung.*') ? $activeClass : $inactiveClass }}">
        <span class="w-6 text-center text-xl">🧮</span>
        <span>Kawan Hitung</span>
    </a>

    <a href="{{ route('baca.index') }}"
        class="{{ $navClass }} {{ request()->routeIs('baca.*') ? $activeClass : $inactiveClass }}">
        <span class="w-6 text-center text-xl">📖</span>
        <span>Kawan Baca</span>
    </a>
    @endcan

</nav>