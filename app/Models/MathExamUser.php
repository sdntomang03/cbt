<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MathExamUser extends Model
{
    protected $guarded = [];

    public function exam()
    {
        return $this->belongsTo(MathExam::class, 'math_exam_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
