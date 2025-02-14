<?php

namespace App\Http\Resources\API\Provider;

use App\Http\Resources\API\User\ExpressServiceResource;
use App\Http\Resources\API\User\UserResource;
use App\Http\Resources\API\User\VehiclesResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PunctureServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if($this->status == 'accepted'){
            $order = Order::where('user_id', $this->user_id)->latest()->first();
//            dd($order , $order->user->where('role' , 'provider'));
            $acceptedProvider = $order ? $order->user->where('role' , 'provider')->first() : null;
        }else{
            $acceptedProvider = null;
        }

        return
        [
            'id'                            => $this->id,
            'from_latitude'                 => $this->from_latitude,
            'from_longitude'                => $this->from_longitude,
            'to_latitude'                   => $this->to_latitude,
            'to_longitude'                  => $this->to_longitude,
            'type_battery'                  => $this->type_battery,
            'battery_image'                 => $this->battery_image,
            'notes'                         => $this->notes,
            'amount'                        => $this->amount,
            'status'                        => $this->status,
            'reason'                        => $this->reason,
            'created_at'                    => $this->created_at,
            'updated_at'                    => $this->updated_at,
            'express_service'               => new ExpressServiceResource($this->expressService),
            'user'                          => new UserResource($this->user),
            'user_vehicle'                  => new VehiclesResource($this->userVehicle),
            'provider'                      => $acceptedProvider ? new UserResource($acceptedProvider) : null,

        ];
    }
}
