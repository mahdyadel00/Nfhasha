<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Translatable;

class District extends Model
{
    use HasFactory, Translatable;

    public $translatedAttributes = ['name']; // Specify translatable attributes

    protected $fillable = ['city_id', 'is_active']; // Add other fillable attributes as needed

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
