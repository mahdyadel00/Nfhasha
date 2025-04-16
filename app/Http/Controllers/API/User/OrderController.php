<?php

namespace App\Http\Controllers\API\User;

use App\Events\ServiceRequestEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\User\StoreperiodicExaminationRequest;
use App\Http\Resources\API\CyPeriodicResource;
use App\Http\Resources\API\ErrorResource;
use App\Http\Resources\API\OrderResource;
use App\Http\Resources\API\SuccessResource;
use App\Models\CarReservations;
use App\Models\ComprehensiveInspections;
use App\Models\CyPeriodic;
use App\Models\ExpressService;
use App\Models\Maintenance;
use App\Models\Order;
use App\Models\PeriodicInspections;
use App\Models\Provider;
use App\Models\ProviderNotification;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Pusher\Pusher;
use Carbon\Carbon;

class OrderController extends Controller
{
    public function cyPeriodics()
    {
        $cyPeriodics = CyPeriodic::paginate(config('app.pagination'));

        return count($cyPeriodics) > 0
            ? CyPeriodicResource::collection($cyPeriodics)
            : new ErrorResource(__('messages.no_cy_periodics_found'));
    }

    public function createOrder(StoreperiodicExaminationRequest $request)
    {
        try {
            DB::beginTransaction();

            $expressService = ExpressService::find($request->service_id);

            $serviceDate = Carbon::parse($request->date);
            $today = Carbon::today();
            if ($serviceDate->lt($today)) {
                return new ErrorResource(__('messages.date_cannot_be_before_today'));
            }


            $order = Order::create([
                'user_id'               => auth()->id(),
                'express_service_id'    => $request->service_id,
                'user_vehicle_id'       => $request->vehicle_id,
                'pick_up_truck_id'      => $request->pick_up_truck_id,
                'city_id'               => $request->city_id ?? null,
                'status'                => 'pending',
                'from_lat'              => $request->from_lat ?? $request->latitude,
                'from_long'             => $request->from_long ?? $request->longitude,
                'type'                  => $expressService->type,
                'payment_method'        => $request->payment_method ?? 'cash',
                'total_cost'            => $expressService->price,
                'address'               => $request->address,
                'address_to'            => $request->address_to,
                'note'                  => $request->note ?? null,
            ]);

            if ($expressService->type == 'car_reservations') {
                $inspection_side_array = is_array($request->inspection_side)
                    ? $request->inspection_side
                    : explode(',', $request->inspection_side);

                $inspection_side_string = implode(',', $inspection_side_array);

                CarReservations::create([
                    'order_id'              => $order->id,
                    'user_id'               => auth()->id(),
                    'express_service_id'    => $request->service_id,
                    'user_vehicle_id'       => $request->vehicle_id,
                    'city_id'               => $request->city_id,
                    'inspection_side'       => $inspection_side_string,
                    'date'                  => $request->date,
                    'time'                  => $request->time,
                    'status'                => 'pending',
                ]);
            }

            if ($expressService->type == 'maintenance') {
                $image = [];
                if ($request->hasFile('image')) {
                    foreach ($request->file('image') as $file) {
                        $image[] = uploadImage($file, 'maintences');
                    }
                }

                Maintenance::create([
                    'order_id'              => $order->id,
                    'user_id'               => auth()->id(),
                    'express_service_id'    => $request->service_id,
                    'user_vehicle_id'       => $request->vehicle_id,
                    'pick_up_truck_id'      => $request->pick_up_truck_id,
                    'maintenance_type'      => $request->maintenance_type,
                    'maintenance_description' => $request->maintenance_description,
                    'address'               => $request->address,
                    'latitude'              => $request->latitude,
                    'longitude'             => $request->longitude,
                    'is_working'            => filter_var($request->is_working, FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
                    'image'                 => json_encode($image),
                    'note'                  => $request->note,
                ]);
            }

            if ($expressService->type == 'comprehensive_inspections') {
                ComprehensiveInspections::create([
                    'order_id'              => $order->id,
                    'user_id'               => auth()->id(),
                    'express_service_id'    => $request->service_id,
                    'user_vehicle_id'       => $request->vehicle_id,
                    'pick_up_truck_id'      => $request->pick_up_truck_id,
                    'city_id'               => $request->city_id,
                    'date'                  => $request->date,
                    'address'               => $request->address,
                    'latitude'              => $request->latitude,
                    'longitude'             => $request->longitude,
                ]);
            }

            if ($expressService->type == 'periodic_inspections') {
                PeriodicInspections::create([
                    'order_id'              => $order->id,
                    'user_id'               => auth()->id(),
                    'express_service_id'    => $request->service_id,
                    'user_vehicle_id'       => $request->vehicle_id,
                    'pick_up_truck_id'      => $request->pick_up_truck_id,
                    'city_id'               => $request->city_id,
                    'inspection_type_id'    => $request->inspection_type_id,
                    'address'               => $request->address,
                    'latitude'              => $request->latitude,
                    'longitude'             => $request->longitude,
                    'status'                => 'pending',
                ]);
            }

            $serviceType = $order->type;

            $users = User::whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->nearby($request->latitude, $request->longitude, 50)
                ->where('role', 'provider')
                ->whereHas('provider', function ($query) use ($serviceType) {
                    $query->where($serviceType, true)
                        ->where('is_active', true);
                })
                ->get();
            $providerIds = $users->pluck('id')->toArray();

            //create notification
            foreach ($providerIds as $providerId) {
                ProviderNotification::create([
                    'provider_id'   => $providerId,
                    'user_id'       => auth()->id(),
                    'order_id'      => $order->id,
                    'message'       => __('messages.new_order'),
                    'service_type'  => $order->type,
                    'order_status'  => $order->status, // إضافة حالة الطلب
                ]);
            }

            $pusher = new Pusher(
                env('PUSHER_APP_KEY'),
                env('PUSHER_APP_SECRET'),
                env('PUSHER_APP_ID'),
                ['cluster' => env('PUSHER_APP_CLUSTER'), 'useTLS' => true]
            );

            $message = match ($order->type) {
                'battery'  => __('messages.battery_order_request'),
                'towing'   => __('messages.towing_order_request'),
                'puncture' => __('messages.puncture_order_request'),
                default    => __('messages.new_order_request'),
            };

            foreach ($users as $user) {
                $pusher->trigger('notifications.providers.' . $user->id, 'sent.offer', [
                    'message'       => $message,
                    'order'         => $order,
                    'order_status'  => $order->status, // إضافة حالة الطلب
                    'Provider_ids'  => $providerIds,
                ]);
            }

            if ($users->isNotEmpty()) {
                try {
                    $tokens = $users->pluck('fcm_token')->filter()->unique()->toArray();

                    if (!empty($tokens)) {
                        $firebaseService = new FirebaseService();

                        $extraData = [
                            'order_id'     => (string) $order->id, // تحويل إلى string للتوافق مع Flutter
                            'type'         => __('messages.new_order'),
                            'order_status' => $order->status, // إضافة حالة الطلب
                            'sound'        => 'notify_sound', // تصحيح الصوت للتوافق مع Flutter
                        ];

                        $firebaseService->sendNotificationToMultipleUsers(
                            $tokens,
                            __('messages.new_order'),
                            $message,
                            $extraData
                        );

                        // تسجيل للتحقق
                        \Log::info(__('messages.notification_sent_with_sound') . ': ' . $extraData);
                    }
                } catch (\Exception $e) {
                    Log::channel('error')->error(__('messages.firebase_notification_failed') . ': ' . $e->getMessage());
                }
            }

            DB::commit();

            return new SuccessResource([
                'message' => __('messages.order_created_successfully'),
                'data'    => $order->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('error')->error(__('messages.error_in_periodic_examination') . ': ' . $e->getMessage());
            return new ErrorResource($e->getMessage());
        }
    }

    public function updatePeriodicInspection(Request $request, $orderId)
    {
        try {
            DB::beginTransaction();

            $order = Order::where('id', $orderId)
                ->where('type', 'periodic_inspections')
                ->whereHas('tracking', function ($query) {
                    $query->where('status', 'rejected');
                })
                ->first();

            if (!$order) {
                return new ErrorResource(__('messages.order_not_found'));
            }

            if ($order->status == 'paid' || $order->status == 'completed' || $order->status == 'canceled') {
                return new ErrorResource(__('messages.order_already_paid'));
            }

            $periodicInspection = PeriodicInspections::where('order_id', $order->id)->firstOrFail();

            $periodicInspection->update([
                'status' => 'pending',
            ]);

            $order->update([
                'status' => 'pending',
            ]);

            $serviceType = $order->type;

            $users = User::whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->nearby($request->latitude, $request->longitude, 50)
                ->where('role', 'provider')
                ->whereHas('provider', function ($query) use ($serviceType) {
                    $query->where($serviceType, true)
                        ->where('is_active', true);
                })
                ->get();

            $providerIds = $users->pluck('id')->toArray();

            foreach ($providerIds as $providerId) {
                ProviderNotification::create([
                    'provider_id'   => $providerId,
                    'user_id'       => auth()->id(),
                    'order_id'      => $order->id,
                    'message'       => '🚀 New order request',
                    'service_type'  => $order->type,
                    'order_status'  => $order->status, // إضافة حالة الطلب
                ]);
            }

            $pusher = new Pusher(
                env('PUSHER_APP_KEY'),
                env('PUSHER_APP_SECRET'),
                env('PUSHER_APP_ID'),
                ['cluster' => env('PUSHER_APP_CLUSTER'), 'useTLS' => true]
            );

            $message = __('messages.periodic_inspection_updated_successfully');

            foreach ($users as $user) {
                $pusher->trigger('notifications.providers.' . $user->id, 'sent.offer', [
                    'message'       => $message,
                    'order'         => $order,
                    'order_status'  => $order->status, // إضافة حالة الطلب
                    'Provider_ids'  => $providerIds,
                ]);
            }

            // إشعارات FCM
            if ($users->isNotEmpty()) {
                try {
                    $tokens = $users->pluck('fcm_token')->filter()->unique()->toArray();

                    if (!empty($tokens)) {
                        $firebaseService = new FirebaseService();

                        $extraData = [
                            'order_id'     => (string) $order->id, // تحويل إلى string
                            'type'         => __('messages.periodic_inspection'),
                            'order_status' => $order->status, // إضافة حالة الطلب
                            'sound'        => 'notify_sound', // تصحيح الصوت
                        ];

                        $firebaseService->sendNotificationToMultipleUsers(
                            $tokens,
                            __('messages.periodic_inspection'),
                            $message,
                            $extraData
                        );

                        \Log::info(__('messages.notification_sent_with_sound') . ': ' . $extraData);
                    }
                } catch (\Exception $e) {
                    Log::channel('error')->error(__('messages.firebase_notification_failed') . ': ' . $e->getMessage());
                }
            }

            DB::commit();

            return new SuccessResource([
                'message' => __('messages.periodic_inspection_updated_successfully'),
                'data' => $order->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('error')->error(__('messages.error_in_periodic_inspection') . ': ' . $e->getMessage());
            return new ErrorResource($e->getMessage());
        }
    }

    public function payment($order)
    {
        $order = auth('sanctum')->user()->orders()->find($order);

        if (!$order) {
            return apiResponse(404, __('messages.order_not_found'));
        }

        if ($order->status == 'paid') {
            return apiResponse(400, __('messages.order_already_paid'));
        }

        $order->update(['status' => 'approved']);

        broadcast(new ServiceRequestEvent($order, $order->type));

        return apiResponse(200, __('messages.order_paid'), $order);
    }

    public function index(Request $request)
    {
        $orders = auth('sanctum')->user()->orders()->latest()->paginate($request->limit ?? 10);

        return new SuccessResource([
            'message'   => __('messages.data_returned_successfully', ['attr' => __('messages.orders')]),
            'data'    => OrderResource::collection($orders)
        ]);
    }

    public function myOrders(Request $request)
    {
        $orders = Order::where('user_id', auth('sanctum')->id())
            ->latest()->paginate(config("app.pagination"));

        return new SuccessResource([
            'message'   => __('messages.data_returned_successfully', ['attr' => __('messages.orders')]),
            'data'    => OrderResource::collection($orders)
        ]);
    }

    public function show($id)
    {
        try {
            $order = Order::query()
                ->with(['providers', 'expressService', 'userVehicle', 'city'])
                ->where('user_id', auth('sanctum')->id())
                ->find($id);

            if (!$order) {
                return response()->json([
                    'status' => 404,
                    'message' => __('messages.order_not_found'),
                    'data' => null
                ], 404);
            }

            return new SuccessResource([
                'message' => __('messages.data_returned_successfully', ['attr' => __('messages.order')]),
                'data' => new OrderResource($order)
            ]);

        } catch (\Exception $e) {
            Log::channel('error')->error(__('messages.error_in_show_order') . ': ' . $e->getMessage());
            return response()->json([
                'status' => 500,
                'message' => __('messages.something_went_wrong'),
                'data' => ['error' => $e->getMessage()]
            ], 500);
        }
    }

    public function ordersByStatus(Request $request)
    {
        $orders = Order::where('user_id', auth('sanctum')->id())
            ->when($request->status, function ($query) use ($request) {
                return $query->whereIn('status', $request->status);
            })
            ->when($request->type, function ($query) use ($request) {
                return $query->where('type', $request->type);
            })
            ->latest()
            ->paginate(config('app.pagination'));

        return new SuccessResource([
            'message'   => __('messages.data_returned_successfully', ['attr' => __('messages.orders')]),
            'data'      => OrderResource::collection($orders)
        ]);
    }

    public function cancelOrder(Request $request, $id)
    {
        $order = Order::where('user_id', auth('sanctum')->id())->find($id);

        if (!$order) {
            return new SuccessResource([
                'message'   => __('messages.order_not_found')
            ]);
        }

        $order->update([
            'status'    => 'canceled',
            'reason'    => $request->reason
        ]);

        if ($order->provider && $order->provider->fcm_token) {
            try {
                $firebaseService = new FirebaseService();

                $extraData = [
                    'order_id'     => (string) $order->id, // تحويل إلى string
                    'type'         => __('messages.order_canceled'),
                    'order_status' => $order->status, // إضافة حالة الطلب
                    'sound'        => 'notify_sound', // تصحيح الصوت
                ];

                $firebaseService->sendNotificationToUser(
                    $order->provider->fcm_token,
                    __('messages.order_canceled'),
                    __('messages.order_canceled_message', ['reason' => $order->reason]),
                    $extraData
                );

                \Log::info('Notification sent with sound: notify_sound', ['extraData' => $extraData]);
            } catch (\Exception $e) {
                Log::channel('error')->error("Firebase Notification Failed: " . $e->getMessage());
            }
        } else {
            Log::channel('error')->warning("No valid provider or FCM token found for order ID: {$order->id}");
        }

        return new SuccessResource([
            'message'   => __('messages.order_canceled_successfully')
        ]);
    }

    public function rejectOrder(Request $request, $id)
    {
        $request->validate([
            'reason'    => 'nullable|array',
            'reason.*'  => 'string|max:255',
            'images'    => 'nullable|array',
            'images.*'  => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $order = Order::where('user_id', auth('sanctum')->id())->find($id);

            if (!$order) {
                return new SuccessResource([
                    'message' => __('messages.order_not_found')
                ]);
            }

            $imagePaths = [];

            if (!Storage::exists('public/order_rejections')) {
                Storage::makeDirectory('public/order_rejections', 0775, true);
            }

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('order_rejections', 'public');
                    if ($path) {
                        $imagePaths[] = $path;
                    }
                }
            }

            $order->update([
                'status' => 'rejected',
                'reason' => json_encode($request->reason),
                'images' => count($imagePaths) > 0 ? json_encode($imagePaths) : null,
            ]);

            if (!empty($order->provider->fcm_token)) {
                $firebaseService = new FirebaseService();

                $extraData = [
                    'order_id'     => (string) $order->id, // تحويل إلى string
                    'type'         => __('messages.order_rejected'),
                    'order_status' => $order->status, // إضافة حالة الطلب
                    'sound'        => 'notify_sound', // تصحيح الصوت
                ];

                $firebaseService->sendNotificationToUser(
                    $order->provider->fcm_token,
                    __('messages.order_rejected'),
                    __('messages.order_rejected_message', ['reason' => $request->reason]),
                    $extraData
                );

                \Log::info('Notification sent with sound: notify_sound', ['extraData' => $extraData]);
            } else {
                Log::warning('❌ No valid FCM token found for provider ID: ' . $order->provider->id);
            }

            return new SuccessResource([
                'message' => __('messages.order_rejected_successfully'),
                'reasons' => $request->reason,
                'images'  => collect($imagePaths)->map(fn($path) => asset('storage/' . $path)),
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Error in rejectOrder: ' . $e->getMessage());

            return response()->json([
                'message' => 'Something went wrong!',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function rate(Request $request, $id)
    {
        $request->validate([
            'rate'      => 'required|numeric|min:1|max:5',
            'comment'   => 'nullable|string',
        ]);

        $order = Order::where('user_id', auth('sanctum')->id())
            ->where('status', '!=', 'accepted')
            ->where('status', '!=', 'canceled')
            ->where('status', 'completed')
            ->find($id);

        if (!$order) {
            return new ErrorResource(__('messages.order_not_found'));
        }

        $providerId = $order->provider_id;

        if (!User::where('role', 'provider')->where('id', $providerId)->exists()) {
            return response()->json(['error' => 'Invalid provider_id. Provider does not exist.'], 400);
        }

        $order->rates()->Create(
            [
                'user_id'     => auth('sanctum')->id(),
                'provider_id' => $order->provider_id ?? null,
                'rate'        => $request->rate,
                'comment'     => $request->comment,
            ]
        );

        return new SuccessResource([
            'message'   => __('messages.order_rated_successfully')
        ]);
    }
}
