<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('math_exams', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->json('types');
            $table->json('digits');
            $table->integer('total_questions');
            $table->integer('duration_minutes');
            $table->foreignId('school_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('math_exams');
    }
};
