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
        Schema::table('student_scores', function (Blueprint $table) {
            $table->double('emotional')->default(0)->nullable(false)->change();
            $table->double('intelligence')->default(0)->nullable(false)->change();
            $table->double('socio_economic')->default(0)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_scores', function (Blueprint $table) {
            $table->double('emotional')->nullable(false)->change();
            $table->double('intelligence')->nullable(false)->change();
            $table->double('socio_economic')->nullable(false)->change();
        });
    }
};
