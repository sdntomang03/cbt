<x-app-layout>
    <div class="py-10 px-4 sm:px-6 lg:px-8 max-w-3xl mx-auto">
        <div class="mb-8 flex items-center gap-4">
            <a href="{{ route('admin.classrooms.index') }}"
                class="w-10 h-10 flex items-center justify-center rounded-full bg-white shadow-sm border border-slate-200 text-slate-500 hover:text-indigo-600 transition">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="text-3xl font-black text-slate-800 tracking-tight">Edit Kelas: {{ $classroom->name }}</h2>
            </div>
        </div>

        <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-200">
            <form action="{{ route('admin.classrooms.update', $classroom->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Nama Kelas <span
                            class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $classroom->name) }}" required
                        class="block w-full rounded-xl border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 font-bold text-slate-700 shadow-sm">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Tahun Ajaran <span
                            class="text-rose-500">*</span></label>
                    <select name="academic_year_id" required
                        class="block w-full rounded-xl border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 font-bold text-slate-700 shadow-sm bg-slate-50">
                        @foreach($academicYears as $year)
                        <option value="{{ $year->id }}" {{ old('academic_year_id', $classroom->academic_year_id) ==
                            $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Wali Kelas (Opsional)</label>
                    <select name="user_id"
                        class="block w-full rounded-xl border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 font-bold text-slate-700 shadow-sm bg-slate-50">
                        <option value="">-- Pilih Guru --</option>
                        @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" {{ old('user_id', $classroom->user_id) == $teacher->id ?
                            'selected' : '' }}>{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-4 flex justify-end">
                    <button type="submit"
                        class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-black rounded-xl shadow-lg shadow-indigo-200 transition-all">
                        Update Kelas
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>