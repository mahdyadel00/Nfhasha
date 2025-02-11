<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class AccepteOffer implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;

    public $message;
    public $providers;
    public $express_service;
    public $amout;

    public function __construct($message, $providers, $express_service , $amount)
    {
        $this->message          = $message;
        $this->providers        = $providers;
        $this->express_service  = $express_service;
        $this->amout            = $amount;

        //store notification in database
        \App\Models\ProviderNotification::create([
            'provider_id' => $providers,
            'message'     => $message,
            'user_id'     => $express_service['user']['id'],
        ]);
    }

    public function broadcastOn()
    {
        return array_map(
            fn($id) => new PrivateChannel('notifications.providers.' . $id),
            is_array($this->providers) ? $this->providers : [],
            is_array($this->express_service->toArray()) ? $this->express_service->toArray() : []
        );

    }

    public function broadcastAs()
    {
        return 'provider.notification';
    }
}
