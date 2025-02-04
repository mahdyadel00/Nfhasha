<?php

namespace App\Filament\Resources\SplashScreenResource\Pages;

use App\Filament\Resources\SplashScreenResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSplashScreen extends CreateRecord
{
    protected static string $resource = SplashScreenResource::class;

    public static function canCreateAnother(): bool
    {
        return false;
    }
    public function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
