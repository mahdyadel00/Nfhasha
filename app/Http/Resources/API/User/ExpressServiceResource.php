<?php

namespace App\Http\Resources\API\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpressServiceResource extends JsonResource
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
            'id'                        => $this->id,
            'is_active'                 => $this->is_active == 1 ? true : false,
            'type'                      => $this->type,
            'price'                     => $this->price,
            'vat'                       => $this->vat,
            'created_at'                => $this->created_at,
            'updated_at'                => $this->updated_at,
            'name'                      => $this->name,
            'battery_image'             => asset('storage/' . $this->punctureServices()->latest('created_at')->first()?->battery_image),
            'type_battery'              => $this->punctureServices()->latest('created_at')->first()?->type_battery,
            'car_reservation'           =>  CarReservationsResource::collection($this->carReservations),
        ];
    }
}
