<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Translatable;

class City extends Model
{
    use HasFactory, Translatable;

    public $translatedAttributes = ['name'];

    protected $fillable = ['is_active']; 

    public function districts()
    {
        return $this->hasMany(District::class);
    }
}
