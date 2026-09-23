<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemAnalysisConclusion extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'session_ids' => 'array',
        'generated_at' => 'datetime',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function session()
    {
        return $this->belongsTo(ExamSession::class, 'exam_session_id');
    }
}
