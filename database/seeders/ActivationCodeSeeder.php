<?php

namespace Database\Seeders;

use App\Models\ActivationCode;
use Illuminate\Database\Seeder;

class ActivationCodeSeeder extends Seeder
{
    public function run()
    {
        // Generate 10 test activation codes with different amounts
        $amounts = [10, 20, 50, 100, 200];

        for ($i = 0; $i < 10; $i++) {
            ActivationCode::create([
                'code' => strtoupper(uniqid()),
                'amount' => $amounts[array_rand($amounts)]
            ]);
        }
    }
}
