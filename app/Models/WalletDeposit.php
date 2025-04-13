<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletDeposit extends Model
{
    protected $fillable = ['user_id', 'amount', 'payment_method', 'status', 'checkout_id'];
}
