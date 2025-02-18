<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PickUpTruck extends Model implements TranslatableContract
{
    use HasFactory, Translatable;

    public $translatedAttributes = ['name'];
    protected $guarded = [];

    public function maintenances()
    {
        return $this->hasMany(Maintenance::class);
    }

    public function comprehensiveInspections()
    {
        return $this->hasMany(ComprehensiveInspections::class);
    }

    public function periodicInspections()
    {
        return $this->hasMany(PeriodicInspections::class);
    }

    public function providers()
    {
        return $this->hasMany(Provider::class);
    }
}
