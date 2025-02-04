<?php

namespace App\Filament\Resources\PickUpTruckResource\Pages;

use App\Filament\Resources\PickUpTruckResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPickUpTruck extends EditRecord
{
    protected static string $resource = PickUpTruckResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }


    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
