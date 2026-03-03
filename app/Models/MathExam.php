<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MathExam extends Model
{
    protected $guarded = [];

    protected $casts = ['types' => 'array', 'digits' => 'array'];

    // Relasi ke tabel pivot (siswa yang ikut ujian ini)
    public function examUsers()
    {
        return $this->hasMany(MathExamUser::class);
    }

    // Relasi ke semua soal di ujian ini
    public function questions()
    {
        return $this->hasMany(MathExamQuestion::class);
    }
}
