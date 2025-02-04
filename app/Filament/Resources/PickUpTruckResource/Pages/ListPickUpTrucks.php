<?php

namespace App\Filament\Resources\PickUpTruckResource\Pages;

use App\Filament\Resources\PickUpTruckResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPickUpTrucks extends ListRecords
{
    protected static string $resource = PickUpTruckResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }


}
