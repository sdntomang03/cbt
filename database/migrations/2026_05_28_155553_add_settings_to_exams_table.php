<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->boolean('require_token')->default(true)->after('show_explanation');
            $table->boolean('enable_violation')->default(true)->after('require_token');
            $table->integer('max_tolerances')->default(3)->after('enable_violation');
        });
    }

    public function down()
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn(['require_token', 'enable_violation', 'max_tolerances']);
        });
    }
};
