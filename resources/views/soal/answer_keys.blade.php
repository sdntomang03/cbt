<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <a href="{{ route('admin.exams.soal.index', $exam) }}"
                    class="inline-flex items-center gap-2 text-sm font-bold text-slate-500 hover:text-indigo-600 mb-2">
                    <i class="fas fa-arrow-left"></i>
                    Kembali ke daftar soal
                </a>
                <h2 class="font-black text-xl text-slate-800">Kunci Jawaban</h2>
                <p class="text-sm text-slate-500 mt-1">{{ $exam->title }}</p>
            </div>
            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-50 text-emerald-700 text-sm font-bold">
                <i class="fas fa-cloud-arrow-up"></i>
                Perubahan tersimpan otomatis
            </span>
        </div>
    </x-slot>

    <div class="py-8 min-h-screen">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 px-4 space-y-5">
            @forelse($questions as $question)
                @php
                    $typeLabels = [
                        'single_choice' => 'Pilihan Ganda',
                        'complex_choice' => 'Pilihan Ganda Kompleks',
                        'tkp' => 'TKP Berbobot',
                        'true_false' => 'Benar / Salah',
                        'matching' => 'Menjodohkan',
                        'essay' => 'Isian Singkat',
                    ];
                @endphp
                <section class="answer-key-card bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden"
                    data-type="{{ $question->type }}"
                    data-save-url="{{ route('admin.exams.soal.answer-keys.update', [$exam, $question]) }}">
                    <div class="px-5 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-black">
                                #{{ $questions->firstItem() + $loop->index }}
                            </span>
                            <div>
                                <span class="block text-sm font-black text-slate-800">
                                    {{ $typeLabels[$question->type] ?? $question->type }}
                                </span>
                                @if($question->section?->section)
                                    <span class="text-xs text-slate-500">
                                        {{ $question->section->section->abbreviation }} - {{ $question->section->section->name }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span id="key-status-{{ $question->id }}" class="text-xs font-bold text-slate-400" aria-live="polite">
                                {{ $question->type === 'matching' ? 'Kunci mengikuti pasangan' : 'Belum ada perubahan' }}
                            </span>
                            <a href="{{ route('admin.exams.soal.edit', [$exam, $question]) }}" target="_blank" rel="noopener noreferrer"
                                class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100 text-xs font-bold transition"
                                aria-label="Edit soal #{{ $questions->firstItem() + $loop->index }}">
                                <i class="fas fa-pen"></i>
                                <span>Edit</span>
                            </a>
                        </div>
                    </div>

                    <div class="answer-key-content p-5">
                        <div class="prose-custom max-w-none text-sm leading-6 text-slate-700 mb-5">
                            {!! $question->content ?: 'Soal tanpa teks.' !!}
                        </div>

                        @if($question->type === 'single_choice')
                            <fieldset class="space-y-2">
                                <legend class="text-xs font-black uppercase tracking-wide text-slate-500 mb-2">Pilih satu kunci jawaban</legend>
                                @forelse($question->options as $option)
                                    <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:border-indigo-300 cursor-pointer transition">
                                        <input type="radio" name="answer-{{ $question->id }}" value="{{ $option->id }}"
                                            @checked($option->is_correct) class="text-emerald-600 focus:ring-emerald-500">
                                        <div class="prose-custom min-w-0 text-sm text-slate-700">{!! $option->option_text ?: 'Opsi tanpa teks' !!}</div>
                                    </label>
                                @empty
                                    <p class="text-sm text-amber-700 bg-amber-50 rounded-lg p-3">Soal ini belum memiliki opsi jawaban.</p>
                                @endforelse
                            </fieldset>
                        @elseif(in_array($question->type, ['complex_choice', 'essay'], true))
                            <fieldset class="space-y-2">
                                <legend class="text-xs font-black uppercase tracking-wide text-slate-500 mb-2">
                                    {{ $question->type === 'complex_choice' ? 'Pilih semua jawaban yang benar' : 'Pilih semua jawaban yang diterima' }}
                                </legend>
                                @forelse($question->options as $option)
                                    <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:border-indigo-300 cursor-pointer transition">
                                        <input type="checkbox" name="answer-{{ $question->id }}" value="{{ $option->id }}"
                                            @checked($option->is_correct) class="rounded text-emerald-600 focus:ring-emerald-500">
                                        <div class="prose-custom min-w-0 text-sm text-slate-700">{!! $option->option_text ?: 'Opsi tanpa teks' !!}</div>
                                    </label>
                                @empty
                                    <p class="text-sm text-amber-700 bg-amber-50 rounded-lg p-3">Soal ini belum memiliki opsi jawaban.</p>
                                @endforelse
                            </fieldset>
                        @elseif($question->type === 'true_false')
                            <div class="space-y-3">
                                <p class="text-xs font-black uppercase tracking-wide text-slate-500">Tentukan kunci untuk setiap pernyataan</p>
                                @forelse($question->options as $option)
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 rounded-xl border border-slate-200">
                                        <div class="prose-custom min-w-0 text-sm text-slate-700">{!! $option->option_text ?: 'Pernyataan tanpa teks' !!}</div>
                                        <div class="flex items-center gap-4 shrink-0">
                                            <label class="inline-flex items-center gap-2 text-sm text-slate-600 cursor-pointer">
                                                <input type="radio" name="answer-{{ $question->id }}-{{ $option->id }}" value="1"
                                                    @checked($option->is_correct) class="text-emerald-600 focus:ring-emerald-500">
                                                Benar
                                            </label>
                                            <label class="inline-flex items-center gap-2 text-sm text-slate-600 cursor-pointer">
                                                <input type="radio" name="answer-{{ $question->id }}-{{ $option->id }}" value="0"
                                                    @checked(!$option->is_correct) class="text-rose-600 focus:ring-rose-500">
                                                Salah
                                            </label>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm text-amber-700 bg-amber-50 rounded-lg p-3">Soal ini belum memiliki pernyataan.</p>
                                @endforelse
                            </div>
                        @elseif($question->type === 'tkp')
                            <div class="space-y-3">
                                <p class="text-xs font-black uppercase tracking-wide text-slate-500">Atur bobot nilai setiap opsi</p>
                                @forelse($question->options as $option)
                                    <label class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 rounded-xl border border-slate-200">
                                        <div class="prose-custom min-w-0 text-sm text-slate-700">{!! $option->option_text ?: 'Opsi tanpa teks' !!}</div>
                                        <span class="flex items-center gap-2 text-sm font-bold text-slate-500">
                                            Bobot
                                            <input type="number" min="0" max="999999.99" step="0.01" value="{{ $option->score_weight }}"
                                                data-option-id="{{ $option->id }}"
                                                class="tkp-weight w-28 rounded-lg border-slate-300 text-sm text-slate-800 focus:border-indigo-500 focus:ring-indigo-500">
                                        </span>
                                    </label>
                                @empty
                                    <p class="text-sm text-amber-700 bg-amber-50 rounded-lg p-3">Soal ini belum memiliki opsi jawaban.</p>
                                @endforelse
                            </div>
                        @elseif($question->type === 'matching')
                            <div class="space-y-3">
                                <p class="text-xs font-black uppercase tracking-wide text-slate-500">Pasangan berikut adalah kunci menjodohkannya</p>
                                @forelse($question->matches as $match)
                                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 p-3 rounded-xl border border-slate-200 text-sm text-slate-700">
                                        <div class="prose-custom min-w-0 flex-1">{!! $match->premise_text ?: 'Pernyataan kosong' !!}</div>
                                        <i class="fas fa-arrow-right text-indigo-400"></i>
                                        <div class="prose-custom min-w-0 flex-1">{!! $match->target_text ?: 'Pasangan kosong' !!}</div>
                                    </div>
                                @empty
                                    <p class="text-sm text-amber-700 bg-amber-50 rounded-lg p-3">Soal ini belum memiliki pasangan.</p>
                                @endforelse
                                <a href="{{ route('admin.exams.soal.edit', [$exam, $question]) }}"
                                    class="inline-flex items-center gap-2 text-sm font-bold text-indigo-600 hover:text-indigo-800">
                                    <i class="fas fa-pen"></i>
                                    Ubah pasangan
                                </a>
                            </div>
                        @endif
                    </div>
                </section>
            @empty
                <div class="bg-white rounded-2xl border border-slate-200 p-10 text-center text-slate-500">
                    Belum ada soal di ujian ini.
                </div>
            @endforelse

            @if($questions->hasPages())
                <div>{{ $questions->links() }}</div>
            @endif
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/katex.min.css">
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/katex.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/contrib/auto-render.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.answer-key-content .ql-formula').forEach(element => {
                const formula = element.getAttribute('data-value');
                if (formula) {
                    window.katex.render(formula, element, { throwOnError: false });
                }
            });

            document.querySelectorAll('.answer-key-content').forEach(container => {
                window.renderMathInElement(container, {
                    delimiters: [
                        { left: '$$', right: '$$', display: true },
                        { left: '$', right: '$', display: false },
                        { left: '\\(', right: '\\)', display: false },
                        { left: '\\[', right: '\\]', display: true }
                    ],
                    throwOnError: false
                });
            });
        });
    </script>
    <script>
        (() => {
            const saveTimers = new WeakMap();
            const saveStates = new WeakMap();
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            const setStatus = (card, text, className) => {
                const status = card.querySelector('[aria-live="polite"]');
                status.textContent = text;
                status.className = `text-xs font-bold ${className}`;
            };

            const makePayload = (card) => {
                const type = card.dataset.type;
                if (type === 'single_choice') {
                    const selected = card.querySelector('input[type="radio"]:checked');
                    return selected ? { correct_option_id: Number(selected.value) } : null;
                }

                if (type === 'complex_choice' || type === 'essay') {
                    return {
                        correct_option_ids: Array.from(card.querySelectorAll('input[type="checkbox"]:checked'))
                            .map(input => Number(input.value))
                    };
                }

                if (type === 'true_false') {
                    const rows = Array.from(card.querySelectorAll('input[type="radio"]'))
                        .reduce((groups, input) => {
                            const selected = input.checked;
                            if (selected) {
                                const optionId = input.name.split('-').pop();
                                groups.set(optionId, { option_id: Number(optionId), is_correct: input.value === '1' });
                            }
                            return groups;
                        }, new Map());

                    return { answers: Array.from(rows.values()) };
                }

                if (type === 'tkp') {
                    return {
                        weights: Array.from(card.querySelectorAll('.tkp-weight')).map(input => ({
                            option_id: Number(input.dataset.optionId),
                            score_weight: input.value
                        }))
                    };
                }

                return null;
            };

            const saveCard = async (card) => {
                const state = saveStates.get(card) || { saving: false, pending: false };
                saveStates.set(card, state);
                if (state.saving) {
                    state.pending = true;
                    return;
                }

                const payload = makePayload(card);
                if (!payload) {
                    setStatus(card, 'Pilih kunci jawaban terlebih dahulu', 'text-amber-600');
                    return;
                }

                state.saving = true;
                setStatus(card, 'Menyimpan...', 'text-indigo-600');
                try {
                    const response = await fetch(card.dataset.saveUrl, {
                        method: 'PUT',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify(payload)
                    });
                    const result = await response.json();
                    if (!response.ok) {
                        const validationMessage = result.errors
                            ? Object.values(result.errors).flat()[0]
                            : null;
                        throw new Error(validationMessage || result.message || 'Kunci jawaban gagal disimpan.');
                    }
                    setStatus(card, 'Tersimpan', 'text-emerald-600');
                } catch (error) {
                    setStatus(card, error.message || 'Gagal menyimpan', 'text-rose-600');
                } finally {
                    state.saving = false;
                    if (state.pending) {
                        state.pending = false;
                        saveCard(card);
                    }
                }
            };

            document.querySelectorAll('.answer-key-card').forEach(card => {
                card.addEventListener('change', event => {
                    if (event.target.matches('.tkp-weight') && (event.target.value === '' || Number(event.target.value) < 0)) {
                        setStatus(card, 'Bobot harus bernilai nol atau lebih', 'text-rose-600');
                        return;
                    }

                    clearTimeout(saveTimers.get(card));
                    saveTimers.set(card, setTimeout(() => saveCard(card), 150));
                });
            });
        })();
    </script>
</x-app-layout>
