<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpressServiceTranslation extends Model
{
    use HasFactory;

    protected $fillable = ['express_service_id', 'name', 'locale' , 'terms_condition'];

}
