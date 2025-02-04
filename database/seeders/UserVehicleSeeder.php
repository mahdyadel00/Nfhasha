<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\VehicleType;
use App\Models\VehicleModel;
use App\Models\VehicleManufactureYear;
use App\Models\VehicleBrand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserVehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {


        $users = User::where('role', 'user')->get();

        $users->each(function ($user) {
            $faker = \Faker\Factory::create();

            $user->vehicles()->create([
                'vehicle_type_id' => VehicleType::inRandomOrder()->first()->id,
                'vehicle_model_id' => VehicleModel::inRandomOrder()->first()->id,
                'vehicle_manufacture_year_id' => VehicleManufactureYear::inRandomOrder()->first()->id,
                'vehicle_brand_id' => VehicleBrand::inRandomOrder()->first()->id,
                'letters_ar' => $this->randomStringFromSet('أبتثجحخدذرزسشصضطظعغفقكلمنهوي', 3),
                'numbers_ar' => $this->randomStringFromSet('٠١٢٣٤٥٦٧٨٩', 3),
                'letters_en' => $this->randomStringFromSet('ABCDEFGHIJKLMNOPQRSTUVWXYZ', 3),
                'numbers_en' => $this->randomStringFromSet('0123456789', 3),
                'checkup_date' => $faker->date(),
            ]);
        });
    }

    /**
     * Generate a random string from a custom character set.
     *
     * @param string $set
     * @param int $length
     * @return string
     */
    private function randomStringFromSet(string $set, int $length): string
    {
        $result = '';
        $setLength = mb_strlen($set);

        for ($i = 0; $i < $length; $i++) {
            $index = random_int(0, $setLength - 1);
            $result .= mb_substr($set, $index, 1);
        }

        return $result;
    }
}
