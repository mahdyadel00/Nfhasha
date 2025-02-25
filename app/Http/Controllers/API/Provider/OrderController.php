<?php

namespace App\Http\Controllers\API\Provider;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\OrderResource;
use App\Http\Resources\API\SuccessResource;
use App\Models\Order;
use App\Models\OrderTracking;
use App\Models\ProviderNotification;
use Illuminate\Http\Request;
use Pusher\Pusher;

class OrderController extends Controller
{
    public function myOrders()
    {
        $orders = Order::where('provider_id', auth('sanctum')->id())
            ->latest()
            ->with('offers')
            ->paginate(config('app.pagination'));

        return new SuccessResource([
            'message' => __('messages.data_returned_successfully', ['attr' => __('messages.orders')]),
            'data' => OrderResource::collection($orders)
        ]);
    }



    public function show($id)
    {
        $order = Order::where('provider_id' , auth('sanctum')->id())->find($id);

        if(!$order)
        {
            return new SuccessResource([
                'message'   => __('messages.order_not_found')
            ]);
        }

        return new SuccessResource([
            'message'   => __('messages.data_returned_successfully' , ['attr' => __('messages.order')]) ,
            'data'     => new OrderResource($order)
        ]);
    }


    public function ordersByStatus(Request $request)
    {
        $orders = Order::where('provider_id' , auth('sanctum')->id())
            //when status array
            ->when($request->status , function ($query) use ($request) {
                return $query->whereIn('status' , $request->status);
            })
            ->when($request->type , function ($query) use ($request) {
                return $query->where('type' , $request->type);
            })
            ->paginate(config('app.pagination'));

        return new SuccessResource([
            'message'   => __('messages.data_returned_successfully' , ['attr' => __('messages.orders')]) ,
            'data'    => OrderResource::collection($orders)
        ]);
    }

    public function changeOrderStatus(Request $request , $id)
    {
        $order = Order::where('provider_id' , auth('sanctum')->id())->find($id);

        if(!$order)
        {
            return new SuccessResource([
                'message'   => __('messages.order_not_found')
            ]);
        }

        $order->update([
            'status'    => $request->status
        ]);

        return new SuccessResource([
            'message'   => __('messages.order_status_changed')
        ]);
    }

    public function orderTracking(Request $request , $id)
    {
        $order = Order::where('provider_id' , auth('sanctum')->id())
            ->where('status' , '!=', 'pending')
            ->where('status' , '!=', 'canceled')
            ->where('status' , '!=', 'completed')
            ->where('status' , '!=', 'rejected')
            ->where('status' ,  'accepted')
            ->find($id);

        $user = auth('sanctum')->user();

        if(!$order)
        {
            return new SuccessResource([
                'message'   => __('messages.order_not_found')
            ]);
        }
        $user->update([
            'latitude'  => $request->latitude,
            'longitude' => $request->longitude
        ]);

        $tracking_order = OrderTracking::create([
            'order_id'   => $order->id,
            'status'     => $request->status,
        ]);

        if ($order->type == 'periodic_inspections') {
            //creat array for image
            $image = [];
            if ($request->hasFile('inspection_reject_image')) {
                foreach ($request->file('inspection_reject_image') as $file) {
                    $image[] = uploadImage($file, 'periodic_inspections');
                }
            }
            $order->expressService->periodicInspections->update([
                'status'                    => $request->status,
                'inspection_reject_reason'  => $request->reason,
                'inspection_reject_image'   => json_encode($image),
            ]);
        }

        //create notification
        ProviderNotification::create([
            'user_id'       => $order->user_id,
            'provider_id'   => auth()->id(),
            'service_type'  => $order->type,
            'message'       => __('messages.tracking_my_order')
        ]);


        $pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            ['cluster' => env('PUSHER_APP_CLUSTER'), 'useTLS' => true]
        );

        $pusher->trigger('notifications.providers.' . $order->user_id, 'sent.location', [
            'message'   => 'Provider location',
            'order'     => $order,
            'provider'  => auth()->user(),
            'latitude'  => $request->latitude,
            'longitude' => $request->longitude
        ]);


        if(!$order)
        {
            return new SuccessResource([
                'message'   => __('messages.order_not_found')
            ]);
        }

        return new SuccessResource([
            'message'   => __('messages.data_returned_successfully' , ['attr' => __('messages.order_tracking')]) ,
        ]);
    }
}
