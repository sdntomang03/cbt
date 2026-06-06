<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'slug', 'description', 'content',
        'subject_id', 'level_id', 'author_id',
        'thumbnail', 'video_url', 'document_path',
        'is_public', 'is_premium', 'status', 'published_at',
        'estimated_time_minutes', 'reward_points', 'view_count',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'is_premium' => 'boolean',
        'published_at' => 'datetime',
    ];

    // --- RELASI ---
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
