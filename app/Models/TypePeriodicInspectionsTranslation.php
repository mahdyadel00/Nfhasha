<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypePeriodicInspectionsTranslation extends Model
{
    use HasFactory;


    protected $fillable = [
        'name',
        'locale',
        'type_periodic_inspection_id',
    ];
}
