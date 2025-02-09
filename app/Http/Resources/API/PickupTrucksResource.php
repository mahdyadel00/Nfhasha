<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PickupTrucksResource extends JsonResource
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
            'name'                      => $this->name,
            'price'                     => number_format($this->price, 2),
            'image'                     => asset('storage/' . $this->image),
        ];
    }
}
