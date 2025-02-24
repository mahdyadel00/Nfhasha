<?php

namespace App\Http\Resources\API\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceResource extends JsonResource
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
            'maintenance_type'          => $this->maintenance_type,
            'maintenance_description'   => $this->maintenance_description,
            'address'                   => $this->address,
            'latitude'                  => $this->latitude,
            'longitude'                 => $this->longitude,
            'is_working'                => $this->is_working == 1 ? true : false,
            'image'                     => asset('storage/' . $this->image),
            'note'                      => $this->note,
            'created_at'                => $this->created_at,
            'updated_at'                => $this->updated_at,
        ];
    }
}
