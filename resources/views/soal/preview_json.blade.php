<x-app-layout>
    <div class="max-w-5xl mx-auto px-4 py-8">

        <div class="mb-6 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-slate-800 uppercase tracking-tight">Preview Data Soal</h2>
                <p class="text-sm text-slate-500 mt-1">Pilih soal mana saja yang ingin dimasukkan ke dalam database.</p>
            </div>
            <a href="{{ route('admin.soal.import_json_view', $exam->id) }}"
                class="px-4 py-2 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 rounded-lg text-sm font-bold shadow-sm transition-colors">
                Batal & Kembali
            </a>
        </div>

        <form action="{{ route('admin.soal.import_json_store', $exam->id) }}" method="POST">
            @csrf

            <input type="hidden" name="json_data" value="{{ $jsonDataEncoded }}">

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-8">
                <div
                    class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex justify-between items-center sticky top-0 z-10">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" id="checkAll"
                            class="w-5 h-5 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500">
                        <span class="ml-3 text-sm font-bold text-slate-700">Pilih Semua Soal (<span
                                id="count_selected">0</span> dari {{ count($soals) }})</span>
                    </label>
                    <button type="submit" id="btn_submit" disabled
                        class="px-6 py-2.5 bg-emerald-600 text-white text-sm font-bold rounded-lg hover:bg-emerald-700 transition-colors shadow disabled:opacity-50 disabled:cursor-not-allowed flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        Konfirmasi Tambah
                    </button>
                </div>

                <div class="divide-y divide-slate-100 max-h-[70vh] overflow-y-auto">
                    @foreach($soals as $index => $item)
                    @php
                    // Decode base64 hanya untuk tampilan jika terenkripsi
                    $isBase64 = (base64_encode(base64_decode($item['content'], true)) === $item['content']);
                    $kontenView = $isBase64 ? base64_decode($item['content']) : $item['content'];
                    @endphp

                    <div class="p-6 hover:bg-indigo-50/30 transition-colors flex gap-4">
                        <div class="pt-1">
                            <input type="checkbox" name="selected_indexes[]" value="{{ $index }}"
                                class="chk-soal w-5 h-5 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 cursor-pointer">
                        </div>

                        <div class="flex-1">
                            <div class="flex gap-2 mb-2">
                                <span
                                    class="bg-slate-200 text-slate-700 text-[10px] px-2 py-0.5 rounded font-bold uppercase tracking-widest">{{
                                    $item['type'] ?? 'Tipe Kosong' }}</span>
                                <span
                                    class="bg-indigo-100 text-indigo-700 text-[10px] px-2 py-0.5 rounded font-bold uppercase">No.
                                    {{ $index + 1 }}</span>
                            </div>

                            <div
                                class="prose prose-sm max-w-none text-slate-800 mb-4 bg-slate-50 p-4 rounded-lg border border-slate-200">
                                {!! $kontenView !!}
                            </div>

                            @if(isset($item['options']) && is_array($item['options']))
                            <ul class="space-y-2">
                                @foreach($item['options'] as $opsi)
                                @if(!empty($opsi['text']))
                                @php $benar = isset($opsi['is_correct']) && $opsi['is_correct'] == true; @endphp
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
                                        $opsi['text'] !!}</span>
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

            checkboxes.forEach(cb => {
                cb.addEventListener('change', function() {
                    if (!this.checked) checkAll.checked = false;
                    if (document.querySelectorAll('.chk-soal:checked').length === checkboxes.length) checkAll.checked = true;
                    updateStatus();
                });
            });
        });
    </script>
</x-app-layout>