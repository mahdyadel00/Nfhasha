<?php

namespace App\Filament\Resources\FAQResource\Pages;

use App\Filament\Resources\FAQResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListFAQS extends ListRecords
{
    protected static string $resource = FAQResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'All' => Tab::make()
                ->label(app()->getLocale() === 'en' ? 'All' : 'الكل')
                ->modifyQueryUsing(fn (Builder $query) => $query),

            'Active' => Tab::make()
                ->label(app()->getLocale() === 'en' ? 'Active' : 'فعال')
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true)),

            'Inactive' => Tab::make()
                ->label(app()->getLocale() === 'en' ? 'Inactive' : 'معطل')
                ->icon('heroicon-o-hand-thumb-down')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false)),

            'For Providers' => Tab::make()
                ->label(app()->getLocale() === 'en' ? 'For Providers' : 'لموفري الخدمة')
                ->icon('heroicon-o-user-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('to_providers', true)),

            'For Customers' => Tab::make()
                ->label(app()->getLocale() === 'en' ? 'For Customers' : 'للمستخدمين')
                ->icon('heroicon-o-user-group')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('to_providers', false)),
        ];
    }


}
