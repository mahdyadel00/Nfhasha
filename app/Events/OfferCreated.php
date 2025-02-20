<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class OfferCreated implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;

    public $message;
    public $providers;
    public $order;

    public function __construct($message, $providers, $order)
    {
        $this->message          = $message;
        $this->providers        = $providers;
        $this->order            = $order;

        //store notification in database
        foreach ($providers as $provider) {
            \App\Models\ProviderNotification::create([
                'provider_id'   => $provider,
                'message'       => $message,
                'user_id'       => $order->user_id,
                'service_type'  => $order->service_type,
            ]);
        }
    }

    public function broadcastOn()
    {
        return array_map(
            fn($id) => new PrivateChannel('notifications.providers.' . $id),
            $this->providers,
            $this->order->toArray()
        );
    }

    public function broadcastAs()
    {
        return 'sent.offer';
    }
}
