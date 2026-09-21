<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. BUAT TABEL SCORING PROFILES (BARU)
        Schema::create('scoring_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('name');
            $table->json('rules');
            $table->timestamps();
        });

        // 2. BUAT TABEL EXAM TYPES[cite: 9]
        Schema::create('exam_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        // 3. BUAT TABEL EXAMS[cite: 9]
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();

            $table->foreignId('exam_type_id')->nullable()->constrained('exam_types')->nullOnDelete();
            $table->foreignId('scoring_profile_id')->nullable()->constrained('scoring_profiles')->nullOnDelete(); // TAMBAHAN
            $table->foreignId('level_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();

            $table->string('title');
            $table->string('slug')->unique();
            $table->integer('duration_minutes')->default(60);
            $table->boolean('random_question')->default(false);
            $table->boolean('random_answer')->default(false);
            $table->boolean('show_explanation')->default(false);
            $table->enum('status', ['draft', 'published', 'closed'])->default('draft');
            $table->boolean('is_public')->default(false);
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            $table->string('meta_description', 160)->nullable();
            $table->string('meta_keywords', 255)->nullable();
            $table->string('thumbnail')->nullable();
            $table->timestamps();
        });

        // 4. BUAT TABEL EXAM SECTIONS (BARU)
        Schema::create('exam_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('scoring_profile_id')->nullable()->constrained('scoring_profiles')->nullOnDelete();
            $table->string('name'); // cth: TWK, TIU, TKP
            $table->integer('order')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_sections');
        Schema::dropIfExists('exams');
        Schema::dropIfExists('exam_types');
        Schema::dropIfExists('scoring_profiles');
    }
};
