<?php

namespace App\Http\Resources\API\User;

use Illuminate\Http\Resources\Json\JsonResource;


class ExpressServiceResource extends JsonResource
{
    protected $orderId; // ✅ تخزين `order_id`

    public function __construct($resource, $orderId = null)
    {
        parent::__construct($resource);
        $this->orderId = $orderId; // ✅ حفظ `order_id`
    }
    public function toArray($request)
    {
        $this->loadMissing('carReservations'); // تحميل العلاقة إن لم تكن مُحمّلة

        $latestPunctureService = $this->punctureServices()->latest('created_at')->first();

        $getUserReservation = function ($relation) use ($request) {
            return $this->$relation()
                ->where('user_id', $request->user()->id)
                ->where('express_service_id', $this->id)
                ->where('order_id', $this->orderId)
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
            'note'                      => $this->note,
            'terms_condition'           => $this->terms_condition ?? null,
            'battery_image'             => $latestPunctureService ? asset('storage/express_services/' . basename($latestPunctureService->battery_image)) : null,
            'type_battery'              => $latestPunctureService?->type_battery,
            'car_reservation'           => CarReservationsResource::make($getUserReservation('carReservations')),
            'comprehensiveInspections'  => ComprehensiveInspectionsResource::make($getUserReservation('comprehensiveInspections')),
            'maintenance'               => MaintenanceResource::make($getUserReservation('maintenance')),
            'periodicInspections'       => PeriodicInspectionsResource::make($getUserReservation('periodicInspections')),
        ];
    }
}
