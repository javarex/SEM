<?php

namespace Database\Seeders;

use App\Models\StudentProfileTabSetting;
use Illuminate\Database\Seeder;

class StudentProfileTabSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        StudentProfileTabSetting::seedMissingDefaults();
    }
}
