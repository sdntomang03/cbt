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
        Schema::create('exam_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->string('session_name'); // Misal: "Sesi 1 (Kelas X-A)"
            $table->string('token', 6)->nullable();
            $table->dateTime('start_time'); // Misal: 2024-03-01 08:00:00
            $table->dateTime('end_time');   // Misal: 2024-03-01 10:00:00
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_sessions');
    }
};
