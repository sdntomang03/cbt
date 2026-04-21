<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'user_id', // ID wali kelas
        'name',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    // Relasi ke Wali Kelas (User)
    public function homeroomTeacher()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke Siswa (Banyak Siswa lewat tabel pivot classroom_student)
    public function students()
    {
        return $this->belongsToMany(User::class, 'classroom_student', 'classroom_id', 'student_id')
                    ->withTimestamps(); // Agar timestamps di pivot table otomatis terisi
    }

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }
}