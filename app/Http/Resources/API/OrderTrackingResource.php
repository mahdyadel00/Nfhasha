<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderTrackingResource extends JsonResource
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
            'status'                        => $this->status,
            'inspection_reject_reason' => $this->order?->expressService?->periodicInspections?->inspection_reject_reason ?? null,
            'inspection_reject_images' => collect(json_decode($this->order?->expressService?->periodicInspections?->inspection_reject_image ?? '[]', true))
                ->map(fn($path) => asset('storage/' . $path)),

            'created_at'                    => $this->created_at,
            'updated_at'                    => $this->updated_at,
        ];
    }
}
