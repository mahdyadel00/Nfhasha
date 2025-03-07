<?php

namespace App\Http\Resources\API\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpressServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $latestPunctureService = $this->punctureServices()->latest('created_at')->first();

        $carReservation = $this->carReservations()
            ->where('user_id', $request->user()->id)
            ->where('express_service_id', $this->id)
            ->first();

        return [
            'id'                        => $this->id,
            'is_active'                 => (bool) $this->is_active,
            'type'                      => $this->type,
            'price'                     => $this->price,
            'vat'                       => $this->vat,
            'created_at'                => $this->created_at,
            'updated_at'                => $this->updated_at,
            'name'                      => $this->name,
            'terms_condition'           => $this->terms_condition ?? null,
            'battery_image'             => $latestPunctureService ? asset('storage/' . $latestPunctureService->battery_image) : null,
            'type_battery'              => $latestPunctureService?->type_battery,
            'car_reservation'           => $carReservation ? new CarReservationsResource($carReservation) : null,
            'comprehensiveInspections'  => $this->whenLoaded('comprehensiveInspections', fn() => new ComprehensiveInspectionsResource($this->comprehensiveInspections)),
            'maintenance'               => $this->whenLoaded('maintenance', fn() => new MaintenanceResource($this->maintenance)),
            'periodicInspections'       => $this->whenLoaded('periodicInspections', fn() => new PeriodicInspectionsResource($this->periodicInspections)),
        ];
    }
}
