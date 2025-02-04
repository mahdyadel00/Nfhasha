<?php

namespace App\Models;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model implements \Astrotomic\Translatable\Contracts\Translatable
{
    use HasFactory , Translatable;
    public $translatedAttributes = ['name'];

    protected $guarded = [];

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }


    public function districts()
    {
        return $this->hasMany(District::class);
    }

}
