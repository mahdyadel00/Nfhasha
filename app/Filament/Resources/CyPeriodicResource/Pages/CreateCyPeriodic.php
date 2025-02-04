<?php

namespace App\Filament\Resources\CyPeriodicResource\Pages;

use App\Filament\Resources\CyPeriodicResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCyPeriodic extends CreateRecord
{
    protected static string $resource = CyPeriodicResource::class;


    public static function canCreateAnother(): bool
    {
        return false;
    }
    public function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
