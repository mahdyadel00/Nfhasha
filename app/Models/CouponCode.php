<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponCode extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function isValid(): bool
    {
        return $this->used < $this->max_uses && ($this->expires_at === null || $this->expires_at->isFuture());
    }

}
