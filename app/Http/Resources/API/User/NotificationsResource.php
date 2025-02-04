<?php

namespace App\Http\Resources\API\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'message' => $this->data['message'],
            'description' => $this->data['description'],
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'created_at_humanly' => $this->created_at->diffForHumans(),
            'is_read' => $this->read_at ? true : false,
            'read_at' => $this->when($this->read_at, function () {
                return $this->read_at->format('Y-m-d H:i:s');
            }),
            'read_at_humanly' => $this->when($this->read_at, function () {
                return $this->read_at->diffForHumans();
            }),
        ];
    }
}
