<?php

namespace App\Filament\Pages;

use App\Models\Student;
use App\Models\StudentScore;
use App\Models\User;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Livewire\WithPagination;

class ConsolidatedScore extends Page
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.consolidated-score';

    public $scores;
    public $judges;

//    public $scores;

    public function mount(): void
    {
        $this->fetchScore();
    }
    public function  fetchScore(): void
    {
        // Get all judges
        $this->judges = User::role('judge')->get();

        // Fetch students with their scores and compute averages
        $students = Student::leftJoin('student_scores', 'students.id', '=', 'student_scores.student_id')
            ->leftJoinSub('select users.* from users inner join model_has_roles role on role.model_id = users.id where role.role_id = 3',
                'judges',
                'student_scores.user_id', '=', 'judges.id')
            ->selectRaw('students.id as student_id, students.first_name as student_name,
                judges.id as judge_id, judges.name as judge_name,
                AVG(student_scores.emotional) as avg_emotional,
                AVG(student_scores.intelligence) as avg_intelligence,
                AVG(student_scores.socio_economic) as avg_socio_economic')
            ->groupBy('students.id', 'students.first_name', 'judges.id', 'judges.name')
//            ->limit(20)
            ->get();

        $formattedStudents = [];

        foreach ($students as $score) {
            if (!isset($formattedStudents[$score->student_id])) {
                $formattedStudents[$score->student_id] = [
                    'name' => $score->student_name,
                    'grades' => [],
                    'totalScore' => 0,
                    'judgeCount' => 0,
                ];
            }

            if ($score->judge_id) {
                $formattedStudents[$score->student_id]['grades'][$score->judge_name] = [
                    'emotional' => number_format($score->avg_emotional, 2),
                    'intelligence' => number_format($score->avg_intelligence, 2),
                    'socio_economic' => number_format($score->avg_socio_economic, 2),
                ];

                $formattedStudents[$score->student_id]['totalScore'] += $score->avg_emotional + $score->avg_intelligence + $score->avg_socio_economic;
                $formattedStudents[$score->student_id]['judgeCount']++;
            }
        }

        foreach ($formattedStudents as &$student) {
            $student['averageScore'] = $student['judgeCount'] > 0 ? $student['totalScore'] / $student['judgeCount'] : 0;
        }

        // Sort by average score (highest first)
        usort($formattedStudents, function ($a, $b) {
            return $b['averageScore'] <=> $a['averageScore'];
        });

        // Assign ranks
        foreach ($formattedStudents as $index => &$student) {
            $student['rank'] = $index + 1;
        }

        $this->scores = array_values($formattedStudents);
//        dd($this->scores);
    }
}
