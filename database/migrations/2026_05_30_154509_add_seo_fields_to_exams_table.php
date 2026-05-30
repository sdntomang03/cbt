<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->text('description')->nullable()->after('title');
            $table->longText('content')->nullable()->after('description');
            $table->string('meta_description', 160)->nullable()->after('content');
            $table->string('meta_keywords', 255)->nullable()->after('meta_description');
            $table->string('thumbnail')->nullable()->after('meta_keywords');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            //
        });
    }
};
