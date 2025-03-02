<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderRate extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'user_id', 'provider_id' , 'rate', 'comment'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function provider()
{
    return $this->belongsTo(Provider::class, 'provider_id');
}

}
