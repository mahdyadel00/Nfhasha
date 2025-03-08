<?php

namespace App\Http\Resources\API\User;

use App\Http\Resources\API\Provider\PunctureServiceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class ExpressServiceResource extends JsonResource
{
    protected $orderId; // ✅ تخزين `order_id`

    public function __construct($resource, $orderId = null)
    {
        parent::__construct($resource);
        $this->orderId = $orderId; // ✅ حفظ `order_id`
    }

    public function toArray(Request $request): array
    {
        $latestPunctureService = $this->punctureServices()->latest('created_at')->first();

        // ✅ تعديل دالة `getUserReservation` لاستخدام `orderId` الممرر من `OrderResource`
        $getUserReservation = function ($relation) use ($request) {
            return $this->$relation()
                ->where('user_id', $request->user()->id)
                ->where('express_service_id', $this->id)
                ->where('order_id', $this->orderId) // ✅ استخدام `orderId` الممرر من `OrderResource`
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
            // 'note'                      => $services->isNotEmpty() ? $services->first()->note : null,
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
