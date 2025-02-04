<?php

namespace App\Models;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SplashScreen extends Model implements \Astrotomic\Translatable\Contracts\Translatable
{
    use HasFactory , Translatable;

    public $translatedAttributes = ['title' , 'description'];
    protected $appends = ['image_url'];
    protected $guarded = [];

    public function scopeActive($query)
    {
        return $query->where('is_active' , true);
    }


    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    //Image URL
    public function getImageUrlAttribute()
    {
        if ($this->image && file_exists(storage_path('app/public/' . $this->image))) {
            return asset('storage/' . $this->image);
        } else {
            return 'https://ui-avatars.com/api/?name=' . urlencode($this->title) . '&color=7F9CF5&background=EBF4FF';
        }
    }

}
