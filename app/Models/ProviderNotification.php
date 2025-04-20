<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'user_id',
        'order_id',
        'message',
        'service_type',
    ];


    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}