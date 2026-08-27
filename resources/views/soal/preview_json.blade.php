<x-app-layout>
    {{-- Ubah max-w-5xl menjadi w-full agar melebar penuh --}}
    <div class="w-full px-4 sm:px-6 lg:px-8 py-8">

        <div class="mb-6 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-slate-800 uppercase tracking-tight">Preview Import JSON</h2>
                <p class="text-sm text-slate-500 mt-1">Pilih soal yang ingin disimpan ke dalam ujian.</p>
            </div>
            <a href="{{ route('admin.exams.soal.index', $exam) }}"
                class="px-4 py-2 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 rounded-lg text-sm font-bold shadow-sm">
                Batal
            </a>
        </div>

        <form action="{{ route('admin.soal.import_json_store', $exam) }}" method="POST">
            @csrf
            <input type="hidden" name="json_data" value="{{ $jsonDataEncoded }}">

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-8">
                <div
                    class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex justify-between items-center sticky top-0 z-10">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" id="checkAll"
                            class="w-5 h-5 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500">
                        <span class="ml-3 text-sm font-bold text-slate-700">Pilih Semua (<span
                                id="count_selected">0</span>/{{ count($soals) }})</span>
                    </label>
                    <button type="submit" id="btn_submit" disabled
                        class="px-6 py-2.5 bg-emerald-600 text-white text-sm font-bold rounded-lg hover:bg-emerald-700 shadow disabled:opacity-50 disabled:cursor-not-allowed">
                        Konfirmasi Simpan
                    </button>
                </div>

                <div class="divide-y divide-slate-100 max-h-[70vh] overflow-y-auto">
                    @foreach($soals as $index => $item)
                    <div class="p-6 hover:bg-indigo-50/30 flex gap-4">
                        <div class="pt-1">
                            <input type="checkbox" name="selected_indexes[]" value="{{ $index }}"
                                class="chk-soal w-5 h-5 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 cursor-pointer">
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex gap-2 mb-2">
                                <span
                                    class="bg-slate-200 text-slate-700 text-[10px] px-2 py-0.5 rounded font-bold uppercase">{{
                                    $item['type'] ?? 'unknown' }}</span>
                                <span
                                    class="bg-indigo-100 text-indigo-700 text-[10px] px-2 py-0.5 rounded font-bold uppercase">No.
                                    {{ $index + 1 }}</span>
                            </div>

                            <div
                                class="prose prose-sm max-w-none text-slate-800 mb-4 bg-slate-50 p-4 rounded-lg border border-slate-200">
                                {!! $item['content'] !!}
                            </div>

                            @if(isset($item['options']) && is_array($item['options']))
                            <ul class="space-y-2">
                                @foreach($item['options'] as $opsi)

                                {{-- Render Matching --}}
                                @if(isset($item['type']) && $item['type'] === 'matching')
                                @if(isset($opsi['premise_text']) && isset($opsi['target_text']))
                                <li class="flex items-center gap-3 p-2 rounded bg-white border border-slate-200">
                                    <div class="flex-1 p-3 bg-slate-50 rounded-lg text-sm">{!! $opsi['premise_text'] !!}
                                    </div>
                                    <div class="shrink-0 text-slate-300 px-2"><i class="fas fa-arrow-right"></i></div>
                                    <div class="flex-1 p-3 bg-emerald-50 rounded-lg text-sm font-bold text-emerald-800">
                                        {!! $opsi['target_text'] !!}</div>
                                </li>
                                @endif

                                {{-- Render PG / Essay --}}
                                @elseif(is_string($opsi) || isset($opsi['option_text']) || isset($opsi['text']))

                                @php
                                // Deteksi jika formatnya string (Essay Praktis) atau array object (Pilihan Ganda)
                                if (is_string($opsi)) {
                                $teksJawaban = $opsi;
                                $benar = true; // Jawaban essay dianggap sebagai alternatif benar
                                } else {
                                $teksJawaban = $opsi['option_text'] ?? $opsi['text'] ?? '';
                                $benar = isset($opsi['is_correct']) && filter_var($opsi['is_correct'],
                                FILTER_VALIDATE_BOOLEAN);
                                }
                                @endphp

                                <li
                                    class="flex items-start p-2 rounded {{ $benar ? 'bg-emerald-50 border border-emerald-200' : 'bg-white border border-slate-200' }}">
                                    @if($benar)
                                    <svg class="w-5 h-5 text-emerald-500 mr-2 shrink-0" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    @else
                                    <div class="w-5 h-5 rounded-full border-2 border-slate-300 mr-2 shrink-0"></div>
                                    @endif
                                    <span
                                        class="text-sm {{ $benar ? 'text-emerald-800 font-bold' : 'text-slate-600' }}">{!!
                                        $teksJawaban !!}</span>
                                </li>
                                @endif

                                @endforeach
                            </ul>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkAll = document.getElementById('checkAll');
            const checkboxes = document.querySelectorAll('.chk-soal');
            const btnSubmit = document.getElementById('btn_submit');
            const countDisplay = document.getElementById('count_selected');

            function updateStatus() {
                const checkedCount = document.querySelectorAll('.chk-soal:checked').length;
                countDisplay.innerText = checkedCount;
                btnSubmit.disabled = checkedCount === 0;
            }

            checkAll.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = this.checked);
                updateStatus();
            });

            checkboxes.forEach(cb => cb.addEventListener('change', function() {
                if (!this.checked) checkAll.checked = false;
                if (document.querySelectorAll('.chk-soal:checked').length === checkboxes.length) checkAll.checked = true;
                updateStatus();
            }));
        });
    </script>
</x-app-layout>