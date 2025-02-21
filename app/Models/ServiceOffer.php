<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'price',
        'duration',
        'image',
        'status',
    ];


    public function service()
    {
        return $this->belongsTo(ExpressService::class);
    }


    public function translations()
    {
        return $this->hasMany(ServiceOfferTranslation::class);
    }
}
