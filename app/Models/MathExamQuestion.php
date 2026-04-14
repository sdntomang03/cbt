<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class MathExamQuestion extends Model
{
    use BelongsToSchool;

    protected $guarded = [];
}
