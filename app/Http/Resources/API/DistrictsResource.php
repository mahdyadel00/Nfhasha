<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DistrictsResource extends JsonResource
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
            'id' => $this->id,
            'name' => $this->name,
            'city_id' => $this->city->name,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'created_at_humanly' => $this->created_at->diffForHumans(),
        ];
    }
}
