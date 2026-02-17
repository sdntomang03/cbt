<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * Relasi: Satu Tingkat bisa memiliki banyak Soal
     */
    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
