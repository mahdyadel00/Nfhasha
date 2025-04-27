<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCurrentOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getTableQuery(): ?Builder
    {
        return parent::getTableQuery()->whereIn('status', ['pending', 'accepted', 'sent', 'paid']);
    }

    public static function getNavigationLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Current Orders' : 'الطلبات الحالية';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}