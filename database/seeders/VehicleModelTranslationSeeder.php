<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VehicleModelTranslationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicleModels = \App\Models\VehicleModel::all();
        $languages = ['en', 'ar'];
        $faker = \Faker\Factory::create();
        $vehicleModelTranslations = [];
        foreach ($vehicleModels as $vehicleModel) {
            foreach ($languages as $language) {
                $vehicleModelTranslations[] = [
                    'vehicle_model_id' => $vehicleModel->id,
                    'locale' => $language,
                    'title' => $faker->name,
                ];
            }
        }
        \App\Models\VehicleModelTranslation::insert($vehicleModelTranslations);
    }
}
