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
        Schema::table('students', function (Blueprint $table) {
            // Add a temporary column to store the current fullname data
            $table->string('temp_fullname')->nullable();
        });

        // Copy data from the virtual column using CONCAT
        DB::statement("UPDATE students SET temp_fullname = CONCAT(first_name, ' ', last_name)");

        Schema::table('students', function (Blueprint $table) {
            // Remove the virtual column
            $table->dropColumn('fullname');

            // Add a regular fullname column
            $table->string('fullname')->nullable();
        });

        // Restore the data from the temp column to the new fullname column
        DB::statement("UPDATE students SET fullname = temp_fullname");

        // Remove the temporary column
        Schema::table('students', function (Blueprint $table) {
            $table->string('fullname')->nullable()->unique()->change();
            $table->dropColumn('temp_fullname');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('fullname');
            $table->string('fullname')->virtualAs("CONCAT(first_name, ' ', last_name)");
        });
    }
};
