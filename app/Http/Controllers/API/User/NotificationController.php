<?php

namespace App\Http\Controllers\API\User;

use Pusher\Pusher;
use App\Models\Order;
use App\Models\OrderOffer;
use Illuminate\Http\Request;
use App\Models\OrderProvider;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\ProviderNotification;
use App\Http\Resources\API\ErrorResource;
use App\Http\Resources\API\SuccessResource;
use App\Http\Resources\API\OrderOfferResource;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = ProviderNotification::where('user_id', auth()->id())
            ->latest()
            ->paginate(config('app.pagination'));

        if ($notifications->isEmpty()) {
            return new ErrorResource('No notifications found');
        }

        $order = Order::where('user_id', auth()->id())->latest()->first();

        if (!$order) {
            return new ErrorResource('No orders found');
        }

        $offers = OrderOffer::where('order_id', $order->id)
            ->get();

        return new SuccessResource([
            'message'   => 'Notifications found successfully',
            'data'      => OrderOfferResource::collection($offers),
        ]);
    }

    public function show($order_id)
    {
        $offers = OrderOffer::where('order_id', $order_id)
            ->get();

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
        $offer = OrderOffer::find($id);

        if (!$offer) {
            return new ErrorResource('Offer not found');
        }

        $offer->update(['status' => 'rejected']);

        try {
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

            ProviderNotification::create([
                'user_id'       => auth()->id(),
                'provider_id'   => $offer->provider_id,
                'service_type'  => $offer->order->type,
                'message'       => 'Offer rejected',
            ]);

            if (!empty($offer->provider->fcm_token)) {
                $firebaseService = new FirebaseService();
                $firebaseService->sendNotificationToUser($offer->provider->fcm_token, 'Offer rejected', 'Offer rejected');
            }
        } catch (\Exception $e) {
            Log::channel('error')->error("Notification Failed: " . $e->getMessage());
        }

        return new SuccessResource('Offer rejected successfully');
    }

    public function acceptOffer($id)
    {
        $offer = OrderOffer::find($id);

        if (!$offer) {
            return response()->json(['message' => 'Offer not found'], 404);
        }

        $order = Order::find($offer->order_id);
        $order->update([
            'status'        => 'accepted',
            'provider_id'   => $offer->provider_id,
            'total_cost'    => $offer->amount,
        ]);


        if ($order->type == 'periodic_inspections' && $order->status == 'pending') {
            OrderProvider::create([
                'provider_id'   => auth()->id(),
                'order_id'      => $order->id,
                'status'        => 'assigned',
            ]);
        }
        $offer->update(['status' => 'accepted']);

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

        ProviderNotification::create([
            'user_id'       => $order->user_id,
            'order_id'      => $order->id,
            'provider_id'   => $offer->provider_id,
            'service_type'  => $order->type,
            'message'       => 'Offer accepted',
        ]);

        if (!empty($offer->provider->fcm_token)) {
            try {
                $tokens = collect([$offer->provider->fcm_token])->filter()->unique()->toArray();
                if (!empty($tokens)) {
                    $firebaseService = new FirebaseService();
                    $firebaseService->sendNotificationToMultipleUsers($tokens, 'Offer accepted', 'Offer accepted');
                }
            } catch (\Exception $e) {
                Log::channel('error')->error("Firebase Notification Failed: " . $e->getMessage());
            }
        }

        return response()->json(['message' => 'Offer accepted successfully']);
    }
}
