<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailNilai extends Model
{
    use HasFactory;

    protected $table = 'detail_nilai';

    protected $fillable = [
        'user_id',
        'exam_attempt_id',
        'exam_session_id',
        'exam_id',
        'section_id',
        'scoring_profile_id',
        'result_mode',
        'nilai',
        'benar',
        'salah',
        'tidak_dijawab',
        'earned',
        'maximum',
        'display_score',
        'is_point_based',
        'score_label',
        'metadata',
    ];

    protected $casts = [
        'nilai' => 'decimal:2',
        'earned' => 'decimal:2',
        'maximum' => 'decimal:2',
        'display_score' => 'decimal:2',
        'is_point_based' => 'boolean',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class, 'exam_attempt_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ExamSession::class, 'exam_session_id');
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(ExamSection::class, 'section_id');
    }

    public function scoringProfile(): BelongsTo
    {
        return $this->belongsTo(ScoringProfile::class);
    }
}
