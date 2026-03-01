<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // WAJIB ADA
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

// TAMBAHKAN TRAIT MULTI-TENANT DI SINI JIKA SUDAH DIBUAT
// use App\Traits\BelongsToSchool;

class User extends Authenticatable implements MustVerifyEmail
{
    use BelongsToSchool, HasFactory, HasRoles,Notifiable;
    // Jika Trait BelongsToSchool sudah dibuat di langkah sebelumnya, nyalakan baris di bawah ini:
    // use BelongsToSchool;

    protected $guarded = ['id'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function examSessions()
    {
        return $this->belongsToMany(ExamSession::class, 'exam_session_user')
            ->withPivot([
                'status',
                'started_at',
                'finished_at',
                'score',
                'violation_count', // <--- WAJIB ADA UNTUK MONITORING PROKTOR
                'is_locked',        // <--- WAJIB ADA UNTUK MONITORING PROKTOR
            ])
            ->withTimestamps();
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
