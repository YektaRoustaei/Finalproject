<?php

namespace Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\City;
use App\Models\Admin;

class CityControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testStoreCitySuccess()
    {

        $admin = Admin::factory()->create();


        $token = $admin->createToken('auth_token')->plainTextToken;


        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/city/register', [ // Ensure this matches your route
            'city_name' => 'Test City',
            'Latitude' => 12.34, // Ensure this matches the response key
            'Longitude' => 56.78, // Ensure this matches the response key
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'City created successfully!',
                'city' => [
                    'city_name' => 'Test City',
                    'Latitude' => 12.34, // Ensure this matches the response key
                    'Longitude' => 56.78, // Ensure this matches the response key
                ],
            ]);

        $this->assertDatabaseHas('cities', [
            'city_name' => 'Test City',
            'Latitude' => 12.34, // Ensure this matches the database schema
            'Longitude' => 56.78, // Ensure this matches the database schema
        ]);
    }

    public function testStoreCityMissingCityName()
    {
        $admin = Admin::factory()->create();

        $token = $admin->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/city/register', [ // Ensure this matches your route
            'Latitude' => 12.34,
            'Longitude' => 56.78,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['city_name']);
    }

    public function testStoreCityInvalidLatitude()
    {
        $admin = Admin::factory()->create();

        $token = $admin->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/city/register', [ // Ensure this matches your route
            'city_name' => 'Test City',
            'Latitude' => 'invalid_latitude',
            'Longitude' => 56.78,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['Latitude']);
    }

    public function testStoreCityInvalidLongitude()
    {
        $admin = Admin::factory()->create();

        $token = $admin->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/city/register', [
            'city_name' => 'Test City',
            'Latitude' => 12.34,
            'Longitude' => 'invalid_longitude',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['Longitude']);
    }


    public function testStoreCityMissingLatitude()
    {
        $admin = Admin::factory()->create();

        $token = $admin->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/city/register', [
            'city_name' => 'Test City',
            'Longitude' => 56.78,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['Latitude']);
    }


    public function testStoreCityMissingLongitude()
    {
        $admin = Admin::factory()->create();

        $token = $admin->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/city/register', [
            'city_name' => 'Test City',
            'Latitude' => 12.34,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['Longitude']);
    }
}
