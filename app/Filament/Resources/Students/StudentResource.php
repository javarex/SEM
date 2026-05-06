<?php

namespace App\Filament\Resources\Students;

use App\Filament\Exports\StudentExporter;
use App\Filament\Pages\ConsolidatedScore;
use App\Filament\Resources\Students\Pages\CreateStudent;
use App\Filament\Resources\Students\Pages\EditStudent;
use App\Filament\Resources\Students\Pages\ListStudents;
use App\Models\Student;
use App\Models\StudentScore;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View as SchemaView;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Throwable;

class StudentResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Student::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('fullname')
                    ->label('Full Name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('sex')
                    ->maxLength(255),
                TextInput::make('purok')
                    ->maxLength(255),
                TextInput::make('barangay')
                    ->maxLength(255),
                TextInput::make('municipality')
                    ->maxLength(255),
                TextInput::make('school')
                    ->maxLength(255),
                TextInput::make('family_background')
                    ->maxLength(255),
                TextInput::make('category')
                    ->maxLength(255),
                TextInput::make('ethnicity')
                    ->maxLength(255),
                TextInput::make('type')
                    ->maxLength(255),
                TextInput::make('ranking')
                    ->maxLength(255),
                TextInput::make('exam_score')
                    ->numeric(),
                Textarea::make('pcro_remarks')
                    ->maxLength(255),
                Textarea::make('cao_remarks'),
                Textarea::make('ydd_remarks'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $query
                    ->when(auth()->user()->isAdmin(), function ($query) {
                        $query->with('scores');
                    })
                    ->when(! auth()->user()->isAdmin(), function ($query) {
                        $query->with(['score' => function ($query) {
                            $query->where('user_id', auth()->id());
                        }]);
                    });
            })
            ->columns([
                TextColumn::make('fullname')
                    ->searchable()
                    ->description(fn ($record) => collect([$record->barangay, $record->municipality])->filter()->implode(', ')),
                TextColumn::make('score.created_at')
                    ->label('Date')
                    ->searchable()
                    ->dateTime('F j, Y')
                    ->badge(fn ($state) => $state?->toDateString() == now()->toDateString()),
                TextColumn::make('type')
                    ->searchable(),
                TextColumn::make('sex')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('barangay')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('category')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                // Tables\Columns\TextColumn::make('score.remarks')
                //     ->label('Remarks')
                //     ->searchable(),
                //                Tables\Columns\TextColumn::make('total')
                //                    ->default(fn($record) => $record->scores?->firstWhere('user_id', auth()->id())?->totalScore)
                //                ,
                ColumnGroup::make('Scores', [
                    TextColumn::make('score.emotional')
                        ->label('Emotional Quotient')
                        ->formatStateUsing(function ($state, $record) {
                            if (ConsolidatedScore::canAccess()) {
                                return view('filament.custom.student.scores', [
                                    'scores' => $record->scores,
                                    'column' => 'emotional',
                                ]);
                            } else {
                                return $state;
                            }
                        })
                        ->alignCenter(),
                    TextColumn::make('score.intelligence')
                        ->label('Intelligence Quotient')
                        ->formatStateUsing(function ($state, $record) {
                            if (ConsolidatedScore::canAccess()) {
                                return view('filament.custom.student.scores', [
                                    'scores' => $record->scores,
                                    'column' => 'intelligence',
                                ]);
                            } else {
                                return $state;
                            }
                        })
                        ->alignCenter(),
                    TextColumn::make('score.socio_economic')
                        ->label('Socio-Economic Form')
                        ->formatStateUsing(function ($state, $record) {
                            if (ConsolidatedScore::canAccess()) {
                                return view('filament.custom.student.scores', [
                                    'scores' => $record->scores,
                                    'column' => 'socio_economic',
                                ]);
                            } else {
                                return $state;
                            }
                        })
                        ->alignCenter(),
                    TextColumn::make('score.totalScore')
                        ->label('Total Score')
                        ->formatStateUsing(function ($state, $record) {
                            if (ConsolidatedScore::canAccess()) {
                                return view('filament.custom.student.scores', [
                                    'scores' => $record->scores,
                                    'column' => 'totalScore',
                                ]);
                            } else {
                                return $state;
                            }
                        })
                        ->alignCenter(),
                ])
                    ->alignCenter(),
                TextColumn::make('exam_score'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordUrl(null)
            ->filters([
                Filter::make('date')
                    ->schema([
                        DatePicker::make('date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date'],
                                fn (Builder $query, $date): Builder => $query
                                    ->when(auth()->user()->isAdmin(), function ($query) use ($date) {
                                        $query->whereHas('scores', fn ($query) => $query->whereDate('created_at', $date)
                                        );
                                    })
                                    ->when(! auth()->user()->isAdmin(), function ($query) use ($date) {
                                        $query->whereHas('score', fn ($query) => $query->whereDate('created_at', $date)
                                            ->where('user_id', auth()->id())
                                        );
                                    }),
                            );
                    }),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(StudentExporter::class)
                    ->color('success')
                    ->icon('heroicon-s-arrow-right-start-on-rectangle')
                    ->label('Export Results')
                    ->visible(fn (): bool => auth()->user()?->hasRole('super_admin') ?? false),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('View')
                    ->modalHeading(fn (Student $record): string => 'Student Details')
                    // ->modalHeading(fn (Student $record): string => $record->getRawOriginal('fullname') ?: trim("{$record->first_name} {$record->last_name}") ?: 'Student Details')
                    ->modalWidth('4xl')
                    ->schema([])
                    ->modalContent(fn (Student $record): View => view('filament.resources.students.actions.view-student', [
                        'record' => $record,
                    ])),
                Action::make('score')
                    ->label('Score')
                    ->icon('heroicon-s-star')
                    ->schema(fn (Student $student) => [
                        Grid::make([
                            'default' => 1,
                            'lg' => 2,
                        ])
                            ->schema([
                                SchemaView::make('filament.resources.students.actions.view-student')
                                    ->viewData([
                                        'record' => $student,
                                    ])
                                    ->columnSpan(1),
                                Section::make('Scoring Panel')
                                    ->description('Enter only the evaluation scores and remarks.')
                                    ->schema([
                                        TextInput::make('emotional')
                                            ->label('Emotional Quotient')
                                            ->hint(new HtmlString('<span class="text-lg font-bold dark:text-green-400 text-green-700">15%</span>'))
                                            //                            ->mask('999')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(15)
                                            ->required(),
                                        TextInput::make('intelligence')
                                            ->label('Intelligence Quotient')
                                            ->hint(new HtmlString('<span class="text-lg font-bold dark:text-green-400 text-green-700">15%</span>'))
                                            //                            ->mask('999')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(15)
                                            ->required(),
                                        TextInput::make('socio_economic')
                                            ->label('Socio-Economic Form')
                                            ->hint(new HtmlString('<span class="text-lg font-bold dark:text-green-400 text-green-700">20%</span>'))
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(20)
                                            ->required(),
                                        Textarea::make('remarks')
                                            ->label('Remarks'),
                                    ])
                                    ->columnSpan(1),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->action(function (Student $record, array $data): void {
                        $user = auth()->user();

                        throw_unless($user?->can('score', $record), ValidationException::withMessages([
                            'score' => 'This student score can no longer be edited.',
                        ]));

                        DB::beginTransaction();

                        try {
                            $scoreData = collect($data)
                                ->only(StudentScore::editableFields())
                                ->all();

                            $score = StudentScore::query()
                                ->where('student_id', $record->id)
                                ->where('user_id', auth()->id())
                                ->latest('id')
                                ->first();

                            if ($score === null) {
                                $user->studentScores()->create([
                                    ...$scoreData,
                                    'student_id' => $record->id,
                                ]);
                            } else {
                                throw_unless($score->isEditableBy($user), ValidationException::withMessages([
                                    'score' => 'This student score can no longer be edited.',
                                ]));

                                $score->update($scoreData);
                            }

                            DB::commit();
                            // Livewire::dispatch('refreshInterviewedStudent');
                        } catch (Throwable $th) {
                            DB::rollBack();

                            throw $th;
                        }

                    })
                    ->fillForm(function (Student $record): array {
                        $score = StudentScore::query()
                            ->where('student_id', $record->id)
                            ->where('user_id', auth()->id())
                            ->latest('id')
                            ->first();

                        return $score?->only(StudentScore::editableFields()) ?? [];
                    })
                    ->closeModalByClickingAway(false)
                    ->closeModalByEscaping(false)
                    ->modalWidth('6xl')
                    ->modalHeading(fn ($record) => $record?->fullname)
                    ->visible(fn (Student $record): bool => auth()->user()?->can('score', $record) ?? false),
                EditAction::make()
                    ->visible(fn (Student $record): bool => auth()->user()?->can('update', $record) ?? false),
                DeleteAction::make()
                    ->visible(fn (Student $record): bool => auth()->user()?->can('delete', $record) ?? false),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStudents::route('/'),
            'create' => CreateStudent::route('/create'),
            'edit' => EditStudent::route('/{record}/edit'),
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'restore',
            'restore_any',
            'replicate',
            'reorder',
            'delete',
            'delete_any',
            'force_delete',
            'force_delete_any',
            'score',
        ];
    }
}
