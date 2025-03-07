<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'user_id',
        'express_service_id',
        'user_vehicle_id',
        'city_id',
        'cy_periodic_id',
        'pick_up_truck_id',
        'type',
        'status',
        'payment_method',
        'from_lat',
        'from_long',
        'to_lat',
        'to_long',
        'details',
        'total_cost',
        'position',
        'address',
        'address_to',
        'reason',
        'images',
    ];

    public function scopeNearby(Builder $query, $latitude, $longitude, $distance = 50)
    {
        $haversine = "(6371 * acos(cos(radians(?)) * cos(radians(from_lat))
        * cos(radians(from_long) - radians(?)) + sin(radians(?)) * sin(radians(from_lat))))";

        return $query->select('*')
            ->selectRaw("{$haversine} AS distance", [$latitude, $longitude, $latitude])
            ->having('distance', '<', $distance) // المسافة بالكيلومترات
            ->orderBy('distance');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function userVehicle()
    {
        return $this->belongsTo(UserVehicle::class , 'user_vehicle_id');
    }

    public function expressService()
    {
        return $this->belongsTo(ExpressService::class);
    }


    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function cyPeriodic()
    {
        return $this->belongsTo(CyPeriodic::class);
    }

    public function pickUpTruck()
    {
        return $this->belongsTo(PickUpTruck::class);
    }


    public function provider()
    {
        return $this->belongsTo(User::class , 'provider_id');
    }


    public function rates()
    {
        return $this->hasMany(OrderRate::class);
    }

    public function offers()
    {
        return $this->hasMany(OrderOffer::class);
    }

    public function tracking()
    {
        return $this->hasOne(OrderTracking::class);
    }

    public function chat()
    {
        return $this->hasOne(Chat::class);
    }


}
