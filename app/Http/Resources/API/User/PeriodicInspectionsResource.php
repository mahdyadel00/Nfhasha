<?php

namespace App\Http\Resources\API\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PeriodicInspectionsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $images = json_decode($this->inspection_reject_image, true);
        $images = array_map(function ($image) {
            return asset('storage/' . $image);
        }, $images);
        $this->inspection_reject_image = $images;

        return
        [
            'id'                        => $this->id,
            'inspectionType'            => TypePeriodicInspectionsResource::make($this->inspectionType),
            'address'                   => $this->address,
            'latitude'                  => $this->latitude,
            'longitude'                 => $this->longitude,
            'status'                    => $this->status,
            'inspection_reject_image'   => $this->inspection_reject_image,
            'inspection_reject_reason'  => $this->inspection_reject_reason,
            'created_at'                => $this->created_at,
            'updated_at'                => $this->updated_at,
        ];
    }
}
