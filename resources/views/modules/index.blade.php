<x-public-layout :page-title="'Modul Belajar - CBT Pro'">
    <div class="w-full bg-slate-900 relative overflow-hidden pt-16 pb-24">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-800 to-slate-900 opacity-90"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <span
                class="inline-block px-4 py-1.5 rounded-full bg-blue-500/20 text-blue-300 text-xs font-black tracking-widest uppercase mb-4 border border-blue-400/30">
                <i class="fas fa-book-open mr-1"></i> Perpustakaan Materi
            </span>
            <h1 class="text-4xl md:text-5xl font-black text-white tracking-tight mb-4">Modul Interaktif</h1>
            <p class="text-lg text-slate-300 max-w-2xl mx-auto font-medium">
                Pelajari materi lengkap untuk mempersiapkan diri sebelum menghadapi ujian. Dapatkan poin tambahan setiap
                kali Anda menyelesaikan modul!
            </p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10 relative z-20 pb-20">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($modules as $module)
            <a href="{{ route('public.modules.show', $module->slug) }}"
                class="bg-white rounded-[2rem] border border-slate-200 shadow-sm hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 flex flex-col h-full overflow-hidden group">

                {{-- Thumbnail --}}
                <div class="h-48 bg-slate-100 relative overflow-hidden">
                    @if($module->thumbnail)
                    <img src="{{ asset('storage/' . $module->thumbnail) }}"
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @else
                    <div
                        class="w-full h-full bg-gradient-to-br from-indigo-100 to-blue-50 flex items-center justify-center">
                        <i class="fas fa-book-reader text-5xl text-indigo-200"></i>
                    </div>
                    @endif

                    @if($module->is_premium)
                    <div
                        class="absolute top-4 right-4 bg-amber-500 text-white text-[10px] font-black uppercase px-3 py-1 rounded-full shadow-lg flex items-center gap-1">
                        <i class="fas fa-crown"></i> Premium
                    </div>
                    @endif
                </div>

                {{-- Konten --}}
                <div class="p-6 flex-1 flex flex-col">
                    <div class="flex flex-wrap gap-2 mb-3">
                        <span
                            class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded-lg border border-blue-100">{{
                            $module->subject->name }}</span>
                        <span
                            class="text-xs font-bold text-orange-600 bg-orange-50 px-2 py-1 rounded-lg border border-orange-100">{{
                            $module->level->name }}</span>
                    </div>

                    <h2
                        class="text-lg font-black text-slate-800 mb-2 leading-snug group-hover:text-blue-600 transition-colors">
                        {{ $module->title }}
                    </h2>
                    <p class="text-sm text-slate-500 mb-4 line-clamp-2 flex-1">{{ $module->description }}</p>

                    <div class="flex items-center justify-between pt-4 border-t border-slate-100 mt-auto">
                        <div class="flex items-center gap-2 text-xs font-bold text-slate-400">
                            <i class="far fa-clock"></i> {{ $module->estimated_time_minutes }} Menit
                        </div>
                        <div
                            class="flex items-center gap-1 text-xs font-black text-emerald-500 bg-emerald-50 px-2.5 py-1 rounded-lg">
                            +{{ $module->reward_points }} Poin
                        </div>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
        <div class="mt-8">{{ $modules->links() }}</div>
    </div>
</x-public-layout>