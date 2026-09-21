<x-app-layout>
    <div class="w-full px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Preview Import Excel</h2>
                <p class="text-sm text-slate-500 mt-1">Pilih soal yang benar-benar ingin ditambahkan ke ujian.</p>
            </div>
            <a href="{{ route('admin.exams.soal.index', $exam) }}"
                class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-bold text-slate-700">Batal</a>
        </div>

        <form action="{{ route('admin.exams.soal.import.store', $exam) }}" method="POST">
            @csrf
            <input type="hidden" name="excel_data" value="{{ $jsonDataEncoded }}">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="bg-slate-50 px-5 py-4 border-b flex items-center justify-between">
                    <label class="flex items-center gap-3 font-bold text-sm text-slate-700">
                        <input type="checkbox" id="checkAll" class="w-5 h-5 rounded text-indigo-600">
                        Pilih semua (<span id="selectedCount">0</span>/{{ $rows->count() }})
                    </label>
                    <button type="submit" id="submitButton" disabled
                        class="px-5 py-2.5 rounded-lg bg-emerald-600 text-white text-sm font-bold disabled:opacity-50">
                        Import Soal Terpilih
                    </button>
                </div>

                <div class="divide-y divide-slate-100">
                    @foreach($rows as $index => $row)
                    @php
                        $content = $row['narasi_soal'] ?? '';
                        $explanation = $row['pembahasan'] ?? $row['explanation'] ?? '';
                        $options = array_filter([
                            'A' => $row['opsi_a'] ?? null,
                            'B' => $row['opsi_b'] ?? null,
                            'C' => $row['opsi_c'] ?? null,
                            'D' => $row['opsi_d'] ?? null,
                            'E' => $row['opsi_e'] ?? null,
                        ]);
                    @endphp
                    <label class="block p-5 hover:bg-indigo-50/30 cursor-pointer">
                        <div class="flex gap-4">
                            <input type="checkbox" name="selected_indexes[]" value="{{ $index }}"
                                class="question-check mt-1 w-5 h-5 rounded text-indigo-600">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-xs font-black text-indigo-600">Soal {{ $index + 1 }}</span>
                                    <span class="text-[10px] uppercase font-bold bg-slate-100 text-slate-500 px-2 py-1 rounded">
                                        {{ empty($options) ? 'Essay' : 'Pilihan Ganda' }}
                                    </span>
                                </div>
                                <div class="prose prose-sm max-w-none text-slate-800">{!! $content !!}</div>
                                @if($options)
                                <div class="mt-3 grid gap-1 text-sm text-slate-600">
                                    @foreach($options as $letter => $option)
                                    <div><strong>{{ $letter }}.</strong> {!! $option !!}</div>
                                    @endforeach
                                </div>
                                @endif
                                @if($explanation)
                                <div class="mt-3 rounded-lg bg-amber-50 border border-amber-200 p-3">
                                    <div class="text-[10px] font-black uppercase text-amber-700 mb-1">Pembahasan</div>
                                    <div class="prose prose-sm max-w-none text-amber-900">{!! $explanation !!}</div>
                                </div>
                                @endif
                                @if(!empty($row['kunci_jawaban']))
                                <div class="mt-2 text-xs text-emerald-700 font-bold">
                                    Kunci jawaban: {{ $row['kunci_jawaban'] }}
                                </div>
                                @endif
                            </div>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
        </form>
    </div>
    <script>
        const all = document.getElementById('checkAll');
        const checks = () => [...document.querySelectorAll('.question-check')];
        const count = document.getElementById('selectedCount');
        const submit = document.getElementById('submitButton');
        function update() {
            const selected = checks().filter(item => item.checked).length;
            count.textContent = selected;
            submit.disabled = selected === 0;
            all.checked = selected === checks().length;
        }
        all.addEventListener('change', () => {
            checks().forEach(item => item.checked = all.checked);
            update();
        });
        checks().forEach(item => item.addEventListener('change', update));
    </script>
</x-app-layout>
