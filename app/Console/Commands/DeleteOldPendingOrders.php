<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use Carbon\Carbon;

class DeleteOldPendingOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:delete-old';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete orders that are pending for more than 24 hours and have no provider offers.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Searching for old pending orders...");

        $query = Order::where('status', 'pending') // طلبات حالتها معلقة
            ->where('created_at', '<=', Carbon::now()->subDay()) // عدى عليهم 24 ساعة
            ->doesntHave('offers'); // مفيش أي عرض من مقدم خدمة

        $count = $query->count();

        if ($count === 0) {
            $this->info("No old pending orders found.");
            return;
        }

        // نستخدم chunk لو العدد كبير
        $query->chunk(100, function ($orders) {
            foreach ($orders as $order) {
                $order->delete(); // بيشتغل سواء soft delete أو permanent
            }
        });

        $this->info("✅ Deleted {$count} old pending orders.");
    }
}
