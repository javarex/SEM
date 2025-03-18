<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Livewire\Livewire;
use App\Models\Student;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use Filament\Resources\Resource;
use Illuminate\Support\HtmlString;
use Filament\Tables\Columns\ColumnGroup;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\StudentResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\StudentResource\RelationManagers;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Illuminate\Support\Facades\DB;

class StudentResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('first_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('middle_name')
                    ->maxLength(255),
                Forms\Components\TextInput::make('last_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('municipality')
                    ->maxLength(255),
                Forms\Components\TextInput::make('type')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $query->with(['score' =>  function($query) {
                    $query->where('user_id', auth()->id());
                }]);
            })
            ->columns([
                Tables\Columns\TextColumn::make('fullname')
                    ->searchable(['first_name',  'last_name']),
                Tables\Columns\TextColumn::make('municipality')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->searchable(),
                // Tables\Columns\TextColumn::make('score.remarks')
                //     ->label('Remarks')
                //     ->searchable(),
//                Tables\Columns\TextColumn::make('total')
//                    ->default(fn($record) => $record->scores?->firstWhere('user_id', auth()->id())?->totalScore)
//                ,
                ColumnGroup::make('Scores',[
                    Tables\Columns\TextColumn::make('score.emotional')
                        ->label('Emotional Quotient'),
                    Tables\Columns\TextColumn::make('score.intelligence')
                        ->label('Intelligence Quotient'),
                    Tables\Columns\TextColumn::make('score.socio_economic')
                        ->label('Socio-Economic Form'),
                    Tables\Columns\TextColumn::make('score.totalScore')
                        ->label('Total Score'),
                ])
                ->alignCenter(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
//                Tables\Columns\TextColumn::make('total1')
//                    ->default(fn($record) => dd($record->scores)),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('score')
                    ->label('Score')
                    ->icon('heroicon-s-star')
                    ->form(fn (Student $student) => [
                        Forms\Components\TextInput::make('emotional')
                            ->label('Emotional Quotient')
                            ->hint(new HtmlString('<span class="text-lg font-bold dark:text-green-400 text-green-700">15%</span>'))
//                            ->mask('999')
                            ->numeric()
                            ->maxValue(15)
                            ->required(),
                        Forms\Components\TextInput::make('intelligence')
                            ->label('Intelligence Quotient')
                            ->hint(new HtmlString('<span class="text-lg font-bold dark:text-green-400 text-green-700">15%</span>'))
//                            ->mask('999')
                            ->numeric()
                            ->maxValue(15)
                            ->required(),
                        Forms\Components\TextInput::make('socio_economic')
                            ->label('Socio-Economic Form')
                            ->hint(new HtmlString('<span class="text-lg font-bold dark:text-green-400 text-green-700">20%</span>'))
                            ->numeric()
                            ->maxValue(20)
                            ->required(),
                        Forms\Components\Textarea::make('remarks')
                            ->label('Remarks')
                    ])
                    ->action(function($record, $data, $livewire) {
                        DB::beginTransaction();

                        try {
                            $data['student_id'] = $record->id;
                            // dd($record);
                        // ? $record->score->update($data) : auth()->user()->studentScores()->create($data);;
                            if (!$record->score) {
                                auth()->user()->studentScores()->create($data);
                            } else {
                                $has_score = $record->whereHas('score', fn($query) => $query->where('user_id', auth()->id()))->exists();
                                $has_score ? $record->score->update($data) : auth()->user()->studentScores()->create($data);
                            }
                            DB::commit();
                            // Livewire::dispatch('refreshInterviewedStudent');
                        } catch (\Throwable $th) {
                            //throw $th;
                            DB::rollBack();
                            dd($th->getMessage());
                        }


                    })
                    ->fillForm(function($record) {
                        try {
                            return $record->scores?->firstWhere('user_id', auth()->id())->toArray();
                        } catch (\Throwable $th) {
                            return [];
                        }
                    })
                    ->closeModalByClickingAway(false)
                    ->closeModalByEscaping(false)
                    ->modalWidth('md')
                    ->modalHeading(fn($record) => $record?->fullname)
                    ->visible(fn ($record) => auth()->user()->can('score',  $record)),
                Tables\Actions\EditAction::make(),
            ])
            ;
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
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
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
            'score'
        ];
    }
}
