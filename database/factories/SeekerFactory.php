<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Seeker;
use Illuminate\Database\Eloquent\Factories\Factory;


class SeekerFactory extends Factory
{
    protected $model = Seeker::class;

    public function definition()
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'city_id' => City::factory(), // Ensure this is correct
            'phonenumber' => $this->faker->phoneNumber,
            'password' => $this->faker->password,
        ];
    }
}
