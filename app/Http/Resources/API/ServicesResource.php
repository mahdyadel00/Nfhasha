<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServicesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locale = $request->header('Accept-Language');

        $locale = in_array($locale, ['ar', 'en']) ? $locale : 'en';

        return
        [
            'id' => $this->id,
            'name' => $this->translate($locale)->name ?? '',
            'description' => $this->translate($locale)->description ?? '',
            'instructions' => $this->translate($locale)->instructions ?? '',
            'active' => $this->active,
            'cover' => asset('storage/' . $this->cover),
        ];
    }
}
