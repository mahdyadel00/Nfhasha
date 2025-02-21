<?php

namespace App\Models;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceOfferTranslation extends Model
{
    use HasFactory , Translatable;
    public $translatedAttributes = ['name' , 'description'];


    protected $fillable = [
        'service_offer_id',
        'locale',
        'title',
        'description',
    ];


    public function serviceOffer()
    {
        return $this->belongsTo(ServiceOffer::class);
    }
}
