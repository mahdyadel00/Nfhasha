<?php

namespace App\Http\Resources\API\Provider;

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

        return [
            'id'        => $this->id,
            // 'order_id'  => $this->order_id,
            // 'user_id'   => $this->user_id,
            'rate'      => $this->rate,
            'comment'   => $this->comment,
        ];
    }
}
