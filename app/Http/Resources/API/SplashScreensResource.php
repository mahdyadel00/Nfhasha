<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SplashScreensResource extends JsonResource
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
            'id'                        => $this->id,
            'image'                     => asset('storage/' . $this->image),
            'title'                     => $this->translate($locale)->title ?? '',
            'description'               => $this->translate($locale)->description ?? '',
        ];
    }
}
