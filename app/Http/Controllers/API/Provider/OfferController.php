<?php

namespace App\Http\Controllers\API\Provider;

use Pusher\Pusher;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\OrderProvider;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\ProviderNotification;
use App\Http\Resources\API\ErrorResource;
use App\Http\Resources\API\OrderResource;
use App\Http\Resources\API\SuccessResource;
use App\Models\OrderOffer;

class OfferController extends Controller
{
    public function offers()
    {
        try {
            $provider = auth()->user();
            $providerId = $provider->id;

            $orderIds = ProviderNotification::where('provider_id', $providerId)->pluck('order_id')->toArray();

            $orders = Order::with('offers')
                ->whereIn('id', $orderIds)
                ->whereNotIn('status', ['accepted', 'completed'])
                ->where(function ($query) use ($providerId) {
                    $query->where('status', 'pending')->orWhere(function ($query) use ($providerId) {
                        $query->where('status', 'sent')->where('provider_id', $providerId);
                    });
                })
                ->whereDoesntHave('offers', function ($query) {
                    $query->where('provider_id', auth()->id())->where('status', 'rejected');
                })
                ->orderByDesc('created_at')
                ->get();

            return response()->json(
                [
                    'success' => true,
                    'data' => OrderResource::collection(
                        $orders->map(function ($order) use ($providerId) {
                            $order->is_sent_by_me = $order->provider_id == $providerId;
                            return $order;
                        }),
                    ),
                ],
                200,
            );
        } catch (\Exception $e) {
            Log::channel('error')->error(__('messages.error_in_offers') . ': ' . $e->getMessage());
            return new ErrorResource(['message' => $e->getMessage()]);
        }
    }

