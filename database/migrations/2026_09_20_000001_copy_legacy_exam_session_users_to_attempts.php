<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Copy, never delete: installations created with the pre-attempt pivot can
     * safely move to exam_attempts. Fresh installations simply skip this step.
     */
    public function up(): void
    {
        if (! Schema::hasTable('exam_session_user') || ! Schema::hasTable('exam_attempts')) {
            return;
        }

        DB::table('exam_session_user')->orderBy('id')->each(function (object $legacy): void {
            DB::table('exam_attempts')->updateOrInsert(
                [
                    'exam_session_id' => $legacy->exam_session_id,
                    'user_id' => $legacy->user_id,
                ],
                [
                    'status' => $legacy->status ?? 'not_started',
                    'started_at' => $legacy->started_at,
                    'finished_at' => $legacy->finished_at,
                    'raw_score' => $legacy->score ?? 0,
                    'final_score' => $legacy->score ?? 0,
                    'is_locked' => $legacy->is_locked ?? false,
                    'violation_count' => $legacy->violation_count ?? 0,
                    'school_id' => $legacy->school_id ?? null,
                    'created_at' => $legacy->created_at ?? now(),
                    'updated_at' => now(),
                ]
            );
        });
    }

    /** Legacy records are intentionally retained for rollback safety. */
    public function down(): void
    {
        // No-op: this migration must not delete historical attempts.
    }
};
