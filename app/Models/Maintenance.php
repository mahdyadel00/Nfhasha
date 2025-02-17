<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'user_vehicle_id', 'pick_up_truck_id' , 'express_service_id' , 'provider_id' , 'maintenance_type', 'maintenance_description', 'address', 'latitude', 'longitude', 'is_working', 'image', 'note'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function userVehicle()
    {
        return $this->belongsTo(UserVehicle::class);
    }

    public function pickUpTruck()
    {
        return $this->belongsTo(PickUpTruck::class);
    }

    public function provider()
    {
        return $this->belongsTo(User::class , 'provider_id');
    }

    public function expressService()
    {
        return $this->belongsTo(ExpressService::class);
    }
}
