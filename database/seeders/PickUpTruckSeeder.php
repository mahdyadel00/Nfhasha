<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PickUpTruckSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pickUpTrucks = [
            [
                'name' => 'Toyota Hilux',
                'model' => '2021',
                'license_plate' => '
                    <div class="text-center">
                        <img src="https://via.placeholder.com/150" alt="license plate">
                    </div>
                ',
                'image' => '
                    <div class="text-center">
                        <img src="https://via.placeholder.com/150" alt="pick up truck">
                    </div>
                ',
            ],
            [
                'name' => 'Ford Ranger',
                'model' => '2021',
                'license_plate' => '
                    <div class="text-center">
                        <img src="https://via.placeholder.com/150" alt="license plate">
                    </div>
                ',
                'image' => '
                    <div class="text-center">
                        <img src="https://via.placeholder.com/150" alt="pick up truck">
                    </div>
                ',
            ],
            [
                'name' => 'Chevrolet Silverado',
                'model' => '2021',
                'license_plate' => '
                    <div class="text-center">
                        <img src="https://via.placeholder.com/150" alt="license plate">
                    </div>
                ',
                'image' => '
                    <div class="text-center">
                        <img src="https://via.placeholder.com/150" alt="pick up truck">
                    </div>
                ',
            ],
        ];
    }
}
