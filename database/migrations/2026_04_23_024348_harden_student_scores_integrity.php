<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();

        DB::table('student_scores')
            ->whereNull('created_at')
            ->update([
                'created_at' => $now,
                'updated_at' => $now,
            ]);

        DB::table('student_scores')
            ->select('student_id', 'user_id')
            ->whereNull('deleted_at')
            ->groupBy('student_id', 'user_id')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->each(function (object $duplicateGroup) use ($now): void {
                $duplicateIds = DB::table('student_scores')
                    ->where('student_id', $duplicateGroup->student_id)
                    ->where('user_id', $duplicateGroup->user_id)
                    ->whereNull('deleted_at')
                    ->orderByDesc('id')
                    ->pluck('id')
                    ->skip(1)
                    ->values();

                if ($duplicateIds->isEmpty()) {
                    return;
                }

                DB::table('student_scores')
                    ->whereIn('id', $duplicateIds)
                    ->update([
                        'deleted_at' => $now,
                        'updated_at' => $now,
                    ]);
            });

        Schema::table('student_scores', function (Blueprint $table) {
            $table->unsignedTinyInteger('active_score_key')
                ->nullable()
                ->storedAs('IF(`deleted_at` IS NULL, 1, NULL)')
                ->after('deleted_at');

            $table->index(['created_at', 'student_id'], 'student_scores_created_student_index');
            $table->index(['student_id', 'user_id'], 'student_scores_student_user_index');
            $table->unique(['student_id', 'user_id', 'active_score_key'], 'student_scores_unique_active_student_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_scores', function (Blueprint $table) {
            $table->dropUnique('student_scores_unique_active_student_user');
            $table->dropIndex('student_scores_student_user_index');
            $table->dropIndex('student_scores_created_student_index');
            $table->dropColumn('active_score_key');
        });
    }
};
