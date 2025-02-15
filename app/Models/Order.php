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
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function userVehicle()
    {
        return $this->belongsTo(UserVehicle::class , 'user_vehicle_id');
    }





}
