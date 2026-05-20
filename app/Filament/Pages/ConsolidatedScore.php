<?php

namespace App\Filament\Pages;

use App\Models\Student;
use App\Models\User;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;

class ConsolidatedScore extends Page
{
    use HasPageShield;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'filament.pages.consolidated-score';

    public $scores;

    public $judges;

    public ?string $team = null;

    //    public $scores;

    public function mount(): void
    {
        $this->fetchScore();
    }

    public function updatedTeam(): void
    {
        $this->fetchScore();
    }

    public function fetchScore(): void
    {
        // Get only users who scored at least one student.
        $this->judges = User::query()
            ->whereHas('studentScores')
            ->when($this->team, fn ($query, string $team) => $query->where('team', $team))
            ->orderBy('name')
            ->get(['id', 'name', 'team'])
            ->map(fn (User $judge): array => [
                'id' => $judge->id,
                'name' => $judge->name,
                'team' => $judge->team?->value,
            ])
            ->values()
            ->all();

        // Fetch students with their scores and compute averages
        $students = Student::query()
            ->join('student_scores', 'students.id', '=', 'student_scores.student_id')
            ->join('users as judges', 'student_scores.user_id', '=', 'judges.id')
            ->whereNull('student_scores.deleted_at')
            ->when($this->team, fn ($query, string $team) => $query->where('judges.team', $team))
            ->selectRaw('students.id as student_id, students.fullname as student_name, students.exam_score,
                judges.id as judge_id, judges.name as judge_name,
                AVG(student_scores.emotional) as avg_emotional,
                AVG(student_scores.intelligence) as avg_intelligence,
                AVG(student_scores.socio_economic) as avg_socio_economic')
            ->groupBy('students.id', 'students.fullname', 'students.exam_score', 'judges.id', 'judges.name')
            //            ->limit(20)
            ->get();

        $formattedStudents = [];

        foreach ($students as $score) {
            if (! isset($formattedStudents[$score->student_id])) {
                $formattedStudents[$score->student_id] = [
                    'name' => $score->student_name,
                    'examScore' => (float) ($score->exam_score ?? 0),
                    'grades' => [],
                    'totalScore' => 0,
                    'judgeCount' => 0,
                ];
            }

            if ($score->judge_id) {
                $formattedStudents[$score->student_id]['grades'][$score->judge_id] = [
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
            $student['examScoreWeighted'] = $student['examScore'] * 0.5;
            $student['panelScoreWeighted'] = $student['averageScore'] * 0.5;
            $student['finalAverage'] = $student['examScoreWeighted'] + $student['panelScoreWeighted'];
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
    }
}
