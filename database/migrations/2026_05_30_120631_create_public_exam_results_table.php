<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('public_exam_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->string('nama_peserta');
            $table->string('asal_sekolah');
            $table->integer('score');
            $table->integer('correct_count');
            $table->integer('wrong_count');
            $table->integer('unanswered_count');
            $table->integer('duration_seconds')->nullable(); // Opsional: Untuk tie-breaker ranking jika nilai sama
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('public_exam_results');
    }
};
