<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Translatable;

class City extends Model
{
    use HasFactory, Translatable;

    public $translatedAttributes = ['name']; // Specify translatable attributes

    protected $fillable = ['is_active']; // Add other fillable attributes as needed

    public function districts()
    {
        return $this->hasMany(District::class);
    }
}
