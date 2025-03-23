<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProvider extends Model
{
    use HasFactory;

    protected $table = 'order_providers'; // تحديد اسم الجدول

    protected $fillable = [
        'order_id',
        'provider_id',
        'status',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }
}
