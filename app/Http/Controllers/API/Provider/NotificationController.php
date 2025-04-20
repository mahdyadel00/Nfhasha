<?php

namespace App\Http\Controllers\API\Provider;

use App\Http\Controllers\Controller;
use App\Models\ProviderNotification;
use App\Http\Resources\API\ErrorResource;
use App\Http\Resources\API\SuccessResource;
use App\Models\Order;
use App\Http\Resources\NotificationResource;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = ProviderNotification::where('provider_id', auth()->id())->latest()->paginate(config('app.pagination'));


        if ($notifications->isEmpty()) {
            return new ErrorResource(__('messages.no_notifications_found'));
        }

        $order = Order::where('provider_id', auth()->id())->latest()->first();

        if (!$order) {
            return new ErrorResource(__('messages.no_orders_found'));
        }


        return new SuccessResource([
            'message' => __('messages.notifications_found_successfully'),
            'data'    => NotificationResource::collection($notifications),
        ]);
    }

    public function show($id)
    {
        $notification = ProviderNotification::find($id);

        if (!$notification) {
            return new ErrorResource(__('messages.notification_not_found'));
        }

        return new SuccessResource([
            'message' => __('messages.notification_found_successfully'),
            'data'    => NotificationResource::make($notification),
        ]);
    }

}
