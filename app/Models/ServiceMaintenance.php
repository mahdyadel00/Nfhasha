<?php

namespace App\Models;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceMaintenance extends Model
{
    use HasFactory , Translatable;

    public $translatedAttributes = ['name' , 'description'];

    protected $fillable = [
        'status',
        'service_id',
    ];

    public function translations()
    {
        return $this->hasMany(ServiceMaintenanceTranslation::class);
    }

    public function service()
    {
        return $this->belongsTo(ExpressService::class, 'service_id')->where('type', 'maintenance');
    }


}
