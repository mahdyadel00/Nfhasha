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
            return new ErrorResource(__('messages.no_notifications_found'));
        }

        $order = Order::where('user_id', auth()->id())
            ->latest()
            ->first();

        if (!$order) {
            return new ErrorResource(__('messages.no_orders_found'));
        }

        $offers = OrderOffer::where('order_id', $order->id)->get();

        return new SuccessResource([
            'message' => __('messages.notifications_found_successfully'),
            'data' => OrderOfferResource::collection($offers),
        ]);
    }

    public function show($order_id)
    {
        $offers = OrderOffer::where('order_id', $order_id)->get();

        if ($offers->isNotEmpty()) {
            return new SuccessResource([
                'message' => __('messages.offers_found_successfully'),
                'data' => OrderOfferResource::collection($offers),
            ]);
        }

        return new ErrorResource(__('messages.no_offers_found_for_this_order'));
    }

    public function showOffer($offer_id)
    {
        $offer = OrderOffer::find($offer_id);

        if ($offer) {
            return new SuccessResource([
                'message' => 'Notification found successfully',
                'data' => new OrderOfferResource($offer),
            ]);
        }

        return new ErrorResource(__('messages.no_notification_found'));
    }

    public function rejectOffer(Request $request, $id)
    {
        $offer = OrderOffer::find($id);

        if (!$offer) {
            return new ErrorResource(__('messages.offer_not_found'));
        }

        $offer->update(['status' => 'rejected']);

        try {
            $pusher = new Pusher(env('PUSHER_APP_KEY'), env('PUSHER_APP_SECRET'), env('PUSHER_APP_ID'), ['cluster' => env('PUSHER_APP_CLUSTER'), 'useTLS' => true]);

            $pusher->trigger('notifications.providers', 'sent.offer', [
                'message' => __('messages.offer_rejected'),
                'user_id' => auth()->id(),
                'order_id' => $offer->order_id,
                'provider_id' => $offer->provider_id,
                'order_status' => $offer->order->status, // إضافة حالة الطلب
            ]);

            ProviderNotification::create([
                'user_id' => auth()->id(),
                'provider_id' => $offer->provider_id,
                'order_id' => $offer->order_id,
                'service_type' => $offer->order->type,
                'message' => __('messages.offer_rejected'),
                'order_status' => $offer->order->status, // إضافة حالة الطلب
            ]);

            if (!empty($offer->provider->fcm_token)) {
                try {
                    $firebaseService = new FirebaseService();

                    $extraData = [
                        'offer_id' => (string) $offer->id, // تحويل إلى string
                        'order_id' => (string) $offer->order_id, // إضافة order_id
                        'type' => __('messages.rejected_offer'),
                        'order_status' => $offer->order->status, // إضافة حالة الطلب
                        'sound' => 'notify_sound', // تصحيح الصوت
                    ];

                    $firebaseService->sendNotificationToUser(
                        $offer->provider->fcm_token,
                        __('messages.offer_rejected'),
                        __('messages.offer_rejected_message', [
                            'amount' => $offer->amount,
                        ]),
                        $extraData,
                    );

                    \Log::info('Notification sent with sound: notify_sound', ['extraData' => $extraData]);
                } catch (\Exception $e) {
                    Log::channel('error')->error('Firebase Notification Failed: ' . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            Log::channel('error')->error('Notification Failed: ' . $e->getMessage());
        }

        return new SuccessResource(__('messages.offer_rejected'));
    }

    public function acceptOffer($id)
    {
        $offer = OrderOffer::find($id);

        if (!$offer) {
            return response()->json(['message' => __('messages.offer_not_found'),
                'property_message' => __('messages.offer_not_found_property')
            ], 404);
        }

        $order = Order::find($offer->order_id);
        $order->update([
            'status' => 'accepted',
            'provider_id' => $offer->provider_id,
            'total_cost' => $offer->amount,
        ]);

        if ($order->type == 'periodic_inspections' && $order->status == 'pending') {
            OrderProvider::create([
                'provider_id' => auth()->id(),
                'order_id' => $order->id,
                'status' => 'assigned',
            ]);
        }
        $offer->update(['status' => 'accepted']);

        $pusher = new Pusher(env('PUSHER_APP_KEY'), env('PUSHER_APP_SECRET'), env('PUSHER_APP_ID'), ['cluster' => env('PUSHER_APP_CLUSTER'), 'useTLS' => true]);
        $pusher->trigger('notifications.providers', 'sent.offer', [
            'message' => __('messages.offer_accepted'),
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'provider_id' => $offer->provider_id,
            'order_status' => $order->status, // إضافة حالة الطلب
        ]);

        ProviderNotification::create([
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'provider_id' => $offer->provider_id,
            'service_type' => $order->type,
            'message' => __('messages.offer_accepted'),
            'order_status' => $order->status, // إضافة حالة الطلب
        ]);

        if (!empty($offer->provider->fcm_token)) {
            try {
                $tokens = collect([$offer->provider->fcm_token])
                    ->filter()
                    ->unique()
                    ->toArray();

                if (!empty($tokens)) {
                    $firebaseService = new FirebaseService();

                    $extraData = [
                        'offer_id' => (string) $offer->id, // تحويل إلى string
                        'order_id' => (string) $order->id, // إضافة order_id
                        'type' => __('messages.offer_accepted'),
                        'order_status' => $order->status, // إضافة حالة الطلب
                        'sound' => 'notify_sound', // تصحيح الصوت
                    ];

                    $firebaseService->sendNotificationToMultipleUsers(
                        $tokens,
                        __('messages.offer_accepted'),
                        __('messages.offer_accepted_message', [
                            'amount' => $offer->amount,
                        ]),
                        $extraData,
                    );

                    \Log::info('Notification sent with sound: notify_sound', ['extraData' => $extraData]);
                }
            } catch (\Exception $e) {
                Log::channel('error')->error('Firebase Notification Failed: ' . $e->getMessage());
            }
        }

            return response()->json(['message' => __('messages.offer_accepted_successfully')]);
    }
}