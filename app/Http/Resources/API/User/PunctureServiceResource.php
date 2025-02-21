<?php

namespace App\Http\Resources\API\User;

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

        return
        [
            'id'                            => $this->id,
            'from_latitude'                 => $this->from_latitude,
            'from_longitude'                => $this->from_longitude,
            'to_latitude'                   => $this->to_latitude,
            'to_longitude'                  => $this->to_longitude,
            'type_battery'                  => $this->type_battery,
            'battery_image'                 => asset('storage/' . $this->battery_image),
            'notes'                         => $this->notes,
            'amount'                        => $this->amount,
            'status'                        => $this->status,
            'reason'                        => $this->reason,
            'address'                       => $this->address,
            'distanition'                   => $this->distanition,
            'created_at'                    => $this->created_at,
            'updated_at'                    => $this->updated_at,
            'express_service'               => new ExpressServiceResource($this->express_service),
            'user'                          => new UserResource($this->user),
            'user_vehicle'                  => new VehiclesResource($this->user_vehicle),
            'provider'                      => new UserResource($this->provider),
        ];
    }
}
