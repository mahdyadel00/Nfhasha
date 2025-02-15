<?php

namespace App\Models;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model implements \Astrotomic\Translatable\Contracts\Translatable
{
    use HasFactory , Translatable;

    public $translatedAttributes = ['name' , 'description'];

    protected $guarded = [];

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeMainServices($query)
    {
        return $query->where('parent_id', null);
    }
}
