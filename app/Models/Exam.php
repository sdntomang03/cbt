<?php

namespace App\Models;

use App\Enums\ExamStatus;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Vinkla\Hashids\Facades\Hashids;

class Exam extends Model
{
    use BelongsToSchool;

    protected $guarded = ['id'];

    protected $appends = ['hashid'];

    protected function casts(): array
    {
        return [
            'status' => ExamStatus::class,
            'random_question' => 'boolean',
            'random_answer' => 'boolean',
            'is_public' => 'boolean',
            'scoring' => 'string',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * BARU: Relasi ke Exam Sections
     */
    public function sections(): HasMany
    {
        return $this->hasMany(ExamSection::class);
    }

    /**
     * PERBAIKAN: Relasi ke Questions melalui Exam Sections
     */
    public function questions()
    {
        return $this->hasManyThrough(
            Question::class,
            ExamSection::class,
            'exam_id',          // Foreign key di tabel exam_sections
            'exam_section_id',  // Foreign key di tabel questions
            'id',               // Local key di tabel exams
            'id'                // Local key di tabel exam_sections
        );
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ExamSession::class);
    }

    public function examType(): BelongsTo
    {
        return $this->belongsTo(ExamType::class);
    }

    /**
     * PERBAIKAN: Menghitung total peserta menggunakan tabel exam_attempts
     */
    public function totalParticipantsCount()
    {
        return ExamAttempt::whereHas('session', function ($q) {
            $q->where('exam_id', $this->id);
        })->count();
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function invitedTeachers()
    {
        return $this->belongsToMany(User::class, 'exam_invitations', 'exam_id', 'user_id');
    }

    public function getRouteKey()
    {
        return Hashids::encode($this->getKey());
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $decoded = Hashids::decode($value);
        if (empty($decoded)) {
            abort(404, 'Ujian tidak ditemukan atau link tidak valid.');
        }

        return $this->where('id', $decoded[0])->firstOrFail();
    }

    public function getHashidAttribute()
    {
        return Hashids::encode($this->id);
    }
}
