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
            // Link ke Jadwal Sesi
            $table->foreignId('exam_session_id')->constrained()->cascadeOnDelete();
            // Link ke Siswa (PENTING!)
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Link ke Soal
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();

            // Data Jawaban
            $table->string('answer')->nullable(); // String cukup untuk PG, Text/LongText jika Essai
            // Jika soal kompleks (multiple choice), boleh pakai JSON:
            // $table->json('answer_complex')->nullable();

            $table->decimal('score', 8, 2)->default(0); // Decimal lebih akurat dari float untuk nilai
            $table->boolean('is_doubtful')->default(false);

            $table->timestamps();

            // CONSTRAINT: Satu siswa hanya punya 1 jawaban untuk 1 soal di 1 sesi
            $table->unique(['exam_session_id', 'user_id', 'question_id'], 'unique_answer_per_session');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_answers');
    }
};
