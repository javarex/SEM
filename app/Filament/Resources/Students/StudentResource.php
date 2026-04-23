<?php

namespace App\Filament\Resources\Students;

use App\Filament\Exports\StudentExporter;
use App\Filament\Pages\ConsolidatedScore;
use App\Filament\Resources\Students\Pages\CreateStudent;
use App\Filament\Resources\Students\Pages\EditStudent;
use App\Filament\Resources\Students\Pages\ListStudents;
use App\Models\Student;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
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
                TextInput::make('first_name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('middle_name')
                    ->maxLength(255),
                TextInput::make('last_name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('municipality')
                    ->maxLength(255),
                TextInput::make('type')
                    ->maxLength(255),
                Textarea::make('pcro_remarks')
                    ->maxLength(255),
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
                    ->searchable(['first_name',  'last_name'])
                    ->description(fn ($record) => $record->municipality),
                TextColumn::make('score.created_at')
                    ->label('Date')
                    ->searchable()
                    ->dateTime('F j, Y')
                    ->badge(fn ($state) => $state?->toDateString() == now()->toDateString()),
                TextColumn::make('type')
                    ->searchable(),
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
                //                Tables\Columns\TextColumn::make('total1')
                //                    ->default(fn($record) => dd($record->scores)),
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
                    ->label('Export Results'),
            ])
            ->recordActions([
                Action::make('score')
                    ->label('Score')
                    ->icon('heroicon-s-star')
                    ->schema(fn (Student $student) => [
                        TextInput::make('emotional')
                            ->label('Emotional Quotient')
                            ->hint(new HtmlString('<span class="text-lg font-bold dark:text-green-400 text-green-700">15%</span>'))
//                            ->mask('999')
                            ->numeric()
                            ->maxValue(15)
                            ->required(),
                        TextInput::make('intelligence')
                            ->label('Intelligence Quotient')
                            ->hint(new HtmlString('<span class="text-lg font-bold dark:text-green-400 text-green-700">15%</span>'))
//                            ->mask('999')
                            ->numeric()
                            ->maxValue(15)
                            ->required(),
                        TextInput::make('socio_economic')
                            ->label('Socio-Economic Form')
                            ->hint(new HtmlString('<span class="text-lg font-bold dark:text-green-400 text-green-700">20%</span>'))
                            ->numeric()
                            ->maxValue(20)
                            ->required(),
                        Textarea::make('remarks')
                            ->label('Remarks'),
                    ])
                    ->modalSubmitAction(function (Action $action, $record) {
                        // dd();

                        // ->hidden($record->score?->created_at->format('Y-m-d') !== now()->format('Y-m-d') && $record->score?->created_at !== null)

                    })
                    ->action(function ($record, $data, $livewire) {
                        DB::beginTransaction();

                        try {
                            $data['student_id'] = $record->id;
                            // dd($record);
                            // ? $record->score->update($data) : auth()->user()->studentScores()->create($data);;
                            if (! $record->score) {
                                auth()->user()->studentScores()->create($data);
                            } else {
                                $has_score = $record->whereHas('score', fn ($query) => $query->where('user_id', auth()->id()))->exists();
                                $has_score ? $record->score->update($data) : auth()->user()->studentScores()->create($data);
                            }
                            DB::commit();
                            // Livewire::dispatch('refreshInterviewedStudent');
                        } catch (Throwable $th) {
                            // throw $th;
                            DB::rollBack();
                            dd($th->getMessage());
                        }

                    })
                    ->fillForm(function ($record) {
                        try {
                            return $record->scores?->firstWhere('user_id', auth()->id())->toArray();
                        } catch (Throwable $th) {
                            return [];
                        }
                    })
                    ->closeModalByClickingAway(false)
                    ->closeModalByEscaping(false)
                    ->modalWidth('md')
                    ->modalHeading(fn ($record) => $record?->fullname)
                    ->visible(fn ($record) => auth()->user()->can('score', $record)),
                EditAction::make(),
                DeleteAction::make(),
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
