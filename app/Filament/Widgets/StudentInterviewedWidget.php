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
        $total = StudentScore::distinct('student_id')->get()
                    ->each(function($item) {
                        $item->date = $item->created_at->format('Y-m-d');
                    });
        // dd($total->groupBy('date'));
        return [
            Stat::make('Overall Interviewed', $total->count()),
            Stat::make(now()->format('F j, Y'), $total->where('date', now()->format('Y-m-d'))->count()),
        ];
    }
}
