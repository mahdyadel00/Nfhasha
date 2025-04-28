<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodicInspections extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'user_id', 'user_vehicle_id', 'city_id', 'express_service_id', 'pick_up_truck_id', 'provider_id', 'inspection_type_id', 'address', 'latitude', 'longitude', 'status', 'inspection_reject_image', 'inspection_reject_reason'];

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

    public function expressService()
    {
        return $this->belongsTo(ExpressService::class);
    }

    public function pickUpTruck()
    {
        return $this->belongsTo(PickUpTruck::class);
    }

    public function provider()
    {
        return $this->belongsTo(User::class);
    }

    public function inspectionType()
    {
        return $this->belongsTo(TypePeriodicInspections::class, 'inspection_type_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
