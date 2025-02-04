<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\FAQ;
use App\Models\SplashScreen;
use App\Models\User;
use App\Models\VehicleBrand;
use App\Models\VehicleManufactureYear;
use App\Models\VehicleModel;
use App\Models\VehicleType;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            CitySeeder::class,
            DistrictSeeder::class,
            PickUpTruckSeeder::class,
            ProviderSeeder::class,
        ]);

        User::factory()->count(50)->create();
        User::factory()->provider()->count(10)->create();

        SplashScreen::factory()->count(5)->create();
        SplashScreen::factory()->withTranslations()->count(5)->create();


        VehicleManufactureYear::factory()->count(10)->create();
        VehicleType::factory()->count(10)->create();
        $this->call(VehicleTypeTranslationSeeder::class);
        VehicleBrand::factory()->count(10)->create();
        $this->call(VehicleBrandTranslationSeeder::class);
        VehicleModel::factory()->count(10)->create();
        $this->call(VehicleModelTranslationSeeder::class);
        $this->call(UserVehicleSeeder::class);

        $this->call(SettingsSeeder::class);

        FAQ::factory()->count(10)->create();
        $this->call(FAQSeeder::class);

    }
}
