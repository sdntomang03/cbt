<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // WAJIB ADA
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, HasRoles, Notifiable; // WAJIB ADA HasRoles

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

    // Logika akses Panel Admin/Guru
    public function canAccessPanel(Panel $panel): bool
    {
        // Hanya user dengan role admin atau guru yang bisa login ke Filament
        return $this->hasRole(['admin', 'guru']);
    }

    public function examSessions()
    {
        return $this->belongsToMany(ExamSession::class, 'exam_session_user')
            ->withPivot(['status', 'started_at', 'finished_at', 'score'])
            ->withTimestamps();
    }
}
