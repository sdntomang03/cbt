<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('abbreviation', 50)->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::table('exam_sections', function (Blueprint $table) {
            $table->foreignId('section_id')->nullable()->after('exam_id');
        });

        $names = DB::table('exam_sections')
            ->whereNotNull('name')
            ->distinct()
            ->pluck('name');
        $usedAbbreviations = [];

        foreach ($names as $name) {
            $base = strtoupper(collect(preg_split('/\s+/', trim($name)))
                ->filter()
                ->map(fn ($word) => Str::substr($word, 0, 1))
                ->implode(''));
            $base = $base !== '' ? Str::substr($base, 0, 50) : 'SECTION';
            $abbreviation = $base;
            $suffix = 2;

            while (in_array($abbreviation, $usedAbbreviations, true)
                || DB::table('sections')->where('abbreviation', $abbreviation)->exists()) {
                $abbreviation = Str::substr($base, 0, 47).'_'.$suffix++;
            }

            $usedAbbreviations[] = $abbreviation;
            $sectionId = DB::table('sections')->insertGetId([
                'abbreviation' => $abbreviation,
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('exam_sections')
                ->where('name', $name)
                ->update(['section_id' => $sectionId]);
        }

        Schema::table('exam_sections', function (Blueprint $table) {
            $table->foreign('section_id')->references('id')->on('sections')->nullOnDelete();
        });
        Schema::table('exam_sections', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }

    public function down(): void
    {
        Schema::table('exam_sections', function (Blueprint $table) {
            $table->string('name')->nullable()->after('section_id');
        });

        DB::table('exam_sections')
            ->join('sections', 'sections.id', '=', 'exam_sections.section_id')
            ->update(['exam_sections.name' => DB::raw('sections.name')]);

        Schema::table('exam_sections', function (Blueprint $table) {
            $table->dropForeign(['section_id']);
            $table->dropColumn('section_id');
        });
        Schema::dropIfExists('sections');
    }
};
