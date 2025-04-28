<?php

namespace App\Http\Resources\API\User;

use Illuminate\Http\Resources\Json\JsonResource;

class ExpressServiceResource extends JsonResource
{
    protected $orderId;

    public function __construct($resource, $orderId = null)
    {
        parent::__construct($resource);
        $this->orderId = $orderId;
    }

    public function toArray($request)
    {
        if (!$this->resource) {
            return [];
        }

        $this->loadMissing('carReservations', 'punctureServices');

        $latestPunctureService = $this->punctureServices()->latest('created_at')->first();


        $getUserReservation = function ($relation) use ($request) {
            $userOrProvider = $request->user();
            if (!$userOrProvider || !$this->id || !$this->orderId) {
                return null;
            }

            $field = $userOrProvider->role === 'provider' ? 'provider_id' : 'user_id';

            return $this->$relation()
                ->where($field, $userOrProvider->id)
                ->where('express_service_id', $this->id)
                ->where('order_id', $this->orderId)
                ->first();
        };

        return [
            'id'                        => $this->id,
            'is_active'                 => (bool) $this->is_active,
            'type'                      => $this->type,
            'price'                     => $this->price,
            'provider'                  => $this->provider ? [
                'id'                    => $this->provider->id ?? null,
                'name'                  => $this->provider->name ?? null,
            ] : null,
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
