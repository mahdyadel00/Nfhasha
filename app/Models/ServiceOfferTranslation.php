<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceOfferTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_offer_id',
        'locale',
        'name',
        'description',
    ];


    public function serviceOffer()
    {
        return $this->belongsTo(ServiceOffer::class);
    }
}
