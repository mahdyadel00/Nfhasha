<?php

namespace App\Events;

use App\Models\Provider;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;


class ServiceRequestEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;
    public $serviceType;

    /**
     * Create a new event instance.
     */
    public function __construct($order, $serviceType)
    {
        $this->order = $order->toArray();
        $this->serviceType = $serviceType;
    }


    public function broadcastOn()
    {
        $providers = Provider::where('periodic_examination', true)->get();

        foreach ($providers as $provider) {
            return new Channel('service-requested.' . $provider->id);
        }
    }

    public function broadcastAs()
    {
        return 'periodic-examination';
    }

}
