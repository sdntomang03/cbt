<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $guarded = ['id'];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
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

    public function matches(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(QuestionMatch::class);
    }
}
