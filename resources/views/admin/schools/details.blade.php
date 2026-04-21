<x-app-layout>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8fafc;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>

    <div class="min-h-screen py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Header Section --}}
            <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center shadow-lg text-white rotate-3">
                        <i class="fas fa-building text-xl -rotate-3"></i>
                    </div>
                    <div>
                        <h2 class="font-black text-2xl text-slate-800 tracking-tight">{{ $school->name }}</h2>
                        <p class="text-sm text-slate-500 font-bold">
                            @if($school->domain)
                                <a href="http://{{ $school->domain }}" target="_blank" class="hover:text-indigo-500 transition">
                                    <i class="fas fa-globe me-1"></i> {{ $school->domain }}
                                </a>
                            @else
                                <span class="text-slate-400"><i class="fas fa-globe me-1"></i> Tidak ada domain</span>
                            @endif
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    {{-- Sesuaikan route ini jika name route kamu berbeda --}}
                    <a href="{{ route('admin.schools.index') }}" 
                        class="bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 px-5 py-2.5 rounded-xl font-bold transition shadow-sm flex items-center gap-2 whitespace-nowrap active:scale-95">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>

            {{-- Alert Tahun Pelajaran --}}
            <div class="mb-8 p-6 rounded-[2rem] border {{ $activeAcademicYear ? 'bg-emerald-50 border-emerald-100' : 'bg-amber-50 border-amber-100' }} flex items-center gap-5 shadow-sm">
                <div class="w-14 h-14 rounded-2xl {{ $activeAcademicYear ? 'bg-gradient-to-br from-emerald-400 to-emerald-600' : 'bg-gradient-to-br from-amber-400 to-amber-600' }} text-white flex items-center justify-center text-2xl shadow-lg shrink-0">
                    <i class="fas {{ $activeAcademicYear ? 'fa-check-circle' : 'fa-exclamation-triangle' }}"></i>
                </div>
                <div>
                    <h3 class="font-black text-slate-800 text-lg mb-0.5">Tahun Pelajaran Aktif</h3>
                    <p class="text-slate-600 font-bold text-sm">
                        {{ $activeAcademicYear ? $activeAcademicYear->name : 'Belum ada tahun pelajaran yang diaktifkan untuk sekolah ini.' }}
                    </p>
                </div>
            </div>

            {{-- Main Content Grid --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                {{-- Kolom Kiri: Daftar Guru (1/3 Lebar) --}}
                <div class="col-span-1 space-y-6">
                    <div class="bg-white rounded-[2rem] border border-slate-100 shadow-sm overflow-hidden">
                        <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                            <h3 class="font-black text-slate-800 text-lg flex items-center gap-2">
                                <i class="fas fa-chalkboard-teacher text-indigo-500"></i> Daftar Guru
                            </h3>
                            <span class="bg-indigo-100 text-indigo-700 text-xs font-black px-3 py-1 rounded-lg shadow-inner">{{ $teachers->count() }}</span>
                        </div>
                        <div class="p-0">
                            <ul class="divide-y divide-slate-100">
                                @forelse($teachers as $teacher)
                                    <li class="p-4 px-6 hover:bg-slate-50 transition flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-black text-sm border border-indigo-100 shrink-0 shadow-sm">
                                            {{ strtoupper(substr($teacher->name, 0, 1)) }}
                                        </div>
                                        <div class="overflow-hidden">
                                            <p class="font-bold text-slate-800 text-sm truncate">{{ $teacher->name }}</p>
                                            <p class="text-xs text-slate-400 font-bold truncate">{{ $teacher->email }}</p>
                                        </div>
                                    </li>
                                @empty
                                    <li class="p-10 text-center">
                                        <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3">
                                            <i class="fas fa-user-slash text-2xl text-slate-300"></i>
                                        </div>
                                        <p class="text-slate-400 text-sm font-bold">Belum ada data guru.</p>
                                    </li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Kolom Kanan: Daftar Kelas & Siswa (2/3 Lebar) --}}
                <div class="col-span-1 lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-[2rem] border border-slate-100 shadow-sm overflow-hidden">
                        <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                            <h3 class="font-black text-slate-800 text-lg flex items-center gap-2">
                                <i class="fas fa-users text-emerald-500"></i> Daftar Kelas & Siswa
                            </h3>
                            @if($activeAcademicYear)
                                <span class="bg-emerald-100 text-emerald-700 text-xs font-black px-3 py-1 rounded-lg shadow-inner">{{ $classrooms->count() }} Kelas</span>
                            @endif
                        </div>
                        
                        <div class="p-6">
                            @if(!$activeAcademicYear)
                                <div class="text-center py-12 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200">
                                    <i class="fas fa-calendar-times text-5xl text-slate-300 mb-4 block"></i>
                                    <p class="text-slate-500 font-bold">Aktifkan tahun pelajaran terlebih dahulu untuk melihat data kelas.</p>
                                </div>
                            @elseif($classrooms->isEmpty())
                                <div class="text-center py-12 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200">
                                    <i class="fas fa-door-open text-5xl text-slate-300 mb-4 block"></i>
                                    <p class="text-slate-500 font-bold">Belum ada kelas yang terdaftar pada tahun pelajaran ini.</p>
                                </div>
                            @else
                                <div class="space-y-4">
                                    @foreach($classrooms as $index => $classroom)
                                        {{-- Komponen Accordion Alpine.js --}}
                                        <div x-data="{ open: {{ $index === 0 ? 'true' : 'false' }} }" class="border border-slate-200 rounded-2xl overflow-hidden bg-white shadow-sm hover:shadow-md transition">
                                            <button @click="open = !open" class="w-full px-6 py-4 hover:bg-slate-50 transition flex justify-between items-center text-left">
                                                <div>
                                                    <h4 class="font-black text-slate-800 flex items-center gap-2">
                                                        <i class="fas fa-chalkboard text-indigo-400"></i> {{ $classroom->name }}
                                                    </h4>
                                                    <p class="text-xs text-slate-500 font-bold mt-1 ml-6">
                                                        Wali Kelas: <span class="text-slate-700">{{ $classroom->homeroomTeacher->name ?? 'Belum ditentukan' }}</span>
                                                    </p>
                                                </div>
                                                <div class="flex items-center gap-4">
                                                    <span class="bg-white border border-slate-200 text-slate-600 text-[10px] uppercase tracking-wider font-black px-3 py-1 rounded-lg shadow-sm">
                                                        {{ $classroom->students->count() }} Siswa
                                                    </span>
                                                    <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 transition-transform duration-300" :class="open ? 'rotate-180 bg-indigo-100 text-indigo-500' : ''">
                                                        <i class="fas fa-chevron-down"></i>
                                                    </div>
                                                </div>
                                            </button>
                                            
                                            <div x-show="open" x-collapse x-cloak class="border-t border-slate-100 bg-slate-50/30">
                                                @if($classroom->students->isEmpty())
                                                    <div class="p-8 text-center">
                                                        <i class="fas fa-user-graduate text-3xl text-slate-200 mb-2 block"></i>
                                                        <p class="text-sm font-bold text-slate-400">Belum ada siswa di kelas ini.</p>
                                                    </div>
                                                @else
                                                    <div class="overflow-x-auto p-4">
                                                        <table class="w-full text-left bg-white rounded-xl overflow-hidden border border-slate-100 shadow-sm">
                                                            <thead>
                                                                <tr class="bg-slate-50 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                                                    <th class="p-3 pl-5 w-12 text-center">No</th>
                                                                    <th class="p-3">Nama Siswa</th>
                                                                    <th class="p-3">Username</th>
                                                                    <th class="p-3 pr-5">Email</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="divide-y divide-slate-50 text-sm font-bold text-slate-600">
                                                                @foreach($classroom->students as $key => $student)
                                                                    <tr class="hover:bg-slate-50/80 transition">
                                                                        <td class="p-3 pl-5 text-center text-slate-400">{{ $key + 1 }}</td>
                                                                        <td class="p-3 text-slate-800">{{ $student->name }}</td>
                                                                        <td class="p-3">
                                                                            <code class="bg-indigo-50 border border-indigo-100 px-2 py-1 rounded-md text-[11px] text-indigo-600 font-black">
                                                                                {{ $student->username }}
                                                                            </code>
                                                                        </td>
                                                                        <td class="p-3 pr-5 text-slate-500">{{ $student->email }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</x-app-layout>