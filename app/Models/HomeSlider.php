<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeSlider extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function getCoverUrlAttribute()
    {
        if ($this->cover && file_exists(storage_path('app/public/' . $this->cover))) {
            return asset('storage/' . $this->cover);
        } else {
            return 'https://ui-avatars.com/api/?name=DefaultCover&color=7F9CF5&background=EBF4FF';
        }
    }
}
