<?php

namespace App\Http\Resources\API\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceResource extends JsonResource
{

    public function getImages()
{
    // فك ترميز الصور المخزنة بصيغة JSON أو إرجاع مصفوفة فارغة في حالة null
    $images = json_decode($this->image, true) ?? [];

    // التأكد أن الصور عبارة عن مصفوفة ثم تحويل كل صورة إلى رابط كامل
    return is_array($images)
        ? array_map(fn($image) => asset('storage/' . $image), $images)
        : [];
}

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'id'                        => $this->id,
            'maintenance_type'          => $this->maintenance_type,
            'maintenance_description'   => $this->maintenance_description,
            'address'                   => $this->address,
            'latitude'                  => $this->latitude,
            'longitude'                 => $this->longitude,
            'is_working'                => (bool) $this->is_working,
            'images'                    => $this->getImages(),
            'note'                      => $this->note,
            'created_at'                => $this->created_at,
            'updated_at'                => $this->updated_at,
        ];

    }
}
