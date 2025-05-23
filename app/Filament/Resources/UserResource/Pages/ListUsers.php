<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getTableQuery(): ?Builder
    {
        return parent::getTableQuery()->where('role', 'user');
    }

    public static function getNavigationLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Users' : 'المستخدمين';
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}