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
use App\Models\PickUpTruck;
use App\Models\Provider;
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
        try{
            DB::beginTransaction();

            $expressService = ExpressService::find($request->service_id);

            //create car reservation
            if($expressService->type == 'car_reservations'){
                $inspection_side_array  = explode(',', $request->inspection_side);
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
            if($expressService->type == 'maintenance'){

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

            if($expressService->type == 'comprehensive_inspections'){

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

            if($expressService->type == 'periodic_inspections'){

                PeriodicInspections::create([
                    'user_id'                   => auth()->id(),
                    'express_service_id'        => $request->service_id,
                    'user_vehicle_id'           => $request->vehicle_id,
                    'pick_up_truck_id'          => $request->pick_up_truck_id,
                    'city_id'                   => $request->city_id,
                    'inspection_type'           => $request->inspection_type,
                    'address'                   => $request->address,
                    'latitude'                  => $request->latitude,
                    'longitude'                 => $request->longitude,
                ]);

            }


            $order = Order::create([
                'user_id'               => auth()->id(),
                'express_service_id'    => $request->service_id,
                'user_vehicle_id'       => $request->vehicle_id,
                'city_id'               => $request->city_id ?? null,
                'status'                => 'pending',
                'from_lat'              => $request->from_lat ?? $request->latitude,
                'from_long'             => $request->from_long ?? $request->longitude,
                'type'                  => ExpressService::find($request->service_id)->type,
                'payment_method'        => $request->payment_method ?? 'cash',
                'total_cost'            => $expressService->price,
                'address'               => $request->address,
            ]);

            DB::commit();

//            send notification to provider
//            broadcast(new ServiceRequestEvent($order , $order->type));
            $pusher = new Pusher(
                env('PUSHER_APP_KEY'),
                env('PUSHER_APP_SECRET'),
                env('PUSHER_APP_ID'),
                ['cluster' => env('PUSHER_APP_CLUSTER'), 'useTLS' => true]
            );

            $pusher->trigger('notifications.providers.' . $order->user_id, 'sent.offer', [
                'message'   => __('messages.new_order') ,
                'order'     => $order,
            ]);


            return new SuccessResource([
                'message' => __('messages.order_created_successfully') ,
            ]);
        }catch (\Exception $e){
            DB::rollBack();
            dd($e->getFile() , $e->getLine() , $e->getMessage());
            Log::channel('error')->error('Error in periodicExamination: ' . $e->getMessage());
            return new ErrorResource($e->getMessage());
        }
    }

    public function payment($order)
    {
        $order = auth('sanctum')->user()->orders()->find($order);

        if(!$order)
        {
            return apiResponse(404 , __('messages.order_not_found'));
        }

        if($order->status == 'paid')
        {
            return apiResponse(400 , __('messages.order_already_paid'));
        }

        $order->update(['status' => 'approved']);


        broadcast(new ServiceRequestEvent($order , $order->type));

        return apiResponse(200 , __('messages.order_paid') , $order);
    }

    public function index(Request $request)
    {
        $orders = auth('sanctum')->user()->orders()->latest()->paginate($request->limit ?? 10);

        return new SuccessResource([
            'message'   => __('messages.data_returned_successfully' , ['attr' => __('messages.orders')]) ,
            'data'    => OrderResource::collection($orders)
        ]);
    }

    public function myOrders(Request $request)
    {
        $orders = Order::where('user_id' , auth('sanctum')->id())->latest()->paginate(config("app.pagination"));

        return new SuccessResource([
            'message'   => __('messages.data_returned_successfully' , ['attr' => __('messages.orders')]) ,
            'data'    => OrderResource::collection($orders)
        ]);
    }


    public function show($id)
    {
        $order = Order::where('user_id' , auth('sanctum')->id())->find($id);

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
        $orders = Order::where('user_id' , auth('sanctum')->id())
            ->when($request->status , function ($query) use ($request) {
                return $query->whereIn('status' , $request->status);
            })
            ->when($request->type , function ($query) use ($request) {
                return $query->where('type' , $request->type);
            })
            ->latest()
            ->paginate(config('app.pagination'));

        return new SuccessResource([
            'message'   => __('messages.data_returned_successfully' , ['attr' => __('messages.orders')]) ,
            'data'    => OrderResource::collection($orders)
        ]);
    }

    public function cancelOrder($id)
    {
        $order = Order::where('user_id' , auth('sanctum')->id())->find($id);

        if(!$order)
        {
            return new SuccessResource([
                'message'   => __('messages.order_not_found')
            ]);
        }

        $order->update(['status' => 'canceled']);

        return new SuccessResource([
            'message'   => __('messages.order_canceled_successfully')
        ]);
    }

}
