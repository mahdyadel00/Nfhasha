<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\ErrorResource;
use App\Http\Resources\API\OrderOfferResource;
use App\Http\Resources\API\SuccessResource;
use App\Models\Order;
use App\Models\OrderOffer;
use App\Models\ProviderNotification;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Pusher\Pusher;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = ProviderNotification::where('user_id', auth()->id())->latest()->paginate(config('app.pagination'));

        $order = Order::where('user_id', auth()->id())->latest()->first();

        if (!$order) {
            return new ErrorResource('No notifications found');
        }

        $offers = OrderOffer::where('order_id', $order->id)->get();

        return new SuccessResource([
            'message'   => 'Notifications found successfully',
            'data'      => OrderOfferResource::collection($offers),
        ]);
    }

    public function show($order_id)
    {
        $offers = OrderOffer::where('order_id', $order_id)->get();

        if ($offers->isNotEmpty()) {
            return new SuccessResource([
                'message' => 'Offers found successfully',
                'data'    => OrderOfferResource::collection($offers),
            ]);
        }

        return new ErrorResource('No offers found for this order');
    }

    public function showOffer($offer_id)
    {
        $offer = OrderOffer::find($offer_id);

        if ($offer) {
            return new SuccessResource([
                'message'   => 'Notification found successfully',
                'data'      => new OrderOfferResource($offer),
            ]);
        }

        return new ErrorResource('No notification found');
    }


    public function rejectOffer(Request $request, $id)
    {
        $offer  = OrderOffer::find($id);

        if ($offer) {
            $offer->update([
                'status' => 'rejected',
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
                'user_id'       => auth()->id(),
                'order_id'      => $offer->order_id,
                'provider_id'   => $offer->provider_id,
            ]);

            //create notification
            ProviderNotification::create([
                'user_id'       => auth()->id(),
                'provider_id'   => $offer->provider_id,
                'service_type'  => $offer->order->type,
                'message'       => 'Offer rejected',
            ]);

            $firebaseService = new FirebaseService();
            $firebaseService->sendNotificationToUser($offer->provider->fcm_token, 'Offer rejected', 'Offer rejected');

            return new SuccessResource('Offer rejected successfully');
        }
        return new ErrorResource('No notification found');
    }

    public function acceptOffer($id)
    {
        $offer = OrderOffer::find($id);

        if ($offer) {
            $order = Order::find($offer->order_id);
            $order->update([
                'status'        => 'accepted',
                'provider_id'   => $offer->provider_id,
                'total_cost'    => $offer->amount,
            ]);

            $offer->update([
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
                'user_id'       => $order->user_id,
                'order_id'      => $order->id,
                'provider_id'   => $offer->provider_id,
            ]);

            //create notification
            ProviderNotification::create([
                'user_id'       => $order->user_id,
                'order_id'      => $order->id,
                'provider_id'   => $offer->provider_id,
                'service_type'  => $order->type,
                'message'       => 'Offer accepted',
            ]);

            $firebaseService = new FirebaseService();
            $firebaseService->sendNotificationToUser($offer->provider->fcm_token, 'Offer accepted', 'Offer accepted');

            return new SuccessResource('Offer accepted successfully');
        }

        return new ErrorResource('No notification found');
    }
}
