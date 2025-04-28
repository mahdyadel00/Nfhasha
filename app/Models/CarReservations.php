<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarReservations extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider_id',
        'express_service_id',
        'order_id',
        'city_id',
        'user_vehicle_id',
        'inspection_side',
        'date',
        'time',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id'); 
    }

    public function expressService()
    {
        return $this->belongsTo(ExpressService::class, 'express_service_id');
    }
}
