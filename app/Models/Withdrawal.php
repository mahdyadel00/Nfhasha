<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    protected $fillable = ['user_id', 'bank_name', 'account_number', 'iban', 'status', 'amount'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}