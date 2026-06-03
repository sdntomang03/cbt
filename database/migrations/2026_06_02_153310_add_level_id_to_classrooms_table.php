<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            // Tambahkan level_id setelah academic_year_id
            // Dibuat nullable() sementara agar tidak error jika sudah ada data lama
            $table->foreignId('level_id')
                ->nullable()
                ->after('academic_year_id')
                ->constrained('levels')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropForeign(['level_id']);
            $table->dropColumn('level_id');
        });
    }
};
