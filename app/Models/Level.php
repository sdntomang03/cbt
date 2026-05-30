<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    use BelongsToSchool, HasFactory;

    protected $fillable = ['name'];

    /**
     * Relasi: Satu Tingkat bisa memiliki banyak Soal
     */
    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function exams()
    {
        // Asumsi foreign key di tabel exams adalah 'level_id'
        return $this->hasMany(Exam::class);
    }
}
