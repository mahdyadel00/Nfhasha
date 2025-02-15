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
use App\Models\CyPeriodic;
use App\Models\Order;
use App\Models\PickUpTruck;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function cyPeriodics()
    {
        $cyPeriodics = CyPeriodic::paginate(config('app.pagination'));

        return count($cyPeriodics) > 0
            ? CyPeriodicResource::collection($cyPeriodics)
            : new ErrorResource('No cy periodics found');
    }
    public function periodicExamination(StoreperiodicExaminationRequest $request)
    {
//        $cyPeriodic             = CyPeriodic::find($request->cy_periodic_id);
//        $pickUpTruckPrice       = PickUpTruck::find($request->pick_up_truck_id)->price;
//
//        $total_cost = ($cyPeriodic->price + $pickUpTruckPrice) * $cyPeriodic->vat / 100 + $cyPeriodic->price + $pickUpTruckPrice;
//
//        $company_profit = ($cyPeriodic->price + $pickUpTruckPrice) * $cyPeriodic->vat / 100;
//
//        $order = auth('sanctum')->user()->orders()->create($request->validated() + ['type' => 'periodic_examination' , 'status' => 'pending']);



//        broadcast(new ServiceRequestEvent($order , $order->type));

//        return new SuccessResource([
//            'message'   => __('messages.order_created_successfully') ,
//        ]);
        try{
            DB::beginTransaction();

            //create car reservation
            $car_reservation = CarReservations::create([
                'user_id'               => auth()->id(),
                'express_service_id'    => $request->service_id,
                'vehicle_id'            => $request->vehicle_id,
                'city_id'               => $request->city_id,
                'inspection_side'       => $request->inspection_side,
                'date'                  => $request->date,
                'time'                  => $request->time,
            ]);

            $ordes = Order::create([
                'user_id'               => auth()->id(),
                'express_service_id'    => $request->service_id,
                'vehicle_id'            => $request->vehicle_id,
                'city_id'               => $request->city_id,
                'status'                => 'pending',
            ]);

            DB::commit();

            return new SuccessResource([
                'message' => __('messages.order_created_successfully') ,
            ]);
        }catch (\Exception $e){
            DB::rollBack();
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
            'orders'    => OrderResource::collection($orders)
        ]);
    }

    public function myOrders(Request $request)
    {
        $orders = Order::where('user_id' , auth('sanctum')->id())->paginate(config("app.pagination"));

        return new SuccessResource([
            'message'   => __('messages.data_returned_successfully' , ['attr' => __('messages.orders')]) ,
            'orders'    => OrderResource::collection($orders)
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
            'order'     => new OrderResource($order)
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
            ->paginate(config('app.pagination'));

        return new SuccessResource([
            'message'   => __('messages.data_returned_successfully' , ['attr' => __('messages.orders')]) ,
            'orders'    => OrderResource::collection($orders)
        ]);
    }

}
