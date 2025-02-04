<?php

namespace App\Models;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleType extends Model implements \Astrotomic\Translatable\Contracts\Translatable
{
    use HasFactory , Translatable;

    public $translatedAttributes = ['title'];

    protected $guarded = [];

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }


    public function vehicles()
    {
        return $this->hasMany(VehicleBrand::class);
    }
    public function vehicle_models()
    {
        return $this->hasMany(VehicleModel::class);
    }
}
