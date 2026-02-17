<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();

            // Relasi ke Ujian
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();

            // TAMBAHAN: Relasi ke User (Pembuat Soal)
            // cascadeOnDelete() berarti jika user dihapus, soal buatannya ikut terhapus.
            // Jika ingin soal tetap ada meski user dihapus, ganti jadi ->nullOnDelete() dan tambahkan ->nullable()
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->enum('type', [
                'single_choice',    // Pilihan Ganda
                'complex_choice',   // Pilihan Ganda Kompleks
                'essay',            // Isian
                'matching',         // Menjodohkan (LeaderLine)
                'true_false',       // Benar Salah
            ]);

            $table->longText('content'); // Soal

            // Metadata
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->foreignId('level_id')->nullable()->constrained('levels')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
