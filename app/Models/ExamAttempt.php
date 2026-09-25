<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_session_id',
        'user_id',
        'status',
        'started_at',
        'finished_at',
        'raw_score',
        'final_score',
        'is_locked',
        'violation_count',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'is_locked' => 'boolean',
        'raw_score' => 'decimal:2',
        'final_score' => 'decimal:2',
    ];

    /**
     * Relasi ke Sesi Ujian
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(ExamSession::class, 'exam_session_id');
    }

    /**
     * Relasi ke Peserta/User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke Jawaban Siswa
     */
    public function answers(): HasMany
    {
        return $this->hasMany(StudentAnswer::class, 'exam_attempt_id');
    }

    public function detailNilai(): HasMany
    {
        return $this->hasMany(DetailNilai::class, 'exam_attempt_id');
    }
}
