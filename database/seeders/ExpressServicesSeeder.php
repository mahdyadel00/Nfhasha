<?php

namespace Database\Seeders;

use App\Models\ExpressService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExpressServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $express_service = ExpressService::create([
            'is_active' => true,
            'type'      => 'open_locks',
        ]);

        $express_service->translations()->create([
            'locale'        => 'en',
            'name'          => 'Express Service',
        ]);

        $express_service->translations()->create([
            'locale'        => 'ar',
            'name'          => 'خدمة سريعة',
        ]);
    }
}
