<?php
namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\ExpressService;
use Carbon\Carbon;

class StatsOverviewWidget extends BaseWidget
{
    protected function getCards(): array
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();

        return [
            Card::make('Active Services', ExpressService::where('is_active', true)->count())
                ->description('Number of active services')
                ->color('success'),

            Card::make('Total Reservations', ExpressService::withCount('carReservations')->get()->sum('car_reservations_count'))
                ->description('Total car reservations')
                ->color('primary'),

            Card::make('Latest Service Price', ExpressService::latest('created_at')->value('price') ?? 'N/A')
                ->description('Price of the latest service')
                ->color('warning'),

            Card::make('Daily Reports', ExpressService::whereDate('created_at', $today)->count())
                ->description('Services created today')
                ->color('info'),

            Card::make('Weekly Reports', ExpressService::whereBetween('created_at', [$startOfWeek, Carbon::now()])->count())
                ->description('Services created this week')
                ->color('success'),

            Card::make('Monthly Reports', ExpressService::whereBetween('created_at', [$startOfMonth, Carbon::now()])->count())
                ->description('Services created this month')
                ->color('primary'),
        ];
    }
}
