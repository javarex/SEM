<?php

namespace App\Filament\Widgets;

use App\Models\Student;
use App\Models\StudentScore;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StudentInterviewedWidget extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 1;

    protected ?string $heading = 'Summary';

    protected ?string $description = 'Total registered students and unique students interviewed.';

    protected int|array|null $columns = [
        'md' => 2,
        'xl' => 2,
    ];

    protected function getStats(): array
    {
        $overallInterviewed = StudentScore::query()
            ->whereNotNull('created_at')
            ->distinct('student_id')
            ->count('student_id');

        return [
            Stat::make('Total Students', Student::count())
                ->color('info'),
            Stat::make('Overall Interviewed', $overallInterviewed)
                ->color('success')
                ->extraAttributes([
                    'class' => 'bg-blue-500 text-white',
                ]),
        ];
    }
}
