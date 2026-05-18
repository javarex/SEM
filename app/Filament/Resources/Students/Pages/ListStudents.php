<?php

namespace App\Filament\Resources\Students\Pages;

use App\Exports\StudentExport;
use App\Filament\Resources\Students\StudentResource;
use App\Imports\StudentExamImport;
use App\Models\Student;
use App\Models\StudentScore;
use App\Models\User;
use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ExcelImportAction::make()
                ->slideOver()
                ->color('primary')
                ->use(StudentExamImport::class)
                ->visible(fn (): bool => auth()->user()?->hasRole('super_admin') ?? false),
            Action::make('export')
                ->schema([
                    Select::make('municipality')
                        ->label('Municipality')
                        ->options(fn (): array => Student::query()
                            ->whereNotNull('municipality')
                            ->where('municipality', '!=', '')
                            ->distinct()
                            ->orderBy('municipality')
                            ->pluck('municipality', 'municipality')
                            ->prepend('All', 'all')
                            ->all())
                        ->searchable()
                        ->required(),
                ])
                ->action(fn (array $data): BinaryFileResponse => $this->export($data['municipality']))
                ->color('success')
                ->icon('heroicon-s-arrow-right-start-on-rectangle')
                ->label('Export Results')
                ->visible(fn (): bool => auth()->user()?->hasRole('super_admin') ?? false),
            Action::make('generate_scores')
                ->requiresConfirmation()
                ->label('Generate Student Scores')
                ->action('generateScores')
                ->visible(fn (): bool => auth()->user()?->hasRole('super_admin') ?? false),
        ];
    }

    public function generateScores(): void
    {
        $this->authorizeSuperAdmin();

        DB::transaction(function (): void {
            User::query()
                ->whereHas('roles', fn (Builder $query): Builder => $query->where('name', 'panelist'))
                ->select('id')
                ->chunkById(100, function ($panelists): void {
                    foreach ($panelists as $panelist) {
                        $studentIds = Student::query()
                            ->whereDoesntHave(
                                'scores',
                                fn (Builder $query): Builder => $query->where('user_id', $panelist->id)
                            )
                            ->pluck('id');

                        if ($studentIds->isEmpty()) {
                            continue;
                        }

                        $now = now();

                        $rows = $studentIds
                            ->map(fn (int $studentId): array => [
                                'student_id' => $studentId,
                                'user_id' => $panelist->id,
                                'emotional' => 0,
                                'intelligence' => 0,
                                'socio_economic' => 0,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ])
                            ->all();

                        StudentScore::query()->insertOrIgnore($rows);
                    }
                });
        });
    }

    public function export(string $municipality): BinaryFileResponse
    {
        $this->authorizeSuperAdmin();

        $filenameMunicipality = str($municipality)->slug()->toString();

        return Excel::download(new StudentExport($municipality), now().'-'.$filenameMunicipality.'.xlsx');
    }

    public function deletePanelistScore(int $scoreId): void
    {
        $score = StudentScore::query()
            ->with('student')
            ->findOrFail($scoreId);

        abort_unless(auth()->user()?->can('deleteScore', $score->student), 403);

        $score->delete();

        Notification::make()
            ->title('Panelist score deleted')
            ->success()
            ->send();
    }

    private function authorizeSuperAdmin(): void
    {
        abort_unless(auth()->user()?->hasRole('super_admin'), 403);
    }
}
