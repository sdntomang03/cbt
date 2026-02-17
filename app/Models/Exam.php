<?php

namespace App\Models;

use App\Enums\ExamStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    protected $guarded = ['id'];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    protected function casts(): array
    {
        return [
            'status' => ExamStatus::class, // Laravel 12 Style
            'random_question' => 'boolean',
            'random_answer' => 'boolean',
        ];
    }

    public function participants()
    {
        return $this->hasMany(ExamSession::class);
    }
}
