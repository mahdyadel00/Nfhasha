<?php

namespace App\Models;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model implements \Astrotomic\Translatable\Contracts\Translatable
{
    use HasFactory , Translatable;
    public $translatedAttributes = ['name'];

    protected $fillable = ['city_id', 'is_active'];

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
