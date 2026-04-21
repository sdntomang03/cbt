<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistrationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'default_exam_session_ids', // Sesuaikan nama kolom
    ];

    protected $casts = [
        'default_exam_session_ids' => 'array', // Cast ke bentuk Array
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}