<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Pages\Dashboard;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\HomeSlider;

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

class StatsOverview extends BaseWidget
{
    protected function getCards(): array
    {
        return [
            Card::make('عدد السلايدرات', HomeSlider::count())
                ->icon('heroicon-o-image')
                ->color('primary'),
            Card::make('عدد السلايدرات النشطة', HomeSlider::where('is_active', true)->count())
                ->icon('heroicon-o-check-circle')
                ->color('success'),
        ];
    }
}
