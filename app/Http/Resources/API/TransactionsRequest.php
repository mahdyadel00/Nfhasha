<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionsRequest extends JsonResource
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
            'amount'                        => $this->amount,
            'type'                          => $this->type,
            'notes'                         => $this->notes,
            'created_at'                    => $this->created_at->format('Y-m-d H:i:s'),
            'created_at_humanly'            => $this->created_at->diffForHumans()
        ];
    }
}
