<?php

namespace App\Http\Controllers\API\User;

use App\Events\AccepteOffer;
use App\Events\SentOffer;
use App\Http\Controllers\Controller;
use App\Http\Resources\API\ErrorResource;
use App\Http\Resources\API\OrderResource;
use App\Http\Resources\API\Provider\PunctureServiceResource;
use App\Http\Resources\API\SuccessResource;
use App\Http\Resources\API\User\ExpressServiceResource;
use App\Http\Resources\API\User\NotificationsResource;
use App\Http\Resources\API\User\ProviderNotificationResource;
use App\Models\Order;
use App\Models\ProviderNotification;
use App\Models\PunctureService;
use Illuminate\Http\Request;
use Pusher\Pusher;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = ProviderNotification::where('user_id', auth()->id())->latest()->paginate(config('app.pagination'));

        $order = Order::where('user_id', auth()->id())
            ->where('status', 'sent')
            ->latest()->paginate(config('app.pagination'));

            $transformedServices = $order->map(function ($service) {
                return new PunctureServiceResource($service);
        });

        return count($transformedServices) > 0
            ? $transformedServices
            : new ErrorResource('No notifications found');
    }


    public function show($id)
    {
       $order = Order::where('user_id', auth()->id())
            ->where('status', 'sent')
            ->find($id);

       return new SuccessResource([
                'message'   => 'Order found successfully',
                'data'      => new OrderResource($order),
        ]);
}

    public function rejectOffer(Request $request, $id)
    {

        $notification = ProviderNotification::where('user_id', auth()->id())->first();
            $order = Order::where('user_id', auth()->id())
            ->where('status', 'sent')
            ->where('user_id' , $notification->user_id)
            ->find($id);

        if ($order) {
            $order->update([
                'status'        => 'rejected',
                'reason'        => $request->reason,
            ]);

            //send notification to provider
            $pusher = new Pusher(
                env('PUSHER_APP_KEY'),
                env('PUSHER_APP_SECRET'),
                env('PUSHER_APP_ID'),
                ['cluster' => env('PUSHER_APP_CLUSTER'), 'useTLS' => true]
            );

            $pusher->trigger('notifications.providers', 'sent.offer', [
                'message'       => 'Offer rejected',
                'user_id'       => $notification->provider_id,
                'order_id'      => $order->id,
                'provider_id'   => $notification->provider_id,
            ]);

            //create notification
            ProviderNotification::create([
                'user_id'       => auth()->id(),
                'provider_id'   => $notification->provider_id,
                'service_type'  => $order->type,
                'message'       => 'Offer rejected',
            ]);



            return new SuccessResource('Offer rejected successfully');
        }

        return new ErrorResource('No notification found');
    }

    public function acceptOffer($id)
    {
        $notification = ProviderNotification::where('user_id', auth()->id())->first();

        $order = Order::where('user_id', auth()->id())
            ->where('status', 'sent')
            ->where('user_id' , $notification->user_id)
            ->find($id);

        if ($order) {
            $order->update([
                'status' => 'accepted',
            ]);

            //send notification to provider
            $pusher = new Pusher(
                env('PUSHER_APP_KEY'),
                env('PUSHER_APP_SECRET'),
                env('PUSHER_APP_ID'),
                ['cluster' => env('PUSHER_APP_CLUSTER'), 'useTLS' => true]
            );

            $pusher->trigger('notifications.providers', 'sent.offer', [
                'message'       => 'Offer accepted',
                'user_id'       => $notification->provider_id,
                'order_id'      => $order->id,
                'provider_id'   => $notification->provider_id,
            ]);

            //create notification
             ProviderNotification::create([
                'user_id'       => auth()->id(),
                'provider_id'   => $notification->provider_id,
                'service_type'  => $order->type,
                 'message'      => 'Offer accepted',
            ]);
            return new SuccessResource('Offer accepted successfully');
        }

        return new ErrorResource('No notification found');
    }

}
