<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class Provider extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cys()
    {
        return $this->hasMany(CyPeriodicProvider::class);
    }

}
