<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    // Izinkan semua kolom untuk diisi secara massal (mass assignment)
    protected $guarded = [];

    // Relasi: Satu Sekolah memiliki Banyak User (Guru, Siswa, Admin)
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Relasi: Satu Sekolah memiliki Banyak Kelas (Nanti kita buat)
    // public function classrooms()
    // {
    //     return $this->hasMany(Classroom::class);
    // }
}
