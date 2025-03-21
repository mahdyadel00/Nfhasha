<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\User\ExpressService\StoreExpressServiceRequest;
use App\Http\Resources\API\ErrorResource;
use App\Http\Resources\API\Provider\PunctureServiceResource;
use App\Http\Resources\API\SuccessResource;
use App\Http\Resources\API\User\ExpressServiceResource;
use App\Models\ExpressService;
use App\Models\Order;
use App\Models\ProviderNotification;
use App\Models\PunctureService;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Pusher\Pusher;

class ExpressServiceController extends Controller
{
    public function index(Request $request)
    {
        $express_services = ExpressService::where('type', $request->type)->paginate(config("app.pagination"));

        return Count($express_services) > 0
            ? ExpressServiceResource::collection($express_services)
            : new ErrorResource('No express services found');
    }

    public function store(StoreExpressServiceRequest $request)
    {
        try {
            DB::beginTransaction();


            $express_services = ExpressService::find($request->express_service_id);


            $serviceType = $express_services->type;

            $users = User::whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->nearby($request->from_latitude, $request->from_longitude, 50)
                ->where('role', 'provider')
                ->whereHas('provider', function ($query) use ($serviceType) {
                    $query->where($serviceType, true)
                        ->where('is_active', true); // ✅ التحقق من أن البروفايدر نشط
                })
                ->get();

            $providerIds = $users->pluck('id')->toArray();

            $puncture_service = PunctureService::create([
                'express_service_id'    => $request->express_service_id,
                'user_id'               => auth()->id(),
                'user_vehicle_id'       => $request->user_vehicle_id ?? null,
                'pick_up_truck_id'      => $request->pick_up_truck_id ?? null,
                'address'               => $request->address,
                'distanition'           => $request->distanition ?? null,
                'from_latitude'         => $request->from_latitude,
                'from_longitude'        => $request->from_longitude,
                'to_latitude'           => $request->to_latitude ?? null,
                'to_longitude'          => $request->to_longitude ?? null,
                'type_battery'          => $request->type_battery ?? null,
                'battery_image' => $request->battery_image ? $request->battery_image->store('public/express_services') : null,
                'notes'                 => $request->notes ?? null,
                'amount'                => $request->amount ?? $express_services->price,
                'status'                => 'pending',
            ]);

            //create order
            $order = Order::create([
                'user_id'               => auth()->id(),
                'express_service_id'    => $request->express_service_id,
                'user_vehicle_id'       => $request->user_vehicle_id ?? null,
                'pick_up_truck_id'      => $request->pick_up_truck_id ?? null,
                'status'                => 'pending',
                'from_lat'              => $request->from_latitude,
                'from_long'             => $request->from_longitude,
                'to_lat'                => $request->to_latitude ?? null,
                'to_long'               => $request->to_longitude ?? null,
                'type'                  => $express_services->type,
                'payment_method'        => $request->payment_method ?? 'cash',
                'total_cost'            => $puncture_service->amount ?? $express_services->price,
                'address'               => $request->address,
                'address_to'            => $request->address_to,
                'note'                  => $request->notes ?? null,
            ]);

            //send notification to provider
            $pusher = new Pusher(
                env('PUSHER_APP_KEY'),
                env('PUSHER_APP_SECRET'),
                env('PUSHER_APP_ID'),
                ['cluster' => env('PUSHER_APP_CLUSTER'), 'useTLS' => true]
            );

            $service_type = $express_services->type;

            $message = match ($service_type) {
                'battery' => '🔋 Battery service request',
                'towing' => '🚛 Towing service request',
                'puncture' => '🛞 Puncture repair service request',
                default => '🚀 New express service request',
            };

            //create notification
            foreach ($providerIds as $providerId) {
                ProviderNotification::create([
                    'provider_id'   => $providerId,
                    'user_id'       => auth()->id(),
                    'order_id'      => $order->id,
                    'message'       => '🚀 New order request',
                    'service_type'  => $order->type,
                ]);
            }

            // إرسال الإشعار برسالة مخصصة
            foreach ($users as $user) {
                $pusher->trigger('notifications.providers.' . $user->id, 'sent.offer', [
                    'message'       => $message,
                    'order'         => $order,
                    'Provider_ids'  => $providerIds,
                ]);
            } //end foreach
            if ($users->isNotEmpty()) {
                try {


                    $tokens = $users->pluck('fcm_token')->filter()->unique()->toArray();

                    if (!empty($tokens)) {
                        $firebaseService = new FirebaseService();
                        $firebaseService->sendNotificationToMultipleUsers($tokens, $message, $message);
                    }
                } catch (\Exception $e) {
                    Log::channel('error')->error("Firebase Notification Failed: " . $e->getMessage());
                }
            }

            DB::commit();

            return new SuccessResource([
                'message'   => __('messages.express_service_created'),
                'data'      => $order->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('error')->error($e->getMessage());
            return new ErrorResource($e->getMessage());
        }
    }

    public function myExpressServices(Request $request)
    {
        $puncture_services = PunctureService::where('user_id', auth()->id())
            ->when($request->status, function ($query) use ($request) {
                return $query->whereIn('status', $request->status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(config("app.pagination"));

        return Count($puncture_services) > 0
            ? PunctureServiceResource::collection($puncture_services)
            : new ErrorResource('No express services found');
    }

    public function show($id)
    {
        $puncture_service = PunctureService::where('user_id', auth()->id())->find($id);

        return $puncture_service
            ? new PunctureServiceResource($puncture_service)
            : new ErrorResource('No express service found');
    }
}