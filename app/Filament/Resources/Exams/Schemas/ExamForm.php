<?php

namespace App\Filament\Resources\Exams\Schemas;

use App\Enums\ExamStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ExamForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('teacher_id')
                    ->relationship('teacher', 'name')
                    ->required(),
                TextInput::make('title')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('duration_minutes')
                    ->required()
                    ->numeric()
                    ->default(60),
                Toggle::make('random_question')
                    ->required(),
                Toggle::make('random_answer')
                    ->required(),
                Select::make('status')
                    ->options(ExamStatus::class)
                    ->default('draft')
                    ->required(),
            ]);
    }
}
