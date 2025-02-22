<?php

namespace App\Models;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypePeriodicInspections extends Model
{
    use HasFactory , Translatable;

    public $translatedAttributes = ['name'];
    protected $fillable = [
        'status',
        'service_id',
    ];

    public function translations()
    {
        return $this->hasMany(TypePeriodicInspectionsTranslation::class , 'type_id');
    }


    public function service()
    {
        return $this->belongsTo(ExpressService::class, 'service_id')->where('type', 'periodic_inspections');
    }

    public function inspections()
    {
        return $this->hasMany(PeriodicInspections::class , 'inspection_type_id');
    }
}
