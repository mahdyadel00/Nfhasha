<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserVehicleImage extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function getFullPathAttribute()
    {
        return asset('storage/' . $this->path);
    }
}
