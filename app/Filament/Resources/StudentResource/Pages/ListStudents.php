<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Exports\StudentExport;
use App\Filament\Exports\StudentExporter;
use App\Filament\Resources\StudentResource;
use App\Imports\StudentExamImport;
use App\Imports\StudentImport;
use App\Models\Student;
use App\Models\User;
use Filament\Actions;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            \EightyNine\ExcelImport\ExcelImportAction::make()
                ->slideOver()
                ->color("primary")
                ->use(StudentExamImport::class)
//                ->use(StudentImport::class)
                ->visible(fn() => auth()->user()->hasRole('super_admin')),
            Actions\Action::make('export')
                ->action('export')
                ->color('success')
                ->icon('heroicon-s-arrow-right-start-on-rectangle')
                ->label('Export Results'),
            Actions\Action::make('generate_scores')
            ->requiresConfirmation()
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

    public function export()
    {
        // return (new StudentExport())->download('invoices.xlsx');;
        return Excel::download(new StudentExport, now().'.xlsx');
        // return (new StudentExport)->download(now().'.xlsx');
    }
}
