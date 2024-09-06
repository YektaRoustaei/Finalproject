<?php

namespace Database\Factories;

use App\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;

class CityFactory extends Factory
{
    protected $model = City::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'city_name' => $this->faker->city,      // Generates a random city name
            'Latitude' => $this->faker->latitude,   // Generates a random latitude
            'Longitude' => $this->faker->longitude, // Generates a random longitude
        ];
    }
}
