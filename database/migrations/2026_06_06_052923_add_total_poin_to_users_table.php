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
        Schema::table('users', function (Blueprint $table) {
            // total_poin untuk merekap poin user
            $table->integer('total_poin')->default(0)->after('sekolah');

            // Opsional tapi penting: penanda apakah user ini adalah user premium
            $table->timestamp('premium_until')->nullable()->after('is_premium');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['total_poin', 'premium_until']);
        });
    }
};
