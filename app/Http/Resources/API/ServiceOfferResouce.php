<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceOfferResouce extends JsonResource
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
            'price'                         => $this->price,
            'duration'                      => $this->duration,
            'name'                          => $this->name,
            'description'                   => $this->description,
            'is_active'                     => $this->is_active ? 'Active' : 'Inactive',
            'image'                         => asset('storage/' . $this->image),
            'created_at'                    => $this->created_at->format('Y-m-d H:i:s'),
            'created_at_humanly'            => $this->created_at->diffForHumans(),
        ];
    }
}
