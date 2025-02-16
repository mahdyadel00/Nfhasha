<?php

namespace App\Models;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpressService extends Model implements \Astrotomic\Translatable\Contracts\Translatable
{
    use HasFactory , Translatable;

    public $translatedAttributes = ['name'];

    protected $fillable = ['is_active' , 'type' ,'price' , 'vat'];

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
        return $this->hasMany(CarReservations::class);
    }

    public function comprehensiveInspections()
    {
        return $this->hasMany(ComprehensiveInspections::class);
    }

}
