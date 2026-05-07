<?php

namespace Database\Factories;

use App\Models\StudentProfileTabSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentProfileTabSetting>
 */
class StudentProfileTabSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tabKey = fake()->unique()->slug(2);

        return [
            'tab_key' => $tabKey,
            'tab_name' => str($tabKey)->replace('-', ' ')->title()->toString(),
            'is_visible' => true,
            'sort_order' => fake()->numberBetween(1, 100),
        ];
    }
}
