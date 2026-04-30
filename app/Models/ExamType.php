<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamType extends Model
{
    protected $fillable = ['school_id', 'name'];

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }
}
