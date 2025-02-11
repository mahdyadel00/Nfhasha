<?php

namespace App\Http\Controllers\API\User;

use App\Events\AccepteOffer;
use App\Events\SentOffer;
use App\Http\Controllers\Controller;
use App\Http\Resources\API\ErrorResource;
use App\Http\Resources\API\SuccessResource;
use App\Http\Resources\API\User\ExpressServiceResource;
use App\Http\Resources\API\User\NotificationsResource;
use App\Http\Resources\API\User\ProviderNotificationResource;
use App\Models\Order;
use App\Models\ProviderNotification;
use App\Models\PunctureService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = ProviderNotification::where('user_id', auth()->id())->latest()->paginate(config('app.pagination'));

        $express_services = PunctureService::where('user_id', auth()->id())
            ->where('status', 'sent')
            ->latest()->paginate(config('app.pagination'));

        return count($express_services) > 0
            ? new ExpressServiceResource($express_services)
            : new ErrorResource('No notifications found');
    }


    public function show($id)
    {
        $notification = ProviderNotification::where('user_id', auth()->id())->find($id);

        $express_service = PunctureService::where('user_id', auth()->id())
            ->where('status', 'sent')
            ->find($id);


        return $express_service
            ? new ExpressServiceResource($express_service)
            : new ErrorResource('No notification found');
    }

    public function rejectOffer(Request $request, $id)
    {

        $notification = ProviderNotification::where('user_id', auth()->id())->first();

        $express_service = PunctureService::where('user_id', auth()->id())
            ->where('status', 'sent')
            ->find($id);

        if ($express_service) {
            $express_service->update([
                'status'    => 'rejected',
                'reason'    => $request->reason
            ]);

            //send notification to provider
            Broadcast(new SentOffer('Offer rejected', $notification->provider_id, $express_service , $express_service->amount));

            return new SuccessResource('Offer rejected successfully');
        }

        return new ErrorResource('No notification found');
    }

    public function acceptOffer($id)
    {
        $notification = ProviderNotification::where('user_id', auth()->id())->first();

        $express_service = PunctureService::where('user_id', auth()->id())
            ->where('status', 'sent')
            ->find($id);

        if ($express_service) {
            $express_service->update([
                'status'    => 'accepted',
            ]);

            //create order for user
            $order = Order::create([
                'user_id'                   => auth()->id(),
                'provider_id'               => $notification->provider_id,
                'express_service_id'        => $express_service->express_service_id,
                'type'                      => $express_service->expressService?->type,
                'status'                    => $express_service->status,
                'payment_method'            => 'Cash',
                'from_lat'                  => $express_service->from_latitude,
                'from_long'                 => $express_service->from_longitude,
                'to_lat'                    => $express_service->to_latitude,
                'to_long'                   => $express_service->to_longitude,
                'details'                   => $express_service->notes,
            ]);

            //send notification to provider
            Broadcast(new AccepteOffer('Offer accepted', $notification->provider_id, $express_service , $express_service->amount));

            return new SuccessResource('Offer accepted successfully');
        }

        return new ErrorResource('No notification found');
    }

}
