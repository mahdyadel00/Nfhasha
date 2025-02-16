<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComprehensiveInspections extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'user_vehicle_id' , 'express_service_id' , 'city_id', 'pick_up_truck_id', 'provider_id', 'date', 'address', 'latitude', 'longitude'];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function userVehicle()
    {
        return $this->belongsTo(UserVehicle::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function pickUpTruck()
    {
        return $this->belongsTo(PickUpTruck::class);
    }

    public function provider()
    {
        return $this->belongsTo(User::class);
    }

    public function expressService()
    {
        return $this->belongsTo(ExpressService::class);
    }
}
