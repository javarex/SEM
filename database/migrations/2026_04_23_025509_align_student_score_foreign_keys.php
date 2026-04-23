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
        if ($this->hasNonNumericScoreKeys()) {
            throw new RuntimeException('Cannot add student_scores foreign keys because non-numeric student_id or user_id values exist.');
        }

        if ($this->hasOrphanedScoreKeys()) {
            throw new RuntimeException('Cannot add student_scores foreign keys because orphaned student_id or user_id values exist.');
        }

        DB::statement('ALTER TABLE `student_scores` MODIFY `student_id` BIGINT UNSIGNED NOT NULL, MODIFY `user_id` BIGINT UNSIGNED NOT NULL');

        Schema::table('student_scores', function (Blueprint $table) {
            $table->foreign('student_id', 'student_scores_student_id_foreign')
                ->references('id')
                ->on('students')
                ->cascadeOnDelete();

            $table->foreign('user_id', 'student_scores_user_id_foreign')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_scores', function (Blueprint $table) {
            $table->dropForeign('student_scores_student_id_foreign');
            $table->dropForeign('student_scores_user_id_foreign');
        });

        DB::statement('ALTER TABLE `student_scores` MODIFY `student_id` VARCHAR(255) NOT NULL, MODIFY `user_id` VARCHAR(255) NOT NULL');
    }

    private function hasNonNumericScoreKeys(): bool
    {
        return DB::table('student_scores')
            ->whereRaw("`student_id` REGEXP '[^0-9]' OR `user_id` REGEXP '[^0-9]'")
            ->exists();
    }

    private function hasOrphanedScoreKeys(): bool
    {
        return DB::table('student_scores')
            ->leftJoin('students', 'students.id', '=', 'student_scores.student_id')
            ->leftJoin('users', 'users.id', '=', 'student_scores.user_id')
            ->whereNull('students.id')
            ->orWhereNull('users.id')
            ->exists();
    }
};
