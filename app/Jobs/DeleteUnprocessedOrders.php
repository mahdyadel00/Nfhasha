<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;
use Carbon\Carbon;

class DeleteUnprocessedOrders implements ShouldQueue
{
use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

public function handle()
{
$threshold = Carbon::now()->subMinutes(5);

Order::whereNull('provider_id')
->where('created_at', '<', $threshold) ->delete();
    }
    }
