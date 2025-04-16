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

            // تحميل العلاقات المطلوبة
            $this->loadMissing(['carReservations', 'comprehensiveInspections', 'maintenance', 'periodicInspections']);

            $latestPunctureService = $this->punctureServices()->latest('created_at')->first();

            $getUserReservation = function ($relation) use ($request) {
                return $this->$relation()
                    ->where('user_id', $request->user()->id)
                    ->where('express_service_id', $this->id)
                    ->where('order_id', $this->orderId)
                    ->first();
            };

            $data = [
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
                'car_reservation'           => $this->type === 'car_reservation' ? CarReservationsResource::make($getUserReservation('carReservations')) : null,
                'comprehensiveInspections'  => $this->type === 'comprehensive_inspections' ? ComprehensiveInspectionsResource::make($getUserReservation('comprehensiveInspections')) : null,
                'maintenance'               => $this->type === 'maintenance' ? MaintenanceResource::make($getUserReservation('maintenance')) : null,
                'periodicInspections'       => $this->type === 'periodic_inspections' ? PeriodicInspectionsResource::make($getUserReservation('periodicInspections')) : null,
            ];

            return $data;
        } catch (\Exception $e) {
            \Log::error('Error in ExpressServiceResource: ' . $e->getMessage());
            return [
                'error' => 'Error processing express service data'
            ];
        }
    }
}