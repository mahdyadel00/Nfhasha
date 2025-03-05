<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceMaintenanceTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_maintenance_id',
        'name',
        'description',
        'locale',
    ];


    public function serviceMaintenance()
    {
        return $this->belongsTo(ServiceMaintenance::class);
    }
}