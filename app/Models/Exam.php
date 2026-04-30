<?php

namespace App\Models;

use App\Enums\ExamStatus;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Exam extends Model
{
    use BelongsToSchool;

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

    public function examType(): BelongsTo
    {
        return $this->belongsTo(ExamType::class);
    }

    public function totalParticipantsCount()
    {
        return \App\Models\ExamSession::where('exam_id', $this->id)
            ->join('exam_session_user', 'exam_sessions.id', '=', 'exam_session_user.exam_session_id')
            ->count();
    }

    /**
     * Relasi ke Tingkat/Level Kelas
     */
    public function level(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    /**
     * Relasi ke Mata Pelajaran
     */
    public function subject(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
