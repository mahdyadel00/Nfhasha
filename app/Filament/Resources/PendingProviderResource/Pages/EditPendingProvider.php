<?php

namespace App\Filament\Resources\PendingProviderResource\Pages;

use App\Filament\Resources\PendingProviderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPendingProvider extends EditRecord
{
    protected static string $resource = PendingProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
