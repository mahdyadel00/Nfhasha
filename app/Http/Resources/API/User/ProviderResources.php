<?php

namespace App\Http\Resources\API\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderResources extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
         return parent::toArray($request);

//        return
//            [
//                'id'                                => $this->id,
//                'type'                              => $this->type,
//                'mechanical'                        => $this->mechanical,
//                'plumber'                           => $this->plumber,
//                'electrical'                        => $this->electrical,
//                'puncture'                          => $this->puncture,
//                'battery'                           => $this->battery,
//                'pickup'                            => $this->pickup,
//                'open_locks'                        => $this->open_locks,
//                'full_examination'                  => $this->full_examination,
//                'periodic_examination'              => $this->periodic_examination,
//                'truck_barriers'                    => $this->truck_barriers,
//                'available_from'                    => $this->available_from,
//                'available_to'                      => $this->available_to,
//                'home_service'                      => $this->home_service,
//                'commercial_register'               => $this->commercial_register,
//                'owner_identity'                    => $this->owner_identity,
//                'general_license'                   => $this->general_license,
//                'municipal_license'                 => $this->municipal_license,
//                'is_active'                         => $this->is_active,
//
//            ];
    }
}
