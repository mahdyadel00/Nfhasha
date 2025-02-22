<?php

namespace App\Models;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceOffer extends Model
{
    use HasFactory , Translatable;

    public $translatedAttributes = ['name' , 'description'];
    protected $fillable = [
        'express_service_id',
        'price',
        'code',
        'duration',
        'image',
        'status',
    ];


    public function service()
    {
        return $this->belongsTo(ExpressService::class , 'express_service_id');
    }


    public function translations()
    {
        return $this->hasMany(ServiceOfferTranslation::class);
    }
}
