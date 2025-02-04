<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VehicleBrandTranslationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicleBrands = \App\Models\VehicleBrand::all();
        $languages = ['en', 'ar'];
        $faker = \Faker\Factory::create();
        $vehicleBrandTranslations = [];
        foreach ($vehicleBrands as $vehicleBrand) {
            foreach ($languages as $language) {
                $vehicleBrandTranslations[] = [
                    'vehicle_brand_id' => $vehicleBrand->id,
                    'locale' => $language,
                    'title' => $faker->name,
                ];
            }
        }
        \App\Models\VehicleBrandTranslation::insert($vehicleBrandTranslations);
    }
}
