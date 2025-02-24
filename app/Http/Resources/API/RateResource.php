<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RateResource extends JsonResource
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
            'rate'                          => $this->rate,
            'comment'                       => $this->comment,
            'created_at'                    => $this->created_at->format('Y-m-d H:i:s'),
            'created_at_humanly'            => $this->created_at->diffForHumans(),
            'updated_at'                    => $this->updated_at->format('Y-m-d H:i:s'),
            'updated_at_humanly'            => $this->updated_at->diffForHumans(),
        ];
    }
}
