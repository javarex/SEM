<?php

namespace App\Filament\Widgets;

use App\Models\StudentScore;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StudentInterviewedWidget extends BaseWidget
{
    use HasWidgetShield;

    protected function getStats(): array
    {
        $data = StudentScore::get()
                    ->each(function($item) {
                        $item->date = $item->created_at->format('Y-m-d');
                    });
        // dd($data->groupBy('student_id')->count());
        $per_day = $data->groupBy('date')->map(function($item, $key) {
            // dd($key, $item->groupBy('student_id'));
            return Stat::make(Carbon::parse($key)->format('F j, Y'), $item->groupBy('student_id')->count());
        })->toArray();

        $data_chart = $data->groupBy('date')->map(function($item, $key) {
            return $item->groupBy('student_id')->count();
        });

        // dd($data->countBy('date'));
        return collect([
                Stat::make('Overall Interviewed', $data->groupBy('student_id')->count())
                    ->color('success')
                    ->description('Daily Interviewed Trend')
                    ->chart($data_chart->toArray())
                ])
                ->merge($per_day)
                ->toArray();
    }
}
