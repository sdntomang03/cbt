<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('math_exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('math_exam_id')->constrained('math_exams')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete(); // <--- Ide Anda
            $table->integer('num1');
            $table->integer('num2');
            $table->string('operator');
            $table->integer('correct_answer');
            $table->integer('student_answer')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('math_exam_questions');
    }
};
