<?php

namespace App\Models;

use App\Enums\ExamStatus;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Vinkla\Hashids\Facades\Hashids;

class Exam extends Model
{
    use BelongsToSchool;

    protected $guarded = ['id'];

    protected $appends = ['hashid'];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function questions()
    {
        // Ini artinya tabel 'questions' punya kolom 'exam_id'
        return $this->hasMany(Question::class, 'exam_id');
    }

    protected function casts(): array
    {
        return [
            'status' => ExamStatus::class, // Laravel 12 Style
            'random_question' => 'boolean',
            'random_answer' => 'boolean',
            'is_public' => 'boolean',
        ];
    }

    public function participants()
    {
        return $this->hasMany(ExamSession::class);
    }

    public function examType(): BelongsTo
    {
        return $this->belongsTo(ExamType::class);
    }

    public function totalParticipantsCount()
    {
        return ExamSession::where('exam_id', $this->id)
            ->join('exam_session_user', 'exam_sessions.id', '=', 'exam_session_user.exam_session_id')
            ->count();
    }

    /**
     * Relasi ke Tingkat/Level Kelas
     */
    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    /**
     * Relasi ke Mata Pelajaran
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function getRouteKey()
    {
        return Hashids::encode($this->getKey());
    }

    /**
     * 2. Fungsi ini otomatis mengubah Huruf Acak kembali menjadi ID
     * saat siswa mengakses URL, sebelum masuk ke Controller.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        // Decode huruf acak (misal 'jR8z9X') kembali ke array angka (misal [1])
        $decoded = Hashids::decode($value);

        // Jika siswa asal ketik URL acak dan gagal di-decode, lempar ke halaman 404
        if (empty($decoded)) {
            abort(404, 'Ujian tidak ditemukan atau link tidak valid.');
        }

        // Cari data di database menggunakan ID asli yang sudah dikembalikan
        return $this->where('id', $decoded[0])->firstOrFail();
    }

    public function getHashidAttribute()
    {
        return Hashids::encode($this->id);
    }
}
