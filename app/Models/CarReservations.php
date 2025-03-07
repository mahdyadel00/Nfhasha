<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarReservations extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'city_id',
        'user_id',
        'express_service_id',
        'user_vehicle_id',
        'inspection_side',
        'date',
        'time',
    ];


    public function city()
    {
        return $this->belongsTo(City::class);
    }


    public function userVehicle()
    {
        return $this->belongsTo(UserVehicle::class , 'user_vehicle_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function expressService()
    {
        return $this->belongsTo(ExpressService::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

}
