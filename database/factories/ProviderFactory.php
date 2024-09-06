<?php

namespace Database\Factories;

use App\Models\Provider;
use App\Models\City; // Import the City model
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Provider>
 */
class ProviderFactory extends Factory
{
    protected $model = Provider::class;

    public function definition(): array
    {
        // Create or get an existing city to use
        $city = City::factory()->create();

        return [
            'company_name' => $this->faker->company,
            'description' => $this->faker->sentence,
            'telephone' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'password' => Hash::make('password123'),
            'city_id' => $city->id, // Add city_id field
        ];
    }
}
