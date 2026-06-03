<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use BelongsToSchool;

    protected $guarded = ['id'];

    public function exams()
    {
        return $this->belongsToMany(Exam::class, 'exam_question');
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class);
    }

    // Relasi ke Mata Pelajaran
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    // Relasi ke Tingkat
    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(QuestionMatch::class);
    }
}
