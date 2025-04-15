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
        try {
            // التحقق من وجود المورد
            if (!$this->resource) {
                return [
                    'error' => 'Express service not found'
                ];
            }

            $this->loadMissing('carReservations');

            $latestPunctureService = $this->punctureServices()->latest('created_at')->first();

            $getUserReservation = function ($relation) use ($request) {
                if (!$this->$relation) {
                    return null;
                }
                return $this->$relation()
                    ->where('user_id', $request->user()->id)
                    ->where('express_service_id', $this->id)
                    ->where('order_id', $this->orderId)
                    ->first();
            };

            return [
                'id'                        => $this->id ?? null,
                'is_active'                 => (bool) ($this->is_active ?? false),
                'type'                      => $this->type ?? null,
                'price'                     => $this->price ?? null,
                'vat'                       => $this->vat ?? null,
                'created_at'                => $this->created_at ?? null,
                'updated_at'                => $this->updated_at ?? null,
                'name'                      => $this->name ?? null,
                'note'                      => $this->note ?? null,
                'terms_condition'           => $this->terms_condition ?? null,
                'battery_image'             => $latestPunctureService && $latestPunctureService->battery_image ?
                    asset('storage/express_services/' . basename($latestPunctureService->battery_image)) : null,
                'type_battery'              => $latestPunctureService?->type_battery ?? null,
                'car_reservation'           => $this->when($this->carReservations, function() use ($getUserReservation) {
                    return CarReservationsResource::make($getUserReservation('carReservations'));
                }),
                'comprehensiveInspections'  => $this->when($this->comprehensiveInspections, function() use ($getUserReservation) {
                    return ComprehensiveInspectionsResource::make($getUserReservation('comprehensiveInspections'));
                }),
                'maintenance'               => $this->when($this->maintenance, function() use ($getUserReservation) {
                    return MaintenanceResource::make($getUserReservation('maintenance'));
                }),
                'periodicInspections'       => $this->when($this->periodicInspections, function() use ($getUserReservation) {
                    return PeriodicInspectionsResource::make($getUserReservation('periodicInspections'));
                }),
            ];
        } catch (\Exception $e) {
            \Log::error('Error in ExpressServiceResource: ' . $e->getMessage());
            return [
                'error' => 'Error processing express service data'
            ];
        }
    }
}
