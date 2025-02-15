<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarReservations extends Model
{
    use HasFactory;

    protected $fillable = [
        'city_id',
        'service_id',
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
        return $this->belongsTo(UserVehicle::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
