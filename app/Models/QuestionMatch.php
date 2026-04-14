<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class QuestionMatch extends Model
{
    use BelongsToSchool;

    // PENTING: Tambahkan ini agar bisa createMany
    protected $guarded = ['id'];

    // Atau jika pakai fillable:
    // protected $fillable = ['question_id', 'premise_text', 'target_text'];
}
