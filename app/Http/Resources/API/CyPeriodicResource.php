<?php

namespace App\Http\Resources\API;

use App\Http\Resources\API\User\VehiclesResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CyPeriodicResource extends JsonResource
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
            'title'                         => $this->title,
            'price'                         => number_format($this->price, 2),
            'status'                        => $this->status,
            'vat'                           => number_format($this->vat, 2),
            'type'                          => $this->type,
            'lat'                           => $this->lat,
            'lng'                           => $this->lng,
            'created_at'                    => $this->created_at,
            'updated_at'                    => $this->updated_at,
            'user_vehicle'                  => new VehiclesResource($this->userVehicle),
            'city'                          => new CityResource($this->city),
        ];
    }
}
