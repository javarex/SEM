<?php

namespace App\Filament\Widgets;

use App\Models\StudentScore;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StudentInterviewedWidget extends BaseWidget
{
    use HasWidgetShield;

    protected function getStats(): array
    {
        $total = StudentScore::distinct('student_id')->groupBy(DB::raw('DATE_FORMAT("created_at", "%Y-%m-%d")'))->count();
        return [
            Stat::make('Total Interview', $total),
        ];
    }
}
