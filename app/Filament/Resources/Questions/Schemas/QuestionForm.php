<?php

namespace App\Filament\Resources\Questions\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class QuestionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // BAGIAN 1: Detail Soal
                Section::make('Detail Soal')
                    ->schema([
                        Select::make('exam_id')
                            ->relationship('exam', 'title')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Mata Ujian'),

                        Select::make('type')
                            ->options([
                                'single_choice' => 'Pilihan Ganda',
                                'complex_choice' => 'Pilihan Ganda Kompleks',
                                'true_false' => 'Benar / Salah',
                                'matching' => 'Menjodohkan',
                                'essay' => 'Esai',
                            ])
                            ->live() // Aktifkan reaktivitas
                            ->afterStateUpdated(fn (Set $set) => $set('options', [])) // Reset opsi jika tipe ganti
                            ->required()
                            ->label('Tipe Soal'),

                        // Gunakan RichEditor agar bisa upload gambar/tabel di soal
                        RichEditor::make('content')
                            ->label('Pertanyaan')
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                // BAGIAN 2: Repeater Opsi (Dinamis)
                Section::make('Opsi Jawaban')
                    ->schema([
                        Repeater::make('options')
                            ->relationship()
                            ->schema(function (Get $get) {
                                // Ambil nilai 'type' dari form induk
                                // '../' digunakan untuk naik level dari repeater ke form utama
                                $type = $get('../../type');

                                // 1. Input Utama (Teks Opsi / Sisi Kiri)
                                $schema = [
                                    TextInput::make('option_text')
                                        ->label($type === 'matching' ? 'Sisi Kiri (Pertanyaan)' : 'Teks Opsi')
                                        ->required()
                                        ->columnSpan(3),
                                ];

                                // 2. Input Tambahan (Sisi Kanan - Khusus Matching)
                                if ($type === 'matching') {
                                    $schema[] = TextInput::make('matching_pair')
                                        ->label('Sisi Kanan (Jawaban)')
                                        ->required()
                                        ->columnSpan(3);
                                }

                                // 3. Checkbox Benar (Khusus PG / TrueFalse)
                                if (! in_array($type, ['matching', 'essay'])) {
                                    $schema[] = Checkbox::make('is_correct')
                                        ->label('Benar')
                                        ->inline(false)
                                        ->columnSpan(1);
                                }

                                return $schema;
                            })
                            ->columns(7) // Grid layout (3+3+1)
                            ->defaultItems(1)
                            ->reorderableWithButtons()
                            ->addActionLabel('Tambah Opsi')
                            // Sembunyikan section ini jika tipe soal adalah Essay
                            ->hidden(fn (Get $get) => $get('type') === 'essay'),
                    ]),
            ]);
    }
}
