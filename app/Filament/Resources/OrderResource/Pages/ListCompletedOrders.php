<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCompletedOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getTableQuery(): ?Builder
    {
        return parent::getTableQuery()->whereIn('status', ['completed', 'canceled', 'rejected', 'refunded']);
    }

    public static function getNavigationLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Completed Orders' : 'الطلبات المنتهية';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}