<?php

namespace App\Models;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CarReservation;

class ExpressService extends Model implements \Astrotomic\Translatable\Contracts\Translatable
{
    use HasFactory, Translatable;

    public $translatedAttributes = ['name', 'terms_condition'];

    protected $fillable = ['is_active', 'type', 'price', 'vat'];

    protected $with = ['periodicInspections']; // تحميل العلاقة افتراضيًا

    public function punctureServices()
    {
        return $this->hasMany(PunctureService::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

     public function carReservations()
    {
        return $this->hasMany(CarReservation::class);
    }

    public function comprehensiveInspections()
    {
        return $this->hasOne(ComprehensiveInspections::class);
    }

    public function maintenance()
    {
        return $this->hasOne(Maintenance::class);
    }

    public function periodicInspections()
    {
        return $this->hasMany(PeriodicInspections::class, 'express_service_id', 'id');
    }

    public function ordersCount()
    {
        return $this->hasMany(Order::class)->count();
    }

    public function serviceOffers()
    {
        return $this->hasMany(ServiceOffer::class);
    }

    public function typePeriodicInspections()
    {
        return $this->hasMany(TypePeriodicInspections::class);
    }
}
