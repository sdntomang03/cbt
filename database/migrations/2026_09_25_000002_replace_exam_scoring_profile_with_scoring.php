<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->enum('scoring', ['average', 'total'])->default('average')->after('subject_id');
        });

        DB::table('exams')
            ->select(['id', 'scoring_profile_id'])
            ->orderBy('id')
            ->each(function (object $exam): void {
                $profile = $exam->scoring_profile_id
                    ? DB::table('scoring_profiles')->where('id', $exam->scoring_profile_id)->first()
                    : null;
                $rules = $profile?->rules ? json_decode($profile->rules, true) : [];
                $scoring = ($rules['result_mode'] ?? null) === 'total'
                    || strtolower((string) ($rules['type'] ?? '')) === 'weighted'
                    ? 'total'
                    : 'average';

                DB::table('exams')->where('id', $exam->id)->update(['scoring' => $scoring]);

                if ($exam->scoring_profile_id) {
                    DB::table('exam_sections')
                        ->where('exam_id', $exam->id)
                        ->whereNull('scoring_profile_id')
                        ->update(['scoring_profile_id' => $exam->scoring_profile_id]);
                }
            });

        Schema::table('exams', function (Blueprint $table) {
            $table->dropForeign(['scoring_profile_id']);
            $table->dropColumn('scoring_profile_id');
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->foreignId('scoring_profile_id')
                ->nullable()
                ->constrained('scoring_profiles')
                ->nullOnDelete();
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn('scoring');
        });
    }
};
