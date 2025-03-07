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

            //create car reservation
            if ($expressService->type == 'car_reservations') {
                $inspection_side_array = is_array($request->inspection_side)
                    ? $request->inspection_side
                    : explode(',', $request->inspection_side);

                $inspection_side_string = implode(',', $inspection_side_array);

                CarReservations::create([
                    'user_id'               => auth()->id(),
                    'express_service_id'    => $request->service_id,
                    'user_vehicle_id'       => $request->vehicle_id,
                    'city_id'               => $request->city_id,
                    'inspection_side'       => $inspection_side_string,
                    'date'                  => $request->date,
                    'time'                  => $request->time,
                ]);
            }

            //create maintenance
            if ($expressService->type == 'maintenance') {

                //creat array for image
                $image = [];
                if ($request->hasFile('image')) {
                    foreach ($request->file('image') as $file) {
                        $image[] = uploadImage($file, 'maintences');
                    }
                }

                Maintenance::create([
                    'user_id'                   => auth()->id(),
                    'express_service_id'        => $request->service_id,
                    'user_vehicle_id'           => $request->vehicle_id,
                    'pick_up_truck_id'          => $request->pick_up_truck_id,
                    'maintenance_type'          => $request->maintenance_type,
                    'maintenance_description'   => $request->maintenance_description,
                    'address'                   => $request->address,
                    'latitude'                  => $request->latitude,
                    'longitude'                 => $request->longitude,
                    'is_working'                => $request->is_working ? 1 : 0,
                    'image'                     => json_encode($image),
                    'note'                      => $request->note,
                ]);
            }

            if ($expressService->type == 'comprehensive_inspections') {

                $comprehensive_inspection       = ComprehensiveInspections::create([
                    'user_id'                   => auth()->id(),
                    'express_service_id'        => $request->service_id,
                    'user_vehicle_id'           => $request->vehicle_id,
                    'pick_up_truck_id'          => $request->pick_up_truck_id,
                    'city_id'                   => $request->city_id,
                    'date'                      => $request->date,
                    'address'                   => $request->address,
                    'latitude'                  => $request->latitude,
                    'longitude'                 => $request->longitude,
                ]);
            }

            if ($expressService->type == 'periodic_inspections') {

                PeriodicInspections::create([
                    'user_id'                   => auth()->id(),
                    'express_service_id'        => $request->service_id,
                    'user_vehicle_id'           => $request->vehicle_id,
                    'pick_up_truck_id'          => $request->pick_up_truck_id,
                    'city_id'                   => $request->city_id,
                    'inspection_type_id'        => $request->inspection_type_id,
                    'address'                   => $request->address,
                    'latitude'                  => $request->latitude,
                    'longitude'                 => $request->longitude,
                    'status'                    => 'pending',
                ]);
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
                'type'                  => ExpressService::find($request->service_id)->type,
                'payment_method'        => $request->payment_method ?? 'cash',
                'total_cost'            => $expressService->price,
                'address'               => $request->address,
                'address_to'            => $request->address_to,
            ]);

            $users = User::whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->nearby($request->latitude, $request->longitude, 50)
                ->where('role', 'provider')
                ->get();

            if ($users->isNotEmpty()) {
                try {
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
                            'message' => $message,
                            'order'   => $order,
                        ]);
                    }

                    $tokens = $users->pluck('fcm_token')
                        ->filter() // حذف القيم الفارغة (null أو "")
                        ->unique() // إزالة التكرارات
                        ->toArray();

                    if (!empty($tokens)) {
                        $firebaseService = new FirebaseService();
                        $firebaseService->sendNotificationToMultipleUsers($tokens, $message, $message);
                    }
                } catch (\Exception $e) {
                    Log::channel('error')->error("Firebase Notification Failed: " . $e->getMessage());
                }
            }

            DB::commit();

            // $firebaseService = new FirebaseService();
            // $firebaseService->sendNotificationToMultipleUsers($users->pluck('fcm_token')->toArray(), 'New order', 'New order');

            return new SuccessResource([
                'message' => __('messages.order_created_successfully'),
                'data'    => $order->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e->getFile(), $e->getLine(), $e->getMessage());
            Log::channel('error')->error('Error in periodicExamination: ' . $e->getMessage());
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

        $firebaseService = new FirebaseService();
        $firebaseService->sendNotificationToUser($order->provider->fcm_token, 'Order canceled', 'Order canceled');
        return new SuccessResource([
            'message'   => __('messages.order_canceled_successfully')
        ]);
    }

    public function rejectOrder(Request $request, $id)
    {
        $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        $order = Order::where('user_id', auth('sanctum')->id())->find($id);

        if (!$order) {
            return new SuccessResource([
                'message' => __('messages.order_not_found')
            ]);
        }

        $order->update([
            'status' => $request->status,
            'reason' => $request->reason,
        ]);


        // $pusher = new Pusher(
        //     env('PUSHER_APP_KEY'),
        //     env('PUSHER_APP_SECRET'),
        //     env('PUSHER_APP_ID'),
        //     ['cluster' => env('PUSHER_APP_CLUSTER'), 'useTLS' => true]
        // );

        // $pusher->trigger('notifications.providers.' . $order->user_id, 'sent.offer', [
        //     'message'       => __('messages.order_rejected'),
        //     'order'         => $order,
        //     'provider_id'   => $order->provider_id
        // ]);
        $firebaseService = new FirebaseService();
        $firebaseService->sendNotificationToUser($order->provider->fcm_token, 'Order rejected', 'Order rejected');

        return new SuccessResource([
            'message' => __('messages.order_rejected_successfully')
        ]);
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
