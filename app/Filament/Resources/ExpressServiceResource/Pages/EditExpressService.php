<?php

namespace App\Filament\Resources\ExpressServiceResource\Pages;

use App\Filament\Resources\ExpressServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExpressService extends EditRecord
{
    protected static string $resource = ExpressServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
