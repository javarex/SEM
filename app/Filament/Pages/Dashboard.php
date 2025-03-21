<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Dashboard extends \Filament\Pages\Dashboard
{
    // protected static ?string $navigationIcon = 'heroicon-o-document-text';

    // protected static string $view = 'filament.pages.dashboard';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasAnyRole(['super_admin', 'pswdo']);
    }
    
}
