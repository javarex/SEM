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
            $table->string('first_name')->nullable()->change();
            $table->string('last_name')->nullable()->change();
            $table->string('sex')->nullable()->after('fullname');
            $table->string('purok')->nullable()->after('sex');
            $table->string('barangay')->nullable()->after('purok');
            $table->string('school')->nullable()->after('barangay');
            $table->string('family_background')->nullable()->after('school');
            $table->string('category')->nullable()->after('family_background');
            $table->string('ethnicity')->nullable()->after('category');
            $table->string('ranking')->nullable()->after('ethnicity');
            $table->text('cao_remarks')->nullable()->after('pcro_remarks');
            $table->text('ydd_remarks')->nullable()->after('cao_remarks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('students')->whereNull('first_name')->update(['first_name' => '']);
        DB::table('students')->whereNull('last_name')->update(['last_name' => '']);

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'sex',
                'purok',
                'barangay',
                'school',
                'family_background',
                'category',
                'ethnicity',
                'ranking',
                'cao_remarks',
                'ydd_remarks',
            ]);

            $table->string('first_name')->nullable(false)->change();
            $table->string('last_name')->nullable(false)->change();
        });
    }
};
