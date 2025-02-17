<?php

namespace App\Listeners;

use App\Events\SentOffer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleSentOffer
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
        // Trigger the event
        public function handle(object $event): void
    {
        // Trigger the event
        event(new SentOffer(
            $event->message,
            $event->providers,
            $event->express_service,
            $event->amount
        ));
    }
}
