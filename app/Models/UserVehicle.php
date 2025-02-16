<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserVehicle extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function type()
    {
        return $this->belongsTo(VehicleType::class, 'vehicle_type_id');
    }

    public function model()
    {
        return $this->belongsTo(VehicleModel::class, 'vehicle_model_id');
    }

    public function manufactureYear()
    {
        return $this->belongsTo(VehicleManufactureYear::class, 'vehicle_manufacture_year_id');
    }

    public function brand()
    {
        return $this->belongsTo(VehicleBrand::class, 'vehicle_brand_id');
    }

    public function images()
    {
        return $this->hasMany(UserVehicleImage::class);
    }

    public function punctureServices()
    {
        return $this->hasMany(PunctureService::class);
    }

    public function carReservations()
    {
        return $this->hasMany(CarReservations::class);
    }

    public function maintenances()
    {
        return $this->hasMany(Maintenance::class);
    }
}
