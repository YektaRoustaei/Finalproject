<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Admin;

class AdminFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Admin::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => Hash::make('password'), // Better practice for password hashing
            'remember_token' => Str::random(10),
            // Add other fields as needed
        ];
    }

    /**
     * Indicate that the admin has a specific role.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function withRole(string $role): Factory
    {
        return $this->state(fn (array $attributes) => [
            'role' => $role,
        ]);
    }
}
