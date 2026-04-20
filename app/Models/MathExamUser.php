<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class MathExamUser extends Model
{
    use BelongsToSchool;

    protected $guarded = [];

    public function exam()
    {
        return $this->belongsTo(MathExam::class, 'math_exam_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function questions()
    {
        // Hubungkan berdasarkan student_id
        return $this->hasMany(MathExamQuestion::class, 'student_id', 'student_id');
    }
}
