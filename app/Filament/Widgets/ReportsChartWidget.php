<?php
namespace App\Filament\Widgets;

use Filament\Widgets\LineChartWidget;
use App\Models\ExpressService;
use Carbon\Carbon;

class ReportsChartWidget extends LineChartWidget
{
protected static ?string $heading = 'Service Reports';

protected function getData(): array
{
$today = Carbon::today();
$startOfWeek = Carbon::now()->startOfWeek();
$startOfMonth = Carbon::now()->startOfMonth();

$dailyCount = ExpressService::whereDate('created_at', $today)->count();
$weeklyCount = ExpressService::whereBetween('created_at', [$startOfWeek, Carbon::now()])->count();
$monthlyCount = ExpressService::whereBetween('created_at', [$startOfMonth, Carbon::now()])->count();

return [
'datasets' => [
[
'label' => 'Services',
'data' => [$dailyCount, $weeklyCount, $monthlyCount],
'backgroundColor' => ['#36A2EB', '#4CAF50', '#FFCE56'],
'borderColor' => ['#36A2EB', '#4CAF50', '#FFCE56'],
],
],
'labels' => ['Daily', 'Weekly', 'Monthly'],
];
}
}
