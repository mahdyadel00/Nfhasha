<?php

namespace App\Filament\Resources\CyPeriodicResource\Pages;

use App\Filament\Resources\CyPeriodicResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCyPeriodics extends ListRecords
{
    protected static string $resource = CyPeriodicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
