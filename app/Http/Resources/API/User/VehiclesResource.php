<?php

namespace App\Http\Resources\API\User;

use App\Http\Resources\API\VehiclesInfo\BrandsResource;
use App\Http\Resources\API\VehiclesInfo\ModelsResource;
use App\Http\Resources\API\VehiclesInfo\TypesResource;
use App\Http\Resources\API\VehiclesInfo\YearsResource;
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
            'vehicle_type'                      => TypesResource::make($this->type),
            'vehicle_model'                     => ModelsResource::make($this->model),
            'vehicle_manufacture_year'          => YearsResource::make($this->manufactureYear),
            'vehicle_brand'                     => BrandsResource::make($this->brand),
            'checkup_date'                      => $this->checkup_date,
            'images'                            => $this->images->pluck('full_path'),
        ];
    }
}
