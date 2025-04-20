<?php

namespace App\Http\Resources;

use App\Http\Resources\API\User\UserResource;
use App\Http\Resources\API\Provider\ProviderResource;
use App\Http\Resources\API\OrderResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'message'           => $this->message,
            'service_type'      => $this->service_type,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
            'user'              => UserResource::make($this->user),
            'provider'          => ProviderResource::make($this->provider),
            'order'             => OrderResource::make($this->order),
        ];
    }
}
