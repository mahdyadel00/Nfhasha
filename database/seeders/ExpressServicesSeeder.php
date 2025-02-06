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
            'price'     => 50,
        ]);

        $express_service->translations()->create([
            'locale'        => 'en',
            'name'          => 'Open Locks',
        ]);

        $express_service->translations()->create([
            'locale'        => 'ar',
            'name'          => 'فتح الأقفال',
        ]);

        $express_service = ExpressService::create([
            'is_active' => true,
            'type'      => 'battery',
            'price'     => 100,
        ]);

        $express_service->translations()->create([
            'locale'        => 'en',
            'name'          => 'Battery Subscription',
        ]);

        $express_service->translations()->create([
            'locale'        => 'ar',
            'name'          => 'اشتراك بطارية',
        ]);

        $express_service = ExpressService::create([
            'is_active' => true,
            'type'      => 'battery',
            'price'     => 150,
        ]);

        $express_service->translations()->create([
            'locale'        => 'en',
            'name'          => 'Battery Replacement',
        ]);

        $express_service->translations()->create([
            'locale'        => 'ar',
            'name'          => 'تبديل بطارية',
        ]);

        $express_service = ExpressService::create([
            'is_active' => true,
            'type'      => 'fuel',
            'price'     => 50,
        ]);

        $express_service->translations()->create([
            'locale'        => 'en',
            'name'          => 'Gasoline 95',
        ]);

        $express_service->translations()->create([
            'locale'        => 'ar',
            'name'          => 'بنزين 95',
        ]);

        $express_service = ExpressService::create([
            'is_active' => true,
            'type'      => 'fuel',
            'price'     => 100,
        ]);

        $express_service->translations()->create([
            'locale'        => 'en',
            'name'          => 'Gasoline 98',
        ]);

        $express_service->translations()->create([
            'locale'        => 'ar',
            'name'          => 'بنزين 98',
        ]);

        $express_service = ExpressService::create([
            'is_active' => true,
            'type'      => 'puncture',
            'price'     => 10,
        ]);

        $express_service->translations()->create([
            'locale'        => 'en',
            'name'          => 'Tire Repair',
        ]);

        $express_service->translations()->create([
            'locale'        => 'ar',
            'name'          => 'تغيير الاطار الاحطياطى',
        ]);

        $express_service = ExpressService::create([
            'is_active' => true,
            'type'      => 'puncture',
            'price'     => 10,
        ]);

        $express_service->translations()->create([
            'locale'        => 'en',
            'name'          => 'Tire Repair',
        ]);

        $express_service->translations()->create([
            'locale'        => 'ar',
            'name'          => 'اصلاح الاطار فى الموقع',
        ]);

        $express_service = ExpressService::create([
            'is_active' => true,
            'type'      => 'puncture',
            'price'     => 15,
        ]);

        $express_service->translations()->create([
            'locale'        => 'en',
            'name'          => 'Tire Repair',
        ]);

        $express_service->translations()->create([
            'locale'        => 'ar',
            'name'          => 'اصلاح الاطار فى المحطه',
        ]);

        $express_service = ExpressService::create([
            'is_active' => true,
            'type'      => 'puncture',
            'price'     => 20
        ]);

        $express_service->translations()->create([
            'locale'        => 'en',
            'name'          => 'Tire Repair',
        ]);

        $express_service->translations()->create([
            'locale'        => 'ar',
            'name'          => 'تغيير الاطار فى المحطه',
        ]);

        $express_service = ExpressService::create([
            'is_active' => true,
            'type'      => 'puncture',
            'price'     => 3,
        ]);

        $express_service->translations()->create([
            'locale'        => 'en',
            'name'          => 'Tire Repair',
        ]);

        $express_service->translations()->create([
            'locale'        => 'ar',
            'name'          => 'تعبئة الاطار باهواء',
        ]);

        $express_service = ExpressService::create([
            'is_active' => true,
            'type'      => 'tow_truck',
            'price'     => 100,
        ]);

        $express_service->translations()->create([
            'locale'        => 'en',
            'name'          => 'Tow Truck',
        ]);

        $express_service->translations()->create([
            'locale'        => 'ar',
            'name'          => 'سحب السيارة',
        ]);

    }
}
