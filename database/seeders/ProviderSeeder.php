<?php

namespace Database\Seeders;

use App\Models\Provider;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Provider::create([
            'user_id'                   => 1,
            'city_id'                   => 1,
            'district_id'               => 1,
            'type'                      => 'center',
            'mechanical'                => true,
            'plumber'                   => true,
            'electrical'                => true,
            'puncture'                  => true,
            'battery'                   => true,
            'pickup'                    => true,
            'open_locks'                => true,
            'full_examination'          => true,
            'periodic_examination'      => true,
            'truck_barriers'            => true,
            'available_from'            => '2025-01-01 08:00:00',
            'available_to'              => '2025-01-01 17:00:00',
            'home_service'              => true,
            'commercial_register'       => '1234567890',
            'owner_identity'            => '1234567890',
            'general_license'           => '1234567890',
            'municipal_license'         => '1234567890',
            'is_active'                 => true,
            'created_at'                => now(),
            'updated_at'                => now(),
            ]);
    }
}
