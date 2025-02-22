<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

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


}
