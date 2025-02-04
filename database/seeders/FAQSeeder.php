<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FAQSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $FAQs = \App\Models\FAQ::all();
        $languages = ['en', 'ar'];
        $faker = \Faker\Factory::create();
        $FAQTranslations = [];
        foreach ($FAQs as $vehicleType) {
            foreach ($languages as $language) {
                $FAQTranslations[] = [
                    'f_a_q_id' => $vehicleType->id,
                    'locale' => $language,
                    'question' => $faker->sentence,
                    'answer' => $faker->paragraph,
                ];
            }
        }
        \App\Models\FAQTranslation::insert($FAQTranslations);
    }
}
