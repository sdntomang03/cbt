<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class StudentAnswer extends Model
{
    use BelongsToSchool;

    protected $guarded = ['id'];

    protected $casts = [
        'answer' => 'json',
        'is_doubtful' => 'boolean',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
