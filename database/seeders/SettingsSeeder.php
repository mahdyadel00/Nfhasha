<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        settings(['terms_and_conditions_ar' => 'هذه الشروط والأحكام باللغة العربية']);
        settings(['terms_and_conditions_en' => 'These are the terms and conditions in English']);
        settings(['privacy_policy_ar' => 'هذه سياسة الخصوصية باللغة العربية']);
        settings(['privacy_policy_en' => 'This is the privacy policy in English']);
        settings(['about_us_ar' => 'هذا هو نبذة عنا باللغة العربية']);
        settings(['about_us_en' => 'This is an about us in English']);

    }
}
