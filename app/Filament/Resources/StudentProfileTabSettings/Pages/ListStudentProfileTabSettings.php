<?php

namespace App\Filament\Resources\StudentProfileTabSettings\Pages;

use App\Filament\Resources\StudentProfileTabSettings\StudentProfileTabSettingResource;
use App\Models\StudentProfileTabSetting;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListStudentProfileTabSettings extends ListRecords
{
    protected static string $resource = StudentProfileTabSettingResource::class;

    public function mount(): void
    {
        parent::mount();

        StudentProfileTabSetting::seedMissingDefaults();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createDefaultTabs')
                ->label('Create Missing Defaults')
                ->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()
                ->action(function (): void {
                    StudentProfileTabSetting::seedMissingDefaults();

                    Notification::make()
                        ->title('Default tab settings are ready.')
                        ->success()
                        ->send();
                }),
            CreateAction::make(),
        ];
    }
}
