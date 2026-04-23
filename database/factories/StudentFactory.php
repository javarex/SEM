<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstNames = [
            'Althea',
            'Angelo',
            'Bianca',
            'Carlo',
            'Clarisse',
            'Danica',
            'Daniel',
            'Elaine',
            'Francis',
            'Gabriel',
            'Hannah',
            'Janelle',
            'Joshua',
            'Kathleen',
            'Kristian',
            'Lara',
            'Miguel',
            'Nicole',
            'Paolo',
            'Samantha',
            'Trisha',
            'Vincent',
        ];

        $middleNames = [
            'Abad',
            'Bautista',
            'Cruz',
            'Dela Cruz',
            'Garcia',
            'Lopez',
            'Mendoza',
            'Reyes',
            'Santos',
            'Torres',
            'Villanueva',
        ];

        $lastNames = [
            'Aquino',
            'Bautista',
            'Castillo',
            'Cruz',
            'Dela Cruz',
            'Domingo',
            'Fernandez',
            'Garcia',
            'Gonzales',
            'Lim',
            'Lopez',
            'Mendoza',
            'Navarro',
            'Ramos',
            'Reyes',
            'Rivera',
            'Santos',
            'Torres',
            'Villanueva',
        ];

        $firstName = fake()->randomElement($firstNames);
        $lastName = fake()->randomElement($lastNames);
        $fullname = "{$firstName} {$lastName}";

        while (in_array($fullname, self::$usedFullnames, true)) {
            $firstName = fake()->randomElement($firstNames);
            $lastName = fake()->randomElement($lastNames);
            $fullname = "{$firstName} {$lastName}";
        }

        self::$usedFullnames[] = $fullname;
        $createdAt = fake()->dateTimeBetween('-2 years', 'now');

        return [
            'first_name' => $firstName,
            'middle_name' => fake()->optional(0.75)->randomElement($middleNames),
            'last_name' => $lastName,
            'municipality' => fake()->optional(0.9)->randomElement([
                'Alabel',
                'Glan',
                'Kiamba',
                'Maasim',
                'Maitum',
                'Malapatan',
                'Malungon',
            ]),
            'type' => fake()->optional(0.85)->randomElement([
                'Academic',
                'Athletic',
                'Cultural',
                'Financial Assistance',
                'Leadership',
            ]),
            'created_at' => $createdAt,
            'updated_at' => fake()->dateTimeBetween($createdAt, 'now'),
            // 'exam_score' => fake()->optional(0.85)->randomFloat(2, 60, 100),
            // 'pcro_remarks' => fake()->optional(0.45)->randomElement([
            //     'For interview',
            //     'Complete requirements',
            //     'Pending document review',
            //     'Qualified for evaluation',
            //     'Requires follow-up',
            // ]),
            'fullname' => $fullname,
        ];
    }

    /**
     * @var array<int, string>
     */
    protected static array $usedFullnames = [];
}
