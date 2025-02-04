<?php

namespace App\Filament\Resources\HomeSliderResource\Pages;

use App\Filament\Resources\HomeSliderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateHomeSlider extends CreateRecord
{
    protected static string $resource = HomeSliderResource::class;
    
    public static function canCreateAnother(): bool
    {
        return false;
    }
    public function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
