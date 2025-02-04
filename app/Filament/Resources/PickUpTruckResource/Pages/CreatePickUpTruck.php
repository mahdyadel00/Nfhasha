<?php

namespace App\Filament\Resources\PickUpTruckResource\Pages;

use App\Filament\Resources\PickUpTruckResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePickUpTruck extends CreateRecord
{
    protected static string $resource = PickUpTruckResource::class;


    public static function canCreateAnother(): bool
    {
        return false;
    }
    public function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
