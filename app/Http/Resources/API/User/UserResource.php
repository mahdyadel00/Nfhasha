<?php

namespace App\Http\Resources\API\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\API\Provider\ProviderResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {


        return [
            'id'                    => $this->id,
            'name'                  => $this->name,
            'phone'                 => $this->phone,
            'email'                 => $this->email,
            'address'               => $this->address,
            'longitude'             => $this->longitude,
            'latitude'              => $this->latitude,
            'role'                  => $this->role,
            'fcm_token'             => $this->fcm_token,
            'profile_picture'       => $this->profile_picture,
            'created_at'            => $this->created_at,
            'updated_at'            => $this->updated_at,
            'provider'              => ProviderResource::make($this->provider),
        ];
    }
}
