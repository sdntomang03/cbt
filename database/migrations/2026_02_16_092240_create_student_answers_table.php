<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_answers', function (Blueprint $table) {
            $table->id();
            // UBAH: Menggunakan exam_attempt_id sebagai induk[cite: 13]
            $table->foreignId('exam_attempt_id')->constrained('exam_attempts')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_id')->nullable()->constrained()->cascadeOnDelete();

            // UBAH: Tipe data answer menjadi json untuk mempermudah multiple choice/matching[cite: 13]
            $table->json('answer')->nullable();

            $table->decimal('score', 8, 2)->default(0);
            $table->boolean('is_doubtful')->default(false);

            $table->timestamps();

            // CONSTRAINT: Satu percobaan ujian hanya punya 1 jawaban untuk 1 soal[cite: 13]
            $table->unique(['exam_attempt_id', 'question_id'], 'unique_answer_per_attempt');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_answers');
    }
};
