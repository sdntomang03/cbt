<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();

            // 1. INFORMASI DASAR
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable(); // Sinopsis singkat modul
            $table->longText('content')->nullable(); // Isi materi (Rich Text / HTML)

            // 2. KATEGORISASI (Relasi)
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('level_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete(); // Guru pembuat

            // 3. MEDIA & LAMPIRAN (Sangat penting di Bimbel)
            $table->string('thumbnail')->nullable(); // Cover modul
            $table->string('video_url')->nullable(); // Link YouTube/Vimeo penjelasan guru
            $table->string('document_path')->nullable(); // File PDF rangkuman untuk di-download

            // 4. KONTROL AKSES (Access Control)
            $table->boolean('is_public')->default(true); // Bisa dilihat tamu atau harus login?
            $table->boolean('is_premium')->default(false); // Terkunci untuk akun berbayar/aktif?
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft'); // Status publikasi
            $table->timestamp('published_at')->nullable(); // Kapan modul ini dirilis (untuk fitur penjadwalan)

            // 5. GAMIFIKASI & UX (User Experience)
            $table->integer('estimated_time_minutes')->default(10); // "Waktu baca: 10 Menit"
            $table->integer('reward_points')->default(0); // Poin yang didapat siswa jika selesai membaca modul ini
            $table->integer('view_count')->default(0); // Menghitung modul paling populer

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
