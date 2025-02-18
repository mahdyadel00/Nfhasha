<?php

namespace App\Http\Controllers\API\User;

use App\Events\ProviderNotification;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\User\ExpressService\StoreExpressServiceRequest;
use App\Http\Resources\API\ErrorResource;
use App\Http\Resources\API\Provider\PunctureServiceResource;
use App\Http\Resources\API\SuccessResource;
use App\Http\Resources\API\User\ExpressServiceResource;
use App\Models\ExpressService;
use App\Models\Order;
use App\Models\PunctureService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpressServiceController extends Controller
{
    public function index(Request $request)
    {
        $express_services = ExpressService::where('type' , $request->type)->paginate(config("app.pagination"));

        return Count($express_services) > 0
            ? ExpressServiceResource::collection($express_services)
            : new ErrorResource('No express services found');
    }

    public function store(StoreExpressServiceRequest $request)
    {
        try{
            DB::beginTransaction();

            $users = User::whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->nearby($request->from_latitude, $request->from_longitude, 50)
                ->where('role', 'provider')
                ->orderBy('distance')
                ->get();

            $express_service = PunctureService::create([
                'express_service_id'    => $request->express_service_id,
                'user_id'               => auth()->id(),
                'user_vehicle_id'       => $request->user_vehicle_id ?? null,
                'address'               => $request->address,
                'distanition'           => $request->distanition ?? null,
                'from_latitude'         => $request->from_latitude,
                'from_longitude'        => $request->from_longitude,
                'to_latitude'           => $request->to_latitude ?? null,
                'to_longitude'          => $request->to_longitude ?? null,
                'type_battery'          => $request->type_battery ?? null,
                'battery_image'         => $request->battery_image ? $request->battery_image->store('express_services') : null,
                'notes'                 => $request->notes ?? null,
                'amount'                => $request->amount,
                'status'                => 'pending',
            ]);

            //create order
            $order = Order::create([
                'user_id'               => auth()->id(),
                'express_service_id'    => $request->express_service_id,
                'amount'                => $request->amount,
                'status'                => 'pending',
                'from_latitude'         => $request->from_latitude,
                'from_longitude'        => $request->from_longitude,
                'to_latitude'           => $request->to_latitude ?? null,
                'to_longitude'          => $request->to_longitude ?? null,
                'type'                  => $express_service->expressService->type,
                'payment_method'        => $request->payment_method ?? 'cash',
                'total_cost'            => $express_service->amount,
            ]);

            //send notification to provider
            Broadcast(new ProviderNotification('New express service request', $users->pluck('id')->toArray() , $express_service));
            DB::commit();

            return new SuccessResource([
                'message' => __('messages.express_service_created'),
            ]);

        }catch(\Exception $e){
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
