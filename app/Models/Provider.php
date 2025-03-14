<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cys()
    {
        return $this->hasMany(CyPeriodicProvider::class);
    }

    public function provider_notifications()
    {
        return $this->hasMany(ProviderNotification::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function pickUpTruck()
    {
        return $this->belongsTo(PickUpTruck::class);
    }

    public function ratings()
{
    return $this->hasManyThrough(OrderRate::class, User::class, 'id', 'provider_id', 'user_id', 'id');
}




}
