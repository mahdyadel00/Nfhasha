<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class ProviderNotification implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;

    public $message;
    public $providers;
    public $express_service;

    public function __construct($message, $providers, $express_service)
    {
        $this->message          = $message;
        $this->providers        = $providers;
        $this->express_service  = $express_service;

        //store notification in database
        foreach ($providers as $provider) {
            \App\Models\ProviderNotification::create([
                'provider_id' => $provider,
                'message'     => $message,
                'user_id'     => $express_service['user']['id'],
            ]);
        }
    }

    public function broadcastOn()
    {
        return array_map(
            fn($id) => new PrivateChannel('notifications.providers.' . $id),
            $this->providers,
//            $this->express_service->user->name,
            $this->express_service->toArray()
        );
    }

    public function broadcastAs()
    {
        return 'provider.notification';
    }
}
