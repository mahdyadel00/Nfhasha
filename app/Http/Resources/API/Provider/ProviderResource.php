<?php

namespace App\Http\Resources\API\Provider;

use App\Http\Resources\API\{CityResource, DistrictsResource, PickupTrucksResource, RateResource};
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->resource->loadMissing('ratings');
        return [
            'id'                    => $this->id,
            'name'                  => $this->name, // من جدول users
            'image'                 => $this->image ? asset('storage/' . $this->image) : null, // من جدول users
            'latitude'              => $this->latitude, // من جدول users
            'longitude'             => $this->longitude, // من جدول users
            'email'                 => $this->email, // من جدول users (اختياري، لو عايز)
            'phone'                 => $this->phone, // من جدول users (اختياري، لو موجود)
            'type'                  => $this->type,
            'mechanical'            => $this->mechanical,
            'plumber'               => $this->plumber,
            'electrical'            => $this->electrical,
            'puncture'              => $this->puncture,
            'battery'               => $this->battery,
            'fuel'                  => $this->fuel,
            'tow_truck'             => $this->tow_truck,
            'pickup'                => $this->pickup,
            'open_locks'            => $this->open_locks,
            'periodic_inspections'  => $this->periodic_inspections,
            'comprehensive_inspections' => $this->comprehensive_inspections,
            'maintenance'           => $this->maintenance,
            'car_reservations'      => $this->car_reservations,
            'available_from'        => $this->available_from,
            'available_to'          => $this->available_to,
            'home_service'          => $this->home_service,
            'commercial_register'   => $this->commercial_register ? asset('storage/' . $this->commercial_register) : null,
            'owner_identity'        => $this->owner_identity ? asset('storage/' . $this->owner_identity) : null,
            'general_license'       => $this->general_license ? asset('storage/' . $this->general_license) : null,
            'municipal_license'     => $this->municipal_license ? asset('storage/' . $this->municipal_license) : null,
            'is_active'             => $this->is_active,
            'wallet_balance'        => $this->wallet_balance ?? 0, // من جدول users
            'rating_rate'           => $this->ratings()->avg('rate') ?? 0, // من جدول ratings المرتبط بـ users
            'created_at'            => $this->created_at,
            'updated_at'            => $this->updated_at,
            'city'                  => CityResource::make($this->city),
            'district'              => DistrictsResource::make($this->district),
            'pick_up_truck'         => PickupTrucksResource::make($this->pickUpTruck),
            'rate'                  => RateResource::make(optional($this->ratings)->firstWhere('provider_id', auth()->id())),
            'ratings_count'         => $this->ratings_count,
            'completed_orders_count' => $this->completed_orders_count,
        ];
    }
}