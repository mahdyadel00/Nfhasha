<?php
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DistrictTranslationSeeder extends Seeder
{
    public function run()
    {
        DB::table('district_translations')->insert([['district_id' => 1, 'locale' => 'en', 'name' => 'District 1'], ['district_id' => 1, 'locale' => 'ar', 'name' => 'الحي 1']]);
    }
}
