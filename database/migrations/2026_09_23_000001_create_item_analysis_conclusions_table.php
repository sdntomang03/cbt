<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_analysis_conclusions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_session_id')->nullable()->constrained('exam_sessions')->cascadeOnDelete();
            $table->json('session_ids')->nullable();
            $table->longText('content');
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->index(['exam_id', 'exam_session_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_analysis_conclusions');
    }
};
