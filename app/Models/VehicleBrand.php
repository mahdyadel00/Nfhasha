<?php

namespace App\Models;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleBrand extends Model implements \Astrotomic\Translatable\Contracts\Translatable
{
    use HasFactory, Translatable;
    public $translatedAttributes = ['title'];

    protected $guarded = [];

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class);
    }

}
