<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamSessionUser extends Model
{
    use HasFactory;

    protected $table = 'exam_session_user';

    protected $fillable = [
        'exam_session_id',
        'user_id',
        'status',
        'started_at',
        'finished_at',
        'score',
        'violation_count',
        'is_locked',
    ];

    protected $casts = [
        'is_locked' => 'boolean',       // Penting agar Javascript baca true/false
        'violation_count' => 'integer',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

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
