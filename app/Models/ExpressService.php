<?php

namespace App\Models;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpressService extends Model implements \Astrotomic\Translatable\Contracts\Translatable
{
    use HasFactory , Translatable;

    public $translatedAttributes = ['name'];

    protected $fillable = ['is_active' , 'type' ,'price' , 'vat'];

    public function punctureServices()
    {
        return $this->hasMany(PunctureService::class);
    }

}
