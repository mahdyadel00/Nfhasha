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

        // دالة لجلب الحجز الخاص بالمستخدم للخدمة المحددة
        $getUserReservation = function ($relation) use ($request) {
            return $this->$relation()
                ->where('user_id', $request->user()->id)
                ->where('express_service_id', $this->id)
                ->first();
        };

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
            'car_reservation'           => CarReservationsResource::make($getUserReservation('carReservations')),
            'comprehensiveInspections'  => ComprehensiveInspectionsResource::make($getUserReservation('comprehensiveInspections')),
            'maintenance'               => MaintenanceResource::make($getUserReservation('maintenance')),
            'periodicInspections'       => PeriodicInspectionsResource::make($getUserReservation('periodicInspections')),
        ];
    }
}
