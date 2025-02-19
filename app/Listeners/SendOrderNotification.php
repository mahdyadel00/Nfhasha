<?php

namespace App\Listeners;

use App\Events\ServiceRequestEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendOrderNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ServiceRequestEvent $event): void
    {
        event(new ServiceRequestEvent(
            $event->order,
            $event->serviceType
        ));
    }
}