    public function offer($id)
    {
        try {
            DB::beginTransaction();

            $order = Order::where('id', $id)->first();

            DB::commit();

            return new SuccessResource([
                'data' => OrderResource::make($order->load('offers')),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('error')->error(__('messages.error_in_offer') . ': ' . $e->getMessage());
            return new ErrorResource(['message' => $e->getMessage()]);
        }
    }

    public function acceptOffer($id)
    {
        try {
            DB::beginTransaction();

            $order = Order::where('id', $id)->where('status', 'pending')->first();

            if (!$order) {
                return new ErrorResource([
                    'message' => __('messages.order_not_found'),
                ]);
            }

            if ($order->status !== 'pending') {
                return new ErrorResource(['message' => __('messages.offer_already_accepted')]);
            }

            if ($order->type == 'periodic_inspections' && $order->status == 'pending') {
                OrderProvider::create([
                    'provider_id'   => auth()->id(),
                    'order_id'      => $order->id,
                    'status'        => 'assigned',
                ]);
            }

            $order->update([
                'status' => 'accepted',
                'provider_id' => auth()->id(),
            ]);

            // Create notification
            ProviderNotification::create([
                'user_id'       => $order->user_id,
                'provider_id'   => auth()->id(),
                'order_id'      => $order->id,
                'service_type'  => $order->type,
                'message'       => __('messages.offer_accepted'),
                'order_status'  => $order->status,
            ]);

            $pusher = new Pusher(env('PUSHER_APP_KEY'), env('PUSHER_APP_SECRET'), env('PUSHER_APP_ID'), ['cluster' => env('PUSHER_APP_CLUSTER'), 'useTLS' => true]);

            $pusher->trigger('notifications.providers.' . $order->user_id, 'sent.offer', [
                'message'       => __('messages.offer_accepted'),
                'order'         => $order,
                'provider'      => auth()->user(),
                'order_status'  => $order->status,
            ]);

            if (!empty($order->user->fcm_token)) {
                $firebaseService = new FirebaseService();

                $extraData = [
                    'order_id'      => (string) $order->id,
                    'type'          => 'order',
                    'order_status'  => $order->status,
                    'sound'         => 'notify_sound',
                ];

                $firebaseService->sendNotificationToUser($order->user->fcm_token, __('messages.offer_accepted'), __('messages.your_offer_has_been_accepted'), $extraData);

                Log::info('Notification sent with sound: notify_sound', ['extraData' => $extraData]);
            }

            DB::commit();

            return new SuccessResource(['message' => __('messages.offer_accepted')]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('error')->error(__('messages.error_in_accept_offer') . ': ' . $e->getMessage());
            return new ErrorResource(['message' => $e->getMessage()]);
        }
    }

    public function sendOffer(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $order = Order::where('id', $id)->first();

            if (!$order) {
                return new ErrorResource([
                    'message' => __('messages.order_not_found'),
                ]);
            }

            if ($order->status !== 'pending') {
                return new ErrorResource([
                    'message' => __('messages.you_can_only_send_offers_for_pending_orders'),
                ]);
            }

            $order->update([
                'provider_id' => auth()->id(),
            ]);

            $order->offers()->create([
                'amount'        => $request->amount ?? $order->total_cost,
                'provider_id'   => auth()->id(),
                'status'        => 'sent',
            ]);

            // Create notification
            ProviderNotification::create([
                'user_id'           => $order->user_id,
                'provider_id'       => auth()->id(),
                'order_id'          => $order->id,
                'service_type'      => $order->type,
                'message'           => __('messages.offer_sent'),
                'order_status'      => $order->status,
            ]);

            // Send notification to user via Pusher
            $pusher = new Pusher(env('PUSHER_APP_KEY'), env('PUSHER_APP_SECRET'), env('PUSHER_APP_ID'), ['cluster' => env('PUSHER_APP_CLUSTER'), 'useTLS' => true]);

            $pusher->trigger('notifications.providers.' . $order->user_id, 'sent.offer', [
                'message'       => __('messages.offer_sent'),
                'offer'         => $order->offers()->latest()->first(),
                'provider'      => auth()->user(),
                'order_status'  => $order->status,
            ]);

            if (!empty($order->user->fcm_token)) {
                $firebaseService = new FirebaseService();

                $extraData = [
                    'order_id'      => (string) $order->id,
                    'type'          => 'order',
                    'order_status'  => $order->status,
                    'sound'         => 'notify_sound',
                ];

                $firebaseService->sendNotificationToUser($order->user->fcm_token, __('messages.offer_sent'), __('messages.you_have_received_offer'), $extraData);

                Log::info(__('messages.offer_sent_successfully'));
            }

            DB::commit();

            return new SuccessResource([
                'message' => __('messages.offer_sent'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('error')->error(__('messages.error_in_send_offer') . ': ' . $e->getMessage());
            return new ErrorResource(['message' => $e->getMessage()]);
        }
    }

    public function rejectOffer(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $order = Order::where('id', $id)
                ->where(function ($query) {
                    $query->where('status', 'pending')->orWhere('status', 'sent');
                })
                ->first();

            if (!$order) {
                return new ErrorResource(['message' => __('messages.order_not_found')]);
            }

            $order_offer        = OrderOffer::updateOrCreate([
                'order_id'      => $order->id,
                'provider_id'   => auth()->id(),
                'status'        => 'rejected',
                'amount'        => $order->total_cost,
            ]);

            if (!$order_offer) {
                return new ErrorResource(['message' => __('messages.offer_not_found')]);
            }

            if ($order->status == 'pending') {
                $order->update([
                    'status' => 'rejected',
                ]);
            }

            $order->update([
                'status'        => 'pending',
                'provider_id'   => null,
                'reason'        => $request->reason,
            ]);

            // Create notification
            ProviderNotification::create([
                'user_id'       => $order->user_id,
                'provider_id'   => auth()->id(),
                'order_id'      => $order->id,
                'service_type'  => $order->type,
                'message'       => __('messages.offer_rejected'),
                'order_status'  => $order->status,
            ]);

            $pusher = new Pusher(env('PUSHER_APP_KEY'), env('PUSHER_APP_SECRET'), env('PUSHER_APP_ID'), ['cluster' => env('PUSHER_APP_CLUSTER'), 'useTLS' => true]);

            $pusher->trigger('notifications.providers.' . $order->user_id, 'sent.offer', [
                'message'       => __('messages.offer_rejected'),
                'order'         => $order,
                'provider'      => auth()->user(),
                'order_status'  => $order->status,
            ]);

            if (!empty($order->user->fcm_token)) {
                $firebaseService = new FirebaseService();

                $extraData = [
                    'order_id'      => (string) $order->id,
                    'type'          => 'order',
                    'order_status'  => $order->status,
                    'sound'         => 'notify_sound',
                ];

                $firebaseService->sendNotificationToUser($order->user->fcm_token, __('messages.offer_rejected'), __('messages.your_offer_has_been_rejected'), $extraData);

                Log::info(__('messages.offer_rejected_successfully'));
            }

            DB::commit();

            return new SuccessResource([
                'message' => __('messages.offer_rejected_successfully'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('error')->error(__('messages.error_in_reject_offer') . ': ' . $e->getMessage());
            return new ErrorResource(['message' => $e->getMessage()]);
        }
    }
}
