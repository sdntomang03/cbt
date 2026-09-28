<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('legacy_premium_until')->nullable()->after('premium_until');
            $table->timestamp('revenuecat_premium_until')->nullable()->after('premium_until');
            $table->boolean('revenuecat_premium_permanent')->default(false)->after('revenuecat_premium_until');
            $table->string('revenuecat_product_id')->nullable()->after('revenuecat_premium_permanent');
            $table->string('revenuecat_store')->nullable()->after('revenuecat_product_id');
            $table->string('revenuecat_environment')->nullable()->after('revenuecat_store');
        });

        DB::table('users')->whereNotNull('premium_until')->update([
            'legacy_premium_until' => DB::raw('premium_until'),
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'legacy_premium_until',
                'revenuecat_premium_until',
                'revenuecat_premium_permanent',
                'revenuecat_product_id',
                'revenuecat_store',
                'revenuecat_environment',
            ]);
        });
    }
};
