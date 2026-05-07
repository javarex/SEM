<?php

namespace App\Filament\Resources\StudentProfileTabSettings;

use App\Filament\Resources\StudentProfileTabSettings\Pages\CreateStudentProfileTabSetting;
use App\Filament\Resources\StudentProfileTabSettings\Pages\EditStudentProfileTabSetting;
use App\Filament\Resources\StudentProfileTabSettings\Pages\ListStudentProfileTabSettings;
use App\Filament\Resources\StudentProfileTabSettings\Schemas\StudentProfileTabSettingForm;
use App\Filament\Resources\StudentProfileTabSettings\Tables\StudentProfileTabSettingsTable;
use App\Models\StudentProfileTabSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class StudentProfileTabSettingResource extends Resource
{
    protected static ?string $model = StudentProfileTabSetting::class;

    protected static ?string $recordTitleAttribute = 'tab_name';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static ?string $navigationLabel = 'Student Profile Tabs';

    protected static ?string $modelLabel = 'Student Profile Tab';

    protected static ?string $pluralModelLabel = 'Student Profile Tabs';

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    public static function form(Schema $schema): Schema
    {
        return StudentProfileTabSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StudentProfileTabSettingsTable::configure($table);
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
            'index' => ListStudentProfileTabSettings::route('/'),
            'create' => CreateStudentProfileTabSetting::route('/create'),
            'edit' => EditStudentProfileTabSetting::route('/{record}/edit'),
        ];
    }
}
