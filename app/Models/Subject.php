<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use BelongsToSchool, HasFactory;

    protected $fillable = ['name'];

    /**
     * Relasi: Satu Mapel bisa memiliki banyak Soal
     */
    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
