<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class SettingsPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog';

    public static function getnavigationGroup(): string
    {
        return app()->getLocale() === 'en' ? 'APP Settings' : 'إعدادات التطبيق';
    }

    public static function getNavigationLabel(): string
    {
        return app()->getLocale() === 'en' ? 'General Settings' : 'إعدادات عامة';
    }
    protected static string $view = 'filament.pages.settings-page';
}
