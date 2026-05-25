<?php

use Illuminate\Database\Migrations\Migration;
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

        DB::table('student_scores')
            ->whereNotNull('deleted_at')
            ->where('active_score_key', 1)
            ->update(['active_score_key' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
