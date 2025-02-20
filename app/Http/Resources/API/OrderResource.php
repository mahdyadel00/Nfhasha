<?php

namespace App\Http\Resources\API;

use App\Http\Resources\API\Provider\ProviderResource;
use App\Http\Resources\API\Provider\PunctureServiceResource;
use App\Http\Resources\API\User\ExpressServiceResource;
use App\Http\Resources\API\User\UserResource;
use App\Http\Resources\API\User\VehiclesResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return
        [
            'id'                            => $this->id,
            'type'                          => $this->type,
            'status'                        => $this->status,
            'payment_method'                => $this->payment_method,
            'type_from'                     => $this->type_from,
            'position'                      => $this->position,
            'date_at'                       => $this->date_at,
            'scheduled_at'                  => $this->scheduled_at,
            'time_at'                       => $this->time_at,
            'address'                       => $this->address,
            'lat'                           => $this->lat,
            'long'                          => $this->long,
            'address_to'                    => $this->address_to,
            'reason'                        => $this->reason,
            'lat_to'                        => $this->lat_to,
            'long_to'                       => $this->long_to,
            'details'                       => $this->details,
            'canceled_by'                   => $this->canceled_by,
            'canceled_by_provider'          => $this->canceled_by_provider,
            'update_by'                     => $this->update_by,
            'company_profit'                => $this->company_profit,
            'total_cost'                    => $this->total_cost,
            'created_at'                    => $this->created_at,
            'updated_at'                    => $this->updated_at,
            'user'                          => new UserResource($this->user),
            'express_service'               => new ExpressServiceResource($this->expressService),
            'provider'                      => new ProviderResource($this->provider),
            'userVehicle'                   => new VehiclesResource($this->userVehicle),
            'city'                          => new CityResource($this->city),
            'pickUpTruck'                   => new PickupTrucksResource($this->pickUpTruck),
        ];
    }
}
