<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'section_id',
        'scoring_profile_id',
        'order',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function getNameAttribute(): ?string
    {
        return $this->section?->name;
    }

    public function getAbbreviationAttribute(): ?string
    {
        return $this->section?->abbreviation;
    }

    public function scoringProfile()
    {
        return $this->belongsTo(ScoringProfile::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Relasi ke peserta ujian (User) melalui tabel exam_attempts
     */
    public function students()
    {
        return $this->belongsToMany(User::class, 'exam_attempts', 'exam_session_id', 'user_id')
            ->withPivot(
                'status',
                'started_at',
                'finished_at',
                'raw_score',
                'final_score',
                'is_locked',
                'violation_count'
            )
            ->withTimestamps();
    }
}
