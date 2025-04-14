<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Direction;

class DirectionSeeder extends Seeder
{
    public function run()
    {
        $directions = [['direction' => 'front', 'price' => 100.0], ['direction' => 'back', 'price' => 120.0], ['direction' => 'left', 'price' => 90.0], ['direction' => 'right', 'price' => 110.0]];

        foreach ($directions as $direction) {
            Direction::create($direction);
        }
    }
}
