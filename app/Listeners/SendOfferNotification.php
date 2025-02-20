<?php

namespace App\Listeners;

use App\Events\OfferCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendOfferNotification
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
    public function handle(OfferCreated $event): void
    {
        event(new OfferCreated(
            $event->message,
            $event->providers,
            $event->order,
        ));
    }
}
