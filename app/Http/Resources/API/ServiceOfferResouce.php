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
        return [
            'id'                            => $this->id,
            'price'                         => $this->price,
            'code'                          => $this->code,
            'duration'                      => $this->duration,
            'name'                          => $this->name,
            'description'                   => $this->description,
            'is_active'                     => $this->is_active ? 'Active' : 'Inactive',
            'image'                         => $this->image ? asset('storage/' . $this->image) : null,
            'created_at'                    => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'created_at_humanly'            => $this->created_at ? $this->created_at->diffForHumans() : null,
            'updated_at'                    => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
            'updated_at_humanly'            => $this->updated_at ? $this->updated_at->diffForHumans() : null,
            'service'                       => $this->whenLoaded('service', function() {
                return new ExpressServiceResource($this->service);
            }),
        ];
    }
}
