<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\ErrorResource;
use App\Http\Resources\API\User\NotificationsResource;
use App\Http\Resources\API\User\ProviderNotificationResource;
use App\Models\ProviderNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = ProviderNotification::where('user_id', auth()->id())->latest()->paginate(config('app.pagination'));

        return count($notifications) > 0
            ? ProviderNotificationResource::collection($notifications)
            : new ErrorResource('No notifications found');
    }


    public function show($id)
    {
        $notification = ProviderNotification::where('user_id', auth()->id())->find($id);

        return $notification
            ? new ProviderNotificationResource($notification)
            : new ErrorResource('Notification not found');
    }
}
