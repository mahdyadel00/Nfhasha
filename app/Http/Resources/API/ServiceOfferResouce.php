<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\API\User\ExpressServiceResource;
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
            'code'                          => $this->code,
            'duration'                      => $this->duration,
            'name'                          => $this->name,
            'description'                   => $this->description,
            'is_active'                     => $this->is_active ? 'Active' : 'Inactive',
            'image'                         => asset('storage/' . $this->image),
            'created_at'                    => $this->created_at->format('Y-m-d H:i:s'),
            'created_at_humanly'            => $this->created_at->diffForHumans(),
            'updated_at'                    => $this->updated_at->format('Y-m-d H:i:s'),
            'updated_at_humanly'            => $this->updated_at->diffForHumans(),
            'service'                       => new ExpressServiceResource($this->service),

        ];
    }
}