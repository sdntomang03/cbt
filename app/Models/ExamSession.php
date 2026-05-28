<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamSession extends Model
{
    use BelongsToSchool, HasFactory;

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
            ->withPivot([
                'status',
                'started_at',
                'finished_at',
                'score',
                'violation_count', // <- TAMBAHKAN INI
                'is_locked',        // <- TAMBAHKAN INI
            ])
            ->withTimestamps();
    }

    // Helper: Cek apakah sesi sedang aktif sekarang?
    public function isOpen()
    {
        return now()->between($this->start_time, $this->end_time);
    }

    // Relasi ke tabel School (Sekolah)
    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
