<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarReservation extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'express_service_id', 'reservation_date', 'status'];

    public function expressService()
    {
        return $this->belongsTo(ExpressService::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
