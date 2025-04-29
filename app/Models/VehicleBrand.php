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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deleteTranslations(array|string|null $locales = null): void
    {
        $this->translations()->whereIn('locale', (array) $locales)->delete();
    }
}
