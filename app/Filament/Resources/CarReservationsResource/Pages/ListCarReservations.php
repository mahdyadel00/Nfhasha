<?php

namespace App\Filament\Resources\CarReservationsResource\Pages;

use App\Filament\Resources\CarReservationsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCarReservations extends ListRecords
{
    protected static string $resource = CarReservationsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
