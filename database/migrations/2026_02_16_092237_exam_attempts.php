<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // UBAH: Nama tabel exam_session_user menjadi exam_attempts[cite: 14]
        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->unique(['exam_session_id', 'user_id']); // 1 user hanya 1 attempt per sesi

            $table->enum('status', ['not_started', 'ongoing', 'completed'])->default('not_started');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('finished_at')->nullable();

            // UBAH: Pemisahan raw_score dan final_score[cite: 14]
            $table->decimal('raw_score', 8, 2)->default(0);
            $table->decimal('final_score', 8, 2)->default(0);

            $table->boolean('is_locked')->default(false);
            $table->integer('violation_count')->default(0);
            $table->foreignId('school_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_attempts');
    }
};
