<?php

namespace App\Filament\Resources\CyPeriodicResource\Pages;

use App\Filament\Resources\CyPeriodicResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCyPeriodic extends EditRecord
{
    protected static string $resource = CyPeriodicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
