<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarReservations extends Model
{
    use HasFactory;

    protected $fillable = [
        'city_id',
        'user_id',
        'express_service_id',
        'vehicle_id',
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
        return $this->belongsTo(UserVehicle::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function expressService()
    {
        return $this->belongsTo(ExpressService::class);
    }

}
