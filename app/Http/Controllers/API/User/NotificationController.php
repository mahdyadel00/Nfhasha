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
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function index()
    {
        try {
            $notifications = ProviderNotification::where('user_id', auth()->id())
                ->with(['order', 'provider']) // تحميل العلاقات مسبقاً
                ->latest()
                ->paginate(config('app.pagination'));

            if ($notifications->isEmpty()) {
                return new SuccessResource([
                    'message' => __('messages.no_notifications'),
                    'data' => []
                ]);
            }

            $latestOrder = Order::where('user_id', auth()->id())
                ->with(['offers' => function($query) {
                    $query->with('provider')->latest();
                }])
                ->latest()
                ->first();

            return new SuccessResource([
                'message' => __('messages.notifications_found'),
                'data' => [
                    'notifications' => $notifications,
                    'latest_order' => $latestOrder ? [
                        'order' => $latestOrder,
                        'offers' => OrderOfferResource::collection($latestOrder->offers)
                    ] : null
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in notifications index: ' . $e->getMessage());
            return new ErrorResource(__('messages.something_went_wrong'));
        }
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
        try {
            $offer = OrderOffer::with(['order', 'provider'])->find($id);

            if (!$offer) {
                return new ErrorResource(__('messages.offer_not_found'));
            }

            if ($offer->status !== 'pending') {
                return new ErrorResource(__('messages.offer_already_processed'));
            }

            $offer->update(['status' => 'rejected']);

            // إرسال الإشعار عبر Pusher
            $this->sendPusherNotification(
                'notifications.providers',
                'sent.offer',
                [
                    'message' => __('messages.offer_rejected'),
                    'user_id' => auth()->id(),
                    'order_id' => $offer->order_id,
                    'provider_id' => $offer->provider_id,
                ]
            );

            // إنشاء إشعار للمزود
            ProviderNotification::create([
                'user_id' => auth()->id(),
                'provider_id' => $offer->provider_id,
                'order_id' => $offer->order_id,
                'service_type' => $offer->order->type,
                'message' => __('messages.offer_rejected'),
            ]);

            // إرسال إشعار Firebase
            if (!empty($offer->provider->fcm_token)) {
                $this->sendFirebaseNotification(
                    $offer->provider->fcm_token,
                    __('messages.offer_rejected'),
                    __('messages.offer_rejected_message', ['amount' => $offer->amount]),
                    [
                        'offer_id' => $offer->id,
                        'type' => __('messages.rejected_offer'),
                    ]
                );
            }

            return new SuccessResource([
                'message' => __('messages.offer_rejected'),
                'data' => new OrderOfferResource($offer)
            ]);
        } catch (\Exception $e) {
            Log::error('Error in rejectOffer: ' . $e->getMessage());
            return new ErrorResource(__('messages.something_went_wrong'));
        }
    }

    private function sendPusherNotification($channel, $event, $data)
    {
        try {
            $pusher = new Pusher(
                env('PUSHER_APP_KEY'),
                env('PUSHER_APP_SECRET'),
                env('PUSHER_APP_ID'),
                ['cluster' => env('PUSHER_APP_CLUSTER'), 'useTLS' => true]
            );
            $pusher->trigger($channel, $event, $data);
        } catch (\Exception $e) {
            Log::error('Pusher notification failed: ' . $e->getMessage());
        }
    }

    private function sendFirebaseNotification($token, $title, $body, $data = [])
    {
        try {
            $firebaseService = new FirebaseService();
            $firebaseService->sendNotificationToUser($token, $title, $body, $data);
        } catch (\Exception $e) {
            Log::error('Firebase notification failed: ' . $e->getMessage());
        }
    }

    public function acceptOffer($id)
    {
        try {
            $offer = OrderOffer::with(['order.expressService', 'provider'])->findOrFail($id);

            if (!$offer) {
                return response()->json([
                    'status' => 404,
                    'message' => __('messages.offer_not_found'),
                    'data' => null
                ], 404);
            }

            if ($offer->status !== 'pending') {
                return response()->json([
                    'status' => 400,
                    'message' => __('messages.offer_already_processed'),
                    'data' => null
                ], 400);
            }

            $order = $offer->order;

            if (!$order) {
                return response()->json([
                    'status' => 404,
                    'message' => __('messages.order_not_found'),
                    'data' => null
                ], 404);
            }

            DB::beginTransaction();

            try {
                $order->update([
                    'status' => 'accepted',
                    'provider_id' => $offer->provider_id,
                    'total_cost' => $offer->amount,
                ]);

                if ($order->type == 'periodic_inspections' && $order->status == 'pending') {
                    OrderProvider::create([
                        'provider_id' => $offer->provider_id,
                        'order_id' => $order->id,
                        'status' => 'assigned',
                    ]);
                }

                $offer->update(['status' => 'accepted']);

                // إرسال الإشعار عبر Pusher
                $this->sendPusherNotification(
                    'notifications.providers.' . $offer->provider_id,
                    'sent.offer',
                    [
                        'message' => __('messages.offer_accepted'),
                        'user_id' => $order->user_id,
                        'order_id' => $order->id,
                        'provider_id' => $offer->provider_id,
                    ]
                );

                // إنشاء إشعار للمزود
                ProviderNotification::create([
                    'user_id' => $order->user_id,
                    'order_id' => $order->id,
                    'provider_id' => $offer->provider_id,
                    'service_type' => $order->type,
                    'message' => __('messages.offer_accepted'),
                ]);

                // إرسال إشعار Firebase
                if (!empty($offer->provider->fcm_token)) {
                    $this->sendFirebaseNotification(
                        $offer->provider->fcm_token,
                        __('messages.offer_accepted'),
                        __('messages.offer_accepted_message', ['amount' => $offer->amount]),
                        [
                            'offer_id' => $offer->id,
                            'type' => __('messages.offer_accepted'),
                            'order_status' => $order->status,
                        ]
                    );
                }

                DB::commit();

                return response()->json([
                    'status' => 200,
                    'message' => __('messages.offer_accepted'),
                    'data' => [
                        'order' => $order->fresh(),
                        'offer' => new OrderOfferResource($offer->fresh())
                    ]
                ], 200);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Error in acceptOffer: ' . $e->getMessage());
            return response()->json([
                'status' => 500,
                'message' => __('messages.something_went_wrong'),
                'data' => ['error' => $e->getMessage()]
            ], 500);
        }
    }
}
