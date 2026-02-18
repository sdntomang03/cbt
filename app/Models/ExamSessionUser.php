<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ExamSessionUser extends Pivot
{
    // Karena ini Pivot Table, kita extend 'Pivot' bukan 'Model'

    protected $table = 'exam_session_user';

    protected $fillable = [
        'exam_session_id',
        'user_id',
        'status',      // not_started, ongoing, completed
        'score',
        'started_at',
        'finished_at',
        'is_locked',       // <--- Tambahan
        'violation_count',  // <--- Tambahan
        'answers',      // Jika masih pakai backup JSON
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'score' => 'float',
    ];
}
