<?php

namespace App\Models;

use App\Enums\ExamStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Exam extends Model
{
    protected $guarded = ['id'];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function questions()
    {
        // Ini artinya tabel 'questions' punya kolom 'exam_id'
        return $this->hasMany(Question::class, 'exam_id');
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
