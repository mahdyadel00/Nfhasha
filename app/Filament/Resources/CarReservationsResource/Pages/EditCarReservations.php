<?php

namespace App\Filament\Resources\CarReservationsResource\Pages;

use App\Filament\Resources\CarReservationsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCarReservations extends EditRecord
{
    protected static string $resource = CarReservationsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
