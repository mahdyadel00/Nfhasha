<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VehicleTypeTranslationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicleTypes = \App\Models\VehicleType::all();
        $languages = ['en', 'ar'];
        $faker = \Faker\Factory::create();
        $vehicleTypeTranslations = [];
        foreach ($vehicleTypes as $vehicleType) {
            foreach ($languages as $language) {
                $vehicleTypeTranslations[] = [
                    'vehicle_type_id' => $vehicleType->id,
                    'locale' => $language,
                    'title' => $faker->name,
                ];
            }
        }
        \App\Models\VehicleTypeTranslation::insert($vehicleTypeTranslations);
    }
}
