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
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Pusher\Pusher;
use Illuminate\Support\Facades\Storage;


class OrderController extends Controller
{
    public function cyPeriodics()
    {
        $cyPeriodics = CyPeriodic::paginate(config('app.pagination'));

        return count($cyPeriodics) > 0
            ? CyPeriodicResource::collection($cyPeriodics)
            : new ErrorResource('No cy periodics found');
    }
    public function createOrder(StoreperiodicExaminationRequest $request)
    {
        try {
            DB::beginTransaction();

            $expressService = ExpressService::find($request->service_id);

            // ✅ إنشاء الطلب أولًا قبل إضافة أي حجز
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

            // ✅ إضافة order_id عند إنشاء الخدمة
            if ($expressService->type == 'car_reservations') {
                $inspection_side_array = is_array($request->inspection_side)
                    ? $request->inspection_side
                    : explode(',', $request->inspection_side);

                $inspection_side_string = implode(',', $inspection_side_array);

                CarReservations::create([
                    'order_id'              => $order->id, // ✅ إضافة order_id
                    'user_id'               => auth()->id(),
                    'express_service_id'    => $request->service_id,
                    'user_vehicle_id'       => $request->vehicle_id,
                    'city_id'               => $request->city_id,
                    'inspection_side'       => $inspection_side_string,
                    'date'                  => $request->date,
                    'time'                  => $request->time,
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
                    'order_id'              => $order->id, // ✅ إضافة order_id
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
                    'order_id'              => $order->id, // ✅ إضافة order_id
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
                    'order_id'              => $order->id, // ✅ إضافة order_id
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
                        ->where('is_active', true); // ✅ التحقق من أن البروفايدر نشط
                })
                ->get();

            $providerIds = $users->pluck('id')->toArray();


            $pusher = new Pusher(
                env('PUSHER_APP_KEY'),
                env('PUSHER_APP_SECRET'),
                env('PUSHER_APP_ID'),
                ['cluster' => env('PUSHER_APP_CLUSTER'), 'useTLS' => true]
            );

            $message = match ($order->type) {
                'battery'  => '🔋 Battery order request',
                'towing'   => '🚛 Towing order request',
                'puncture' => '🛞 Puncture repair order request',
                default    => '🚀 New order request',
            };

            foreach ($users as $user) {
                $pusher->trigger('notifications.providers.' . $user->id, 'sent.offer', [
                    'message'       => $message,
                    'order'         => $order,
                    'Provider_ids'  => $providerIds, // ✅ إرسال قائمة الـ IDs
                ]);
            }

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
                'message' => __('messages.order_created_successfully'),
                'data'    => $order->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('error')->error('Error in periodicExamination: ' . $e->getMessage());
            return new ErrorResource($e->getMessage());
        }
    }


    public function updatePeriodicInspection(Request $request, $orderId)
    {
        try {
            DB::beginTransaction();

            // 🔹 جلب الطلب المرتبط بالفحص الدوري
            $order = Order::where('id', $orderId)
                ->where('type', 'periodic_inspections')
                ->where('status', 'rejected')
                ->firstOrFail();

            if (!$order) {
                return new ErrorResource(__('messages.order_not_found'));
            }

            if ($order->type != 'periodic_inspections') {
                return new ErrorResource(__('messages.order_not_found'));
            }

            if ($order->status == 'paid' || $order->status == 'completed' || $order->status == 'canceled') {
                return new ErrorResource(__('messages.order_already_paid'));
            }


            // 🔹 تحديث بيانات الفحص الدوري فقط
            $periodicInspection = PeriodicInspections::where('order_id', $order->id)->firstOrFail();

            $periodicInspection->update([
                'inspection_type_id' => $request->inspection_type_id ?? $periodicInspection->inspection_type_id,
                'address'            => $request->address ?? $periodicInspection->address,
                'latitude'           => $request->latitude ?? $periodicInspection->latitude,
                'longitude'          => $request->longitude ?? $periodicInspection->longitude,
                'status'             => 'pending', // ✅ إعادة الحالة إلى "pending"
            ]);

            // 🔹 تحديث حالة الطلب أيضًا إلى "pending"
            $order->update(['status' => 'pending']);

            // 🔹 البحث عن مزودي الخدمة القريبين مرة أخرى وإرسال الإشعارات
            $serviceType = $order->type;

            $users = User::whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->nearby($request->latitude, $request->longitude, 50)
                ->where('role', 'provider')
                ->whereHas('provider', function ($query) use ($serviceType) {
                    $query->where($serviceType, true)
                        ->where('is_active', true); // ✅ التحقق من أن البروفايدر نشط
                })
                ->get();

            $providerIds = $users->pluck('id')->toArray();

            // ✅ إعادة إرسال إشعار Pusher
            $pusher = new Pusher(
                env('PUSHER_APP_KEY'),
                env('PUSHER_APP_SECRET'),
                env('PUSHER_APP_ID'),
                ['cluster' => env('PUSHER_APP_CLUSTER'), 'useTLS' => true]
            );

            $message = '🔄 Periodic inspection request updated';

            foreach ($users as $user) {
                $pusher->trigger('notifications.providers.' . $user->id, 'sent.offer', [
                    'message'       => $message,
                    'order'         => $order,
                    'Provider_ids'  => $providerIds,
                ]);
            }

            // ✅ إعادة إرسال إشعارات Firebase
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
                'message' => __('messages.periodic_inspection_updated_successfully'),
                'data'    => $orderId
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('error')->error('Error updating periodic inspection: ' . $e->getMessage());
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
        $order = Order::where('user_id', auth('sanctum')->id())->find($id);

        if (!$order) {
            return new SuccessResource([
                'message'   => __('messages.order_not_found')
            ]);
        }

        return new SuccessResource([
            'message'   => __('messages.data_returned_successfully', ['attr' => __('messages.order')]),
            'data'     => new OrderResource($order)
        ]);
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
        // 🔹 البحث عن الطلب الخاص بالمستخدم
        $order = Order::where('user_id', auth('sanctum')->id())->find($id);

        // ✅ إذا لم يتم العثور على الطلب، إرجاع رسالة خطأ
        if (!$order) {
            return new SuccessResource([
                'message'   => __('messages.order_not_found')
            ]);
        }

        // ✅ تحديث حالة الطلب إلى "ملغي"
        $order->update([
            'status'    => 'canceled',
            'reason'    => $request->reason
        ]);

        // ✅ التحقق من وجود مزود للخدمة قبل محاولة إرسال إشعار
        if ($order->provider && $order->provider->fcm_token) {
            try {
                $firebaseService = new FirebaseService();
                $firebaseService->sendNotificationToUser(
                    $order->provider->fcm_token,
                    __('messages.order_canceled_title'),
                    __('messages.order_canceled_body')
                );
            } catch (\Exception $e) {
                Log::channel('error')->error("Firebase Notification Failed: " . $e->getMessage());
            }
        } else {
            Log::channel('error')->warning("No valid provider or FCM token found for order ID: {$order->id}");
        }

        // ✅ إرجاع استجابة نجاح
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

            // ✅ تأكد أن مجلد التخزين متاح
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

            // ✅ إرسال إشعار عبر FCM إذا كان هناك `fcm_token`
            if (!empty($order->provider->fcm_token)) {
                $firebaseService = new FirebaseService();
                $firebaseService->sendNotificationToUser(
                    $order->provider->fcm_token,
                    'Order Rejected',
                    'Your order has been rejected. Check the reasons and images in your app.'
                );
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