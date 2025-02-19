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
        $this->order        = $order;
        $this->serviceType  = $serviceType;
        $latitude           = $this->order->from_lat;
        $longitude          = $this->order->from_lng;

        if($serviceType == 'maintenance') {
            $providers = Provider::where('type', 'center')
                ->whereNotNull('pick_up_truck_id')
                ->whereHas('user', function ($query) use ($latitude, $longitude) {
                    $query->nearby($latitude, $longitude, 50);
                })
                ->get();
        } else {
            $providers = Provider::where('type', 'center')
                ->whereHas('user', function ($query) use ($latitude, $longitude) {
                    $query->nearby($latitude, $longitude, 50);
                })
                ->get();
        }
        foreach ($providers as $provider) {
            \App\Models\ProviderNotification::create([
                'provider_id' => $provider->user_id,
                'user_id'     => $order->user_id,
                'message'     => 'New periodic examination request',
            ]);
        }
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
