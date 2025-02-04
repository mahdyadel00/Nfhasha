<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $city = City::create([
            'is_active'         => true,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);


        $city->translateOrNew('en')->name = 'Cairo'; 
        $city->translateOrNew('ar')->name = 'القاهرة';

        $city->save();
    }
}
