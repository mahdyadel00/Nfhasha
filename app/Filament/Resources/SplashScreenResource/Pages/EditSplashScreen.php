<?php

namespace App\Filament\Resources\SplashScreenResource\Pages;

use App\Filament\Resources\SplashScreenResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSplashScreen extends EditRecord
{
    protected static string $resource = SplashScreenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
