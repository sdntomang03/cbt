<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Buat tabel pivot exam_question terlebih dahulu
        Schema::create('exam_question', function (Blueprint $table) {
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->primary(['exam_id', 'question_id']);
        });

        // 2. MIGRASI DATA LAMA: Salin relasi dari tabel questions ke tabel pivot
        $existingQuestions = DB::table('questions')->whereNotNull('exam_id')->get();
        foreach ($existingQuestions as $question) {
            DB::table('exam_question')->insert([
                'exam_id' => $question->exam_id,
                'question_id' => $question->id,
            ]);
        }

        // 3. Hapus foreign key dan kolom exam_id dari tabel questions
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['exam_id']); // Menghapus hubungan lama
            $table->dropColumn('exam_id');    // Menghapus kolom lama
        });
    }

    public function down(): void
    {
        // Fitur Rollback: Jika batal, kembalikan kolom exam_id ke tabel questions
        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('exam_id')->nullable()->constrained('exams')->cascadeOnDelete();
        });

        // Kembalikan data dari pivot ke tabel questions sebelum tabel pivot dihapus
        $pivots = DB::table('exam_question')->get();
        foreach ($pivots as $pivot) {
            DB::table('questions')
                ->where('id', $pivot->question_id)
                ->update(['exam_id' => $pivot->exam_id]);
        }

        Schema::dropIfExists('exam_question');
    }
};
