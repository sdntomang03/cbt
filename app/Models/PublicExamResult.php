<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicExamResult extends Model
{
    protected $fillable = [
        'exam_id', 'nama_peserta', 'asal_sekolah', 'score',
        'correct_count', 'wrong_count', 'unanswered_count', 'duration_seconds',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
}
