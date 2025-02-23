<?php

namespace App\Http\Resources\API\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarReservationsResource extends JsonResource
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
            'inspection_side'           => $this->inspection_side,
            'time'                      => $this->time,
            'created_at'                => $this->created_at,
            'updated_at'                => $this->updated_at,
        ];
    }
}
