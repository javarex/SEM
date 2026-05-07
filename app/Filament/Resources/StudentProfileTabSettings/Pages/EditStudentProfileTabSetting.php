<?php

namespace App\Filament\Resources\StudentProfileTabSettings\Pages;

use App\Filament\Resources\StudentProfileTabSettings\StudentProfileTabSettingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStudentProfileTabSetting extends EditRecord
{
    protected static string $resource = StudentProfileTabSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
