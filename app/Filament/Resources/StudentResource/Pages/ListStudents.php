<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use App\Imports\StudentImport;
use App\Models\Student;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
//            Actions\CreateAction::make(),
            \EightyNine\ExcelImport\ExcelImportAction::make()
                ->slideOver()
                ->color("primary")
                ->use(StudentImport::class)
                ->visible(fn() => auth()->user()->hasRole('super_admin')),
            Actions\Action::make('generate_scores')
            ->label('Generate Student Scores')
            ->action('generateScores')
            ->visible(fn() => auth()->user()->hasRole('super_admin')),
        ];
    }

    public  function generateScores(): void
    {
        $judges = User::role('judge')->get();

//        dd($judges);
        foreach ($judges as $judge) {
            $students = Student::whereDoesntHave('scores', fn($query) => $query->where('user_id', $judge->id))->get()->pluck('id');
            $judge->scores()->attach($students);
        }
    }
}
