<?php

namespace App\Models;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FAQ extends Model
{
    use HasFactory , Translatable;

    public $translatedAttributes = ['question' , 'answer'];
    protected $guarded = [];

    public function scopeActive($query)
    {
        return $query->where('is_active' , 1);
    }
}
