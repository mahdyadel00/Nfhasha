<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PunctureService extends Model
{
    use HasFactory;

    protected $fillable = [
        'express_service_id',
        'user_id',
        'user_vehicle_id',
        'from_latitude',
        'from_longitude',
        'to_latitude',
        'to_longitude',
        'type_battery',
        'battery_image',
        'notes',
        'amount',
        'status',
        'reason',
    ];


    public function expressService()
    {
        return $this->belongsTo(ExpressService::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function userVehicle()
    {
        return $this->belongsTo(UserVehicle::class);
    }
}
