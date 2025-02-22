<?php

namespace Database\Seeders;

use App\Models\TypePeriodicInspections;
use App\Models\TypePeriodicInspectionsTranslation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TypePeriodicInspectionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            'Visual Inspection',
            'Ultrasonic Testing',
            'Magnetic Particle Testing',
            'Liquid Penetrant Testing',
            'Eddy Current Testing',
            'Radiographic Testing',
            'Acoustic Emission Testing',
            'Thermal/Infrared Testing',
            'Leak Testing',
            'Vibration Analysis',
            'Corrosion Monitoring',
            'Thickness Measurement',
            'Hardness Testing',
            'Other',
        ];

            TypePeriodicInspections::create([
                'service_id'    => 13,
                'status'        => 1,
            ]);

            foreach ($types as $type) {
                TypePeriodicInspectionsTranslation::create([
                    'type_periodic_inspection_id' => 1,
                    'locale'                        => 'en',
                    'name'                          => $type,
                ]);
            }
    }
}
