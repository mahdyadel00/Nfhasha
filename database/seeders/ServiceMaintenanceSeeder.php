<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceMaintenanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $service_maintenances = [
            [
                'status'         => false,
            ],
        ];

        $service_maintenances->translateOrNew('en')->fill([
            'name'          => 'Electrical',
            'description'   => 'Electrical maintenance',
        ]);

        $service_maintenances->translateOrNew('ar')->fill([
            'name'          => 'كهربائي',
            'description'   => 'صيانة كهربائية',
        ]);
    }
}
