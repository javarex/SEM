<?php

namespace App\Filament\Resources\StudentProfileTabSettings\Schemas;

use App\Models\StudentProfileTabSetting;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class StudentProfileTabSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('tab_key')
                    ->label('Tab')
                    ->options(StudentProfileTabSetting::defaultTabOptions())
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->disabledOn('edit')
                    ->dehydrated()
                    ->live()
                    ->afterStateUpdated(function (Set $set, ?string $state): void {
                        if (! $state) {
                            return;
                        }

                        $default = StudentProfileTabSetting::DEFAULT_TABS[$state] ?? null;

                        if (! $default) {
                            return;
                        }

                        $set('tab_name', $default['tab_name']);
                        $set('sort_order', $default['sort_order']);
                    }),
                TextInput::make('tab_name')
                    ->label('Display Name')
                    ->required()
                    ->maxLength(255),
                Toggle::make('is_visible')
                    ->label('Visible')
                    ->default(true)
                    ->required(),
                TextInput::make('sort_order')
                    ->label('Order')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(65535)
                    ->required(),
            ]);
    }
}
