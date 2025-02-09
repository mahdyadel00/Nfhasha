<?php

namespace App\Http\Controllers\API\User;

use App\Events\ServiceRequestEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\User\StoreperiodicExaminationRequest;
use App\Http\Resources\API\OrderResource;
use App\Http\Resources\API\SuccessResource;
use App\Models\CyPeriodic;
use App\Models\PickUpTruck;
use App\Models\Provider;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function periodicExamination(StoreperiodicExaminationRequest $request)
    {
        $cyPeriodicPrice = CyPeriodic::find($request->cy_periodic_id)->price;
        $periodic_inspection_service_tax = settings()->get('periodic_inspection_service_tax');
        $pickUpTruckPrice = PickUpTruck::find($request->pick_up_truck_id)->price;


        $request['$total_cost'] = ($cyPeriodicPrice + $pickUpTruckPrice) * $periodic_inspection_service_tax / 100 + $cyPeriodicPrice + $pickUpTruckPrice;

        $request['company_profit'] = ($cyPeriodicPrice + $pickUpTruckPrice) * $periodic_inspection_service_tax / 100;

        $order = auth('sanctum')->user()->orders()->create($request->validated() + ['type' => 'periodic_examination' , 'status' => 'pending']);


        broadcast(new ServiceRequestEvent($order , $order->type));

        return apiResponse(201 , __('messages.order_placed') , $order);
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
}
