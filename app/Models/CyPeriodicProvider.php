<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CyPeriodicProvider extends Model
{
    use HasFactory;

    protected $guarded = [];


    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function periodic()
    {
        return $this->belongsTo(CyPeriodic::class);
    }
}
