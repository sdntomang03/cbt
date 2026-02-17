<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Ujian') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('admin.exams.update', $exam) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Nama Ujian</label>
                            <input type="text" name="title" value="{{ old('title', $exam->title) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Durasi (Menit)</label>
                                <input type="number" name="duration_minutes"
                                    value="{{ old('duration_minutes', $exam->duration_minutes) }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    @foreach(App\Enums\ExamStatus::cases() as $status)
                                    <option value="{{ $status->value }}" {{ $exam->status === $status ? 'selected' : ''
                                        }}>
                                        {{ $status->getLabel() }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-6 space-y-2">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="random_question"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm" {{ $exam->random_question
                                ? 'checked' : '' }}>
                                <span class="ml-2">Acak Urutan Soal</span>
                            </label>
                            <br>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="random_answer"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm" {{ $exam->random_answer ?
                                'checked' : '' }}>
                                <span class="ml-2">Acak Urutan Jawaban</span>
                            </label>
                        </div>

                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.exams.index') }}"
                                class="px-4 py-2 bg-gray-300 rounded-md text-gray-700">Batal</a>
                            <button type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Update
                                Ujian</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
