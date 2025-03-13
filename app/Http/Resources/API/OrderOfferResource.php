<?php

namespace App\Http\Resources\API;

use App\Http\Resources\API\Provider\ProviderResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderOfferResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'status'     => $this->status,
            'amount'     => $this->amount,
            'provider'   => $this->whenLoaded('provider', fn() => new ProviderResource($this->provider)),
            'order'      => $this->whenLoaded('order', fn() => new OrderResource($this->order)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

    }
}
