<?php

namespace App\Http\Resources\API\Provider;

use App\Http\Resources\API\CitiesResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class   ProviderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'id'                    => $this->id,
            'type'                  => $this->type,
            'mechanical'            => $this->mechanical,
            'plumber'               => $this->plumber,
            'electrical'            => $this->electrical,
            'puncture'              => $this->puncture,
            'battery'               => $this->battery,
            'pickup'                => $this->pickup,
            'open_locks'            => $this->open_locks,
            'full_examination'      => $this->full_examination,
            'periodic_examination'  => $this->periodic_examination,
            'truck_barriers'        => $this->truck_barriers,
            'pick_up_truck_id'      => $this->pick_up_truck_id,
            'available_from'        => $this->available_from,
            'available_to'          => $this->available_to,
            'home_service'          => $this->home_service,
            'commercial_register'   => asset('storage/' . $this->commercial_register),
            'owner_identity'        => asset('storage/' . $this->owner_identity),
            'general_license'       => asset('storage/' . $this->general_license),
            'municipal_license'     => asset('storage/' . $this->municipal_license),
            'is_active'             => $this->is_active,
            'created_at'            => $this->created_at,
            'updated_at'            => $this->updated_at,
            'city'                  => CitiesResource::make($this->city),
            'district'              => CitiesResource::make($this->district),
        ];
    }
}
