<?php

namespace App\Filament\Resources\ExpressServiceResource\Pages;

use App\Filament\Resources\ExpressServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExpressServices extends ListRecords
{
    protected static string $resource = ExpressServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(), // زر الإضافة
        ];
    }
}
