<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamSessionUser extends Model
{
    use BelongsToSchool,HasFactory;

    /**
     * Compatibility alias for legacy controllers. New code must depend on
     * ExamAttempt directly; both names now address the canonical table.
     */
    protected $table = 'exam_attempts';

    protected $fillable = [
        'exam_session_id',
        'user_id',
        'status',
        'started_at',
        'finished_at',
        'raw_score',
        'final_score',
        'violation_count',
        'is_locked',
    ];

    protected $casts = [
        'is_locked' => 'boolean',       // Penting agar Javascript baca true/false
        'violation_count' => 'integer',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'raw_score' => 'decimal:2',
        'final_score' => 'decimal:2',
    ];

    /** Legacy read alias. Do not persist to a non-existent `score` column. */
    public function getScoreAttribute(): float
    {
        return (float) $this->final_score;
    }

    /**
     * Relasi ke ExamSession (Sesi Ujian)
     * Ini yang dicari oleh whereHas('session')
     */
    public function session()
    {
        return $this->belongsTo(ExamSession::class, 'exam_session_id');
    }

    /**
     * Relasi ke User (Siswa)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
