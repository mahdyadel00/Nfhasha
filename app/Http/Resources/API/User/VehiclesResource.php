<?php

namespace App\Http\Resources\API\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehiclesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);

        return
        [
            'id'                                => $this->id,
            'letters_ar'                        => $this->letters_ar,
            'letters_en'                        => $this->letters_en,
            'numbers_ar'                        => $this->numbers_ar,
            'numbers_en'                        => $this->numbers_en,
            'vehicle_type'                      => $this->type?->title,
            'vehicle_type_id'                   => $this->type?->id,
            'vehicle_model'                     => $this->model?->title,
            'vehicle_model_id'                  => $this->model?->id,
            'vehicle_manufacture_year'          => $this->manufactureYear?->title,
            'vehicle_manufacture_year_id'       => $this->manufactureYear?->id,
            'vehicle_brand'                     => $this->brand?->title,
            'vehicle_brand_id'                  => $this->brand?->id,
            'checkup_date'                      => $this->checkup_date,
            'images'                            => $this->images->pluck('full_path'),
        ];
    }
}
