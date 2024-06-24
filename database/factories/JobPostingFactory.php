<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobPosting>
 */
class JobPostingFactory extends Factory
{
    protected $model = \App\Models\JobPosting::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->jobTitle,
            'description' => $this->faker->paragraph,
            'provider_id' => \App\Models\Provider::factory(),
            'salary' => $this->faker->numberBetween(1000, 9000),
            'type' => $this->faker->randomElement(['full-time', 'part-time', 'contract']),
        ];
    }
}
