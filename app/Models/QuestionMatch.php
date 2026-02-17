<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionMatch extends Model
{
    // PENTING: Tambahkan ini agar bisa createMany
    protected $guarded = ['id'];

    // Atau jika pakai fillable:
    // protected $fillable = ['question_id', 'premise_text', 'target_text'];
}
