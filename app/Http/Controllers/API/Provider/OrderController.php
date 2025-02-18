<?php

namespace App\Http\Controllers\API\Provider;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\OrderResource;
use App\Http\Resources\API\SuccessResource;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function myOrders()
    {
        $orders = Order::where('provider_id', auth('sanctum')->id())->latest()->paginate(config('app.pagination'));

        return new SuccessResource([
            'message'   => __('messages.data_returned_successfully' , ['attr' => __('messages.orders')]) ,
            'orders'    => OrderResource::collection($orders)
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
            'order'     => new OrderResource($order)
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
            'orders'    => OrderResource::collection($orders)
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
}
