<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_nilai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_attempt_id')->constrained('exam_attempts')->cascadeOnDelete();
            $table->foreignId('exam_session_id')->constrained('exam_sessions')->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('exam_sections')->cascadeOnDelete();
            $table->foreignId('scoring_profile_id')->nullable()->constrained('scoring_profiles')->nullOnDelete();

            $table->string('result_mode')->default('average');
            $table->decimal('nilai', 8, 2)->default(0);
            $table->integer('benar')->default(0);
            $table->integer('salah')->default(0);
            $table->integer('tidak_dijawab')->default(0);
            $table->decimal('earned', 8, 2)->default(0);
            $table->decimal('maximum', 8, 2)->default(0);
            $table->decimal('display_score', 8, 2)->default(0);
            $table->boolean('is_point_based')->default(false);
            $table->string('score_label')->nullable();
            $table->json('metadata')->nullable();

            $table->unique(['exam_attempt_id', 'section_id'], 'detail_nilai_attempt_section_unique');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_nilai');
    }
};
