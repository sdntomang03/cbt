<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSession extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    // Relasi ke Siswa (Many to Many)
    public function students()
    {
        return $this->belongsToMany(User::class, 'exam_session_user')
            ->withPivot(['status', 'started_at', 'finished_at', 'score'])
            ->withTimestamps();
    }

    // Helper: Cek apakah sesi sedang aktif sekarang?
    public function isOpen()
    {
        return now()->between($this->start_time, $this->end_time);
    }
}
