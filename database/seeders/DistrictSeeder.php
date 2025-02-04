<?php

namespace Database\Seeders;

use App\Models\District;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $district = District::create([
            'city_id'       => 1,
            'name'          => 'District 1',
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        $district->translateOrNew('ar')->name = 'الحى الاول';
        $district->translateOrNew('en')->name = 'District 1';

        $district->save();

    }
}
