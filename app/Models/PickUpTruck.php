<?php

namespace App\Models;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PickUpTruck extends Model implements \Astrotomic\Translatable\Contracts\Translatable
{
    use HasFactory, Translatable;

    public $translatedAttributes = ['name'];
    protected $guarded = [];

    public function maintenances()
    {
        return $this->hasMany(Maintenance::class);
    }
}
