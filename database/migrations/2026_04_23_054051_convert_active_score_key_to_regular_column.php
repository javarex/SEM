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
        if (! Schema::hasColumn('student_scores', 'active_score_key')) {
            return;
        }

        if (! $this->isGeneratedActiveScoreKey()) {
            $this->syncActiveScoreKeys();

            return;
        }

        Schema::table('student_scores', function (Blueprint $table) {
            $table->dropUnique('student_scores_unique_active_student_user');
            $table->dropColumn('active_score_key');
        });

        Schema::table('student_scores', function (Blueprint $table) {
            $table->unsignedTinyInteger('active_score_key')
                ->nullable()
                ->after('deleted_at');
        });

        $this->syncActiveScoreKeys();

        DB::statement('ALTER TABLE `student_scores` MODIFY `active_score_key` TINYINT UNSIGNED NULL DEFAULT 1');

        Schema::table('student_scores', function (Blueprint $table) {
            $table->unique(['student_id', 'user_id', 'active_score_key'], 'student_scores_unique_active_student_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }

    private function isGeneratedActiveScoreKey(): bool
    {
        $extra = DB::table('information_schema.COLUMNS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'student_scores')
            ->where('COLUMN_NAME', 'active_score_key')
            ->value('EXTRA');

        return str_contains((string) $extra, 'GENERATED');
    }

    private function syncActiveScoreKeys(): void
    {
        DB::table('student_scores')
            ->whereNull('deleted_at')
            ->update(['active_score_key' => 1]);

        DB::table('student_scores')
            ->whereNotNull('deleted_at')
            ->update(['active_score_key' => null]);
    }
};
