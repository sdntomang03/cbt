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
        // 1. BUAT TABEL MASTER TERLEBIH DAHULU
        Schema::create('exam_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        // 2. BARU BUAT TABEL EXAMS
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();

            // Tambahkan kolom foreign key ke exam_types
            $table->foreignId('exam_type_id')->nullable()->constrained('exam_types')->nullOnDelete();

            // Tambahan Level dan Mata Pelajaran (Subject)
            $table->foreignId('level_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();

            $table->string('title');
            $table->string('slug')->unique();
            $table->integer('duration_minutes')->default(60);
            $table->boolean('random_question')->default(false);
            $table->boolean('random_answer')->default(false);
            $table->enum('status', ['draft', 'published', 'closed'])->default('draft');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Urutan hapus harus kebalikan dari urutan buat
        Schema::dropIfExists('exams');
        Schema::dropIfExists('exam_types');
    }
};
