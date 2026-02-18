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
        Schema::create('exam_session_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_session_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unique(['exam_session_id', 'user_id']);
            $table->enum('status', ['not_started', 'ongoing', 'completed'])->default('not_started');
            $table->dateTime('started_at')->nullable(); // Waktu siswa klik "Mulai"
            $table->dateTime('finished_at')->nullable(); // Waktu siswa selesai
            $table->decimal('score', 5, 2)->nullable();
            $table->boolean('is_locked')->default(false);
            // Atau simpan jumlah pelanggaran di server
            $table->integer('violation_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_session_user');
    }
};
