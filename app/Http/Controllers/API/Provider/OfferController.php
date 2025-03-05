<?php

namespace App\Http\Controllers\API\Provider;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\ErrorResource;
use App\Http\Resources\API\OrderResource;
use App\Http\Resources\API\SuccessResource;
use App\Models\Order;
use App\Models\ProviderNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Pusher\Pusher;

class OfferController extends Controller
{
    public function offers()
    {
        try {
            DB::beginTransaction();

            $provider_notifications = ProviderNotification::where('provider_id', auth()->id())->get();

            // if ($provider_notifications->count() == 0) {
            //     return new ErrorResource([
            //         'message' => 'No offers found',
            //     ]);
            // }

            $serviceTypes = $provider_notifications->pluck('service_type')->toArray();
            $orders = collect();

            if (in_array('car_reservations', $serviceTypes) || in_array('maintenance', $serviceTypes) || in_array('comprehensive_inspections', $serviceTypes)
            || in_array('periodic_inspections', $serviceTypes)) {

            // احصل على إحداثيات مقدم الخدمة الحالي
            $provider   = auth()->user();
            $latitude   = $provider->latitude;
            $longitude  = $provider->longitude;

            $orders = Order::query()
            ->whereHas('user', function ($query) {
                $query->where('role', 'provider'); // تأكيد أن المستخدم هو مزود خدمة
            })
            ->whereIn('user_id', $provider_notifications->pluck('user_id')->toArray())
            ->whereNotIn('status', ['accepted', 'completed'])
            ->where(function ($query) {
                $query->where('status', 'pending')
                    ->orWhere(function ($query) {
                        $query->where('status', 'sent')
                            ->where('provider_id', auth()->id());
                    });
            })
            ->nearby($latitude, $longitude) // تصفية الطلبات حسب المسافة
            ->orderBy('created_at', 'desc')
            ->get();

        }

            //check type of service
            if (
                in_array('open_locks', $serviceTypes) || in_array('tow_truck', $serviceTypes) ||
                in_array('fuel', $serviceTypes) || in_array('puncture', $serviceTypes) ||
                in_array('battery', $serviceTypes)
            ) {
                // احصل على إحداثيات مقدم الخدمة الحالي
                $provider = auth()->user();
                $latitude = $provider->latitude;
                $longitude = $provider->longitude;

                $orders = Order::whereIn('user_id', $provider_notifications->pluck('user_id')->toArray())
                    ->with('offers')
                    ->where('status', '!=', 'accepted')
                    ->where('status', '!=', 'completed')
                    ->where(function ($query) {
                        $query->where('status', 'pending')
                            ->orWhere(function ($query) {
                                $query->where('status', 'sent')
                                    ->where('provider_id', auth()->id());
                            });
                    })
                    // تطبيق شرط القرب الجغرافي
                    ->nearby($latitude, $longitude, 50)
                    ->orderBy('created_at', 'desc')
                    ->get();
            }

            DB::commit();

            return new SuccessResource([
                'data' => OrderResource::collection($orders->map(function ($order) {
                    $isSentByMe = $order->provider_id == auth()->id();
                    $order->is_sent_by_me = $isSentByMe;
                    return $order;
                })),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e->getMessage(), $e->getLine(), $e->getFile());
            Log::channel('error')->error('Error in OfferController@offers: ' . $e->getMessage());
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
                'data' => OrderResource::make($order),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('error')->error('Error in OfferController@offer: ' . $e->getMessage());
            return new ErrorResource(['message' => $e->getMessage(),]);
        }
    }

    public function acceptOffer($id)
    {
        try {
            DB::beginTransaction();

            $order = Order::where('id', $id)->where('status', 'pending')->first();

            if (!$order) {
                return new ErrorResource([
                    'message' => 'Order not found',
                ]);
            }

            if ($order->status !== 'pending') {
                return new ErrorResource(['message' => 'Offer already accepted']);
            }

            $order->update([
                'status'        => 'accepted',
                'provider_id'   => auth()->id(),
            ]);

            DB::commit();

            //            event(new \App\Events\OfferCreated('Offer accepted', [auth()->id()], $order));

            $pusher = new Pusher(
                env('PUSHER_APP_KEY'),
                env('PUSHER_APP_SECRET'),
                env('PUSHER_APP_ID'),
                ['cluster' => env('PUSHER_APP_CLUSTER'), 'useTLS' => true]
            );

            $pusher->trigger('notifications.providers.' . $order->user_id, 'sent.offer', [
                'message'   => 'Offer accepted',
                'order'     => $order,
                'provider'  => auth()->user(),
            ]);
            return new SuccessResource(['message' => 'Offer accepted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('error')->error('Error in OfferController@acceptOffer: ' . $e->getMessage());
            return new ErrorResource(['message' => 'Something went wrong, please try again later']);
        }
    }

    public function sendOffer(Request $request, $id)
{
    try {
        DB::beginTransaction();
        $order = Order::where('id', $id)->first();

        if (!$order) {
            return new ErrorResource([
                'message' => 'Order not found',
            ]);
        }

        if ($order->status !== 'pending') {
            return new ErrorResource([
                'message' => 'You can only send offers for pending orders.',
            ]);
        }

        $order->update([
            'provider_id' => auth()->id(),
        ]);

        $order->offers()->create([
            'amount'       => $request->amount,
            'provider_id'  => auth()->id(),
            'status'       => 'sent',
        ]);

        // Create notification
        ProviderNotification::create([
            'user_id'       => $order->user_id,
            'provider_id'   => auth()->id(),
            'service_type'  => $order->type,
            'message'       => 'Offer sent',
        ]);

        // Send notification to user via Pusher
        $pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            ['cluster' => env('PUSHER_APP_CLUSTER'), 'useTLS' => true]
        );

        $pusher->trigger('notifications.providers.' . $order->user_id, 'sent.offer', [
            'message'   => 'Offer sent',
            'offer'     => $order->offers()->latest()->first(),
            'provider'  => auth()->user(),
        ]);

        DB::commit();

        return new SuccessResource([
            'message' => 'Offer sent successfully',
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::channel('error')->error('Error in OfferController@sendOffer: ' . $e->getMessage());
        return new ErrorResource(['message' => $e->getMessage()]);
    }
}


    public function rejectOffer(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $order = Order::where('id', $id)->where('status', 'pending')
                ->orWhere('status', 'sent')->first();
            if ($order) {
                $order->update([
                    'status'        => 'pending',
                    'provider_id'   => null,
                    'reason'        => $request->reason,
                ]);
            }
            DB::commit();

            $pusher = new Pusher(
                env('PUSHER_APP_KEY'),
                env('PUSHER_APP_SECRET'),
                env('PUSHER_APP_ID'),
                ['cluster' => env('PUSHER_APP_CLUSTER'), 'useTLS' => true]
            );

            $pusher->trigger('notifications.providers.' . $order->user_id, 'sent.offer', [
                'message'   => 'Offer rejected',
                'order'     => $order,
                'provider'  => auth()->user(),
            ]);

            return new SuccessResource([
                'message' => 'Offer rejected successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('error')->error('Error in OfferController@rejectOffer: ' . $e->getMessage());
            return new ErrorResource(['message' => $e->getMessage(),]);
        }
    }
}