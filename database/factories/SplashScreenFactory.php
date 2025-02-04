<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SplashScreen>
 */
class SplashScreenFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'image' => $this->faker->imageUrl(),
            'is_active' => $this->faker->boolean,
            'order' => 0,
        ];
    }

    /**
     * Record Translation
     */

     public function withTranslations(): self
     {
         return $this->afterCreating(function (\App\Models\SplashScreen $splashScreen) {
             $splashScreen->translations()->create([
                 'locale' => 'en',
                 'title' => $this->faker->sentence,
                 'description' => $this->faker->paragraph,
             ]);

                $splashScreen->translations()->create([
                    'locale' => 'ar',
                    'title' => $this->faker->sentence,
                    'description' => $this->faker->paragraph,
                ]);
         });
    }
}
