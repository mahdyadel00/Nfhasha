<?php

namespace App\Http\Controllers\API\Provider;

use Pusher\Pusher;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\OrderTracking;
use App\Services\FirebaseService;
use App\Http\Controllers\Controller;
use App\Models\ProviderNotification;
use App\Http\Resources\API\OrderResource;
use App\Http\Resources\API\SuccessResource;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function myOrders()
    {
        $orders = Order::where('provider_id', auth('sanctum')->id())
            ->orderBy('created_at', 'desc')
            ->with('offers')
            ->paginate(config('app.pagination'));

        return new SuccessResource([
            'message' => __('messages.data_returned_successfully', ['attr' => __('messages.orders')]),
            'data' => OrderResource::collection($orders),
        ]);
    }

    public function show($id)
{
    try {
        $order = Order::query()
            ->where('provider_id', auth('sanctum')->id())
            ->where('id', $id)
            ->first();

        if (!$order) {
            return response()->json([
                'status' => 404,
                'message' => __('messages.order_not_found'),
                'data' => null
            ], 404);
        }

        return new SuccessResource([
            'message' => __('messages.data_returned_successfully', ['attr' => __('messages.order')]),
            'data' => new OrderResource($order),
        ]);

    } catch (\Exception $e) {
        Log::channel('error')->error(__('messages.error_in_show_order') . ': ' . $e->getMessage());
        return response()->json([
            'status' => 500,
            'message' => __('messages.something_went_wrong'),
            'data' => ['error' => $e->getMessage()],
        ], 500);
    }
}

    public function ordersByStatus(Request $request)
    {
        $orders = Order::where('provider_id', auth('sanctum')->id())
            ->orderBy('created_at', 'desc')
            ->when($request->status, function ($query) use ($request) {
                return $query->whereIn('status', $request->status);
            })
            ->when($request->type, function ($query) use ($request) {
                return $query->where('type', $request->type);
            })
            ->paginate(config('app.pagination'));

        return new SuccessResource([
            'message' => __('messages.data_returned_successfully', ['attr' => __('messages.orders')]),
            'data' => OrderResource::collection($orders),
        ]);
    }

    public function changeOrderStatus(Request $request, $id)
    {
        $order = Order::where('provider_id', auth('sanctum')->id())->find($id);

        if (!$order) {
            return new SuccessResource([
                'message' => __('messages.order_not_found'),
            ]);
        }

        $order->update([
            'status' => $request->status,
        ]);

        $users = User::whereHas('orders', function ($q) use ($order) {
            $q->where('id', $order->id);
        })->get();

        if ($users->isNotEmpty()) {
            try {
                $tokens = $users->pluck('fcm_token')->filter()->unique()->toArray();

                if (!empty($tokens)) {
                    $firebaseService = new FirebaseService();

                    $extraData = [
                        'order_id' => (string) $order->id, // تحويل إلى string
                        'type' => 'order',
                        'order_status' => $order->status, // إضافة حالة الطلب
                        'sound' => 'notify_sound', // تصحيح الصوت
                    ];

                    $message = 'تم تغيير حالة الطلب الخاص بك';

                    $firebaseService->sendNotificationToMultipleUsers($tokens, 'تغيير حالة الطلب', $message, $extraData);

                    \Log::info(__('messages.notification_sent_with_sound') . ': ' . $extraData);
                }
            } catch (\Exception $e) {
                Log::channel('error')->error(__('messages.firebase_notification_failed') . ': ' . $e->getMessage());
            }
        }

        return new SuccessResource([
            'message' => __('messages.order_status_changed'),
        ]);
    }

    public function orderTracking(Request $request, $id)
    {
        $order = Order::where('provider_id', auth('sanctum')->id())
            ->where('status', '!=', 'pending')
            ->where('status', '!=', 'canceled')
            ->where('status', '!=', 'completed')
            ->where('status', '!=', 'rejected')
            ->where('status', 'accepted')
            ->find($id);

        $user = auth('sanctum')->user();

        if (!$order) {
            return new SuccessResource([
                'message' => __('messages.order_not_found'),
            ]);
        }

        if ($order->status !== 'accepted') {
            return new SuccessResource([
                'message' => __('messages.invalid_order_status'),
            ]);
        }

        $user->update([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        $tracking_order = OrderTracking::where('order_id', $order->id)->first();

        if ($tracking_order) {
            $tracking_order->update([
                'status' => $request->status,
            ]);
        } else {
            OrderTracking::create([
                'order_id' => $order->id,
                'status' => $request->status,
            ]);
        }

        // تعريف المتغير لتجنب undefined error
        $image = [];

        if ($order->type == 'periodic_inspections') {
            if ($request->hasFile('inspection_reject_image')) {
                foreach ($request->file('inspection_reject_image') as $file) {
                    $image[] = uploadImage($file, 'periodic_inspections');
                }
            }

            $order->expressService->periodicInspections->update([
                'status' => $request->status,
                'inspection_reject_reason' => $request->reason,
                'inspection_reject_image' => json_encode($image),
            ]);
        }

        ProviderNotification::create([
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'provider_id' => auth()->id(),
            'service_type' => $order->type,
            'message' => __('messages.tracking_my_order'),
            'order_status' => $order->status, // إضافة حالة الطلب
        ]);

        $pusher = new Pusher(env('PUSHER_APP_KEY'), env('PUSHER_APP_SECRET'), env('PUSHER_APP_ID'), ['cluster' => env('PUSHER_APP_CLUSTER'), 'useTLS' => true]);

        $pusher->trigger('notifications.providers.' . $order->user_id, 'sent.location', [
            'message' => __('messages.tracking_my_order'),
            'order' => $order,
            'provider' => auth()->user(),
            'order_status' => $order->status, // إضافة حالة الطلب
            'order_tracking' => [
                'status' => $request->status,
                'inspection_reject_reason' => $request->reason,
                'inspection_reject_image' => $image,
            ],
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return new SuccessResource([
            'message' => __('messages.data_returned_successfully', ['attr' => __('messages.order_tracking')]),
        ]);
    }
}
