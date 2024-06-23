<?php

namespace Tests\Feature;

use App\Models\Provider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_provider_can_register_successfully()
    {
        $data = [
            'company_name' => 'Test Company',
            'description' => 'Test Description',
            'address' => '123 Test Address',
            'telephone' => '1234567890',
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('api/provider/register', $data);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'id',
                     'company_name',
                     'description',
                     'address',
                     'telephone',
                     'email',
                     'created_at',
                     'updated_at'
                 ]);

        $this->assertDatabaseHas('providers', [
            'email' => 'test@example.com',
        ]);

        $provider = Provider::where('email', 'test@example.com')->first();
        $this->assertTrue(Hash::check('password123', $provider->password));
    }

    public function test_provider_can_register_unsuccessfully_due_to_missing_data()
    {
        // generate empty array for unsuccessful registration
        $data = [];

        $response = $this->postJson('api/provider/register', $data);


        $response->assertStatus(422)
                 ->assertJsonStructure([
                     'errors' => [
                         '*' => [
                             'field',
                             'message'
                         ]
                     ]
                 ]);    
        $this->assertCount(6, $response['errors']);         
    }
    public function test_providers_can_not_register_with_repeated_email()
    {
        // create a provider
        $provider = Provider::factory()->create();

        // generate data with the same email
        $data = [
            'company_name' => 'Test Company',
            'description' => 'Test Description',
            'address' => '123 Test Address',
            'telephone' => '1234567890',
            'email' => $provider->email,
            'password' => 'password123',
        ];

        $response = $this->postJson('api/provider/register', $data);

        $response->assertStatus(422)
                 ->assertJsonStructure([
                     'errors' => [
                         '*' => [
                             'field',
                             'message'
                         ]
                     ]
                 ]);    
        $this->assertCount(1, $response['errors']);         
    }

    public function test_provider_can_login_successfully()
    {
        // create a provider
        $provider = Provider::factory()->create();

        // generate data for login
        $data = [
            'email' => $provider->email,
            'password' => 'password123',
        ];

        $response = $this->postJson('api/provider/login', $data);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'access_token',
                     'token_type'
                 ]);
    }
    
    public function test_provider_can_login_unsuccessfully()
    {
        // create a provider
        $provider = Provider::factory()->create();

        // generate data for login
        $data = [
            'email' => $provider->email,
            'password' => 'passrod1234',
        ];

        $response = $this->postJson('api/provider/login', $data);
        $response->assertStatus(422)
                 ->assertJsonStructure([
                     'message',
                     'errors' => [
                         'email'
                     ]
                 ]);
    }

    public function test_provider_can_login_unsuccessfully_missing_data()
    {
        // create a provider
        $provider = Provider::factory()->create();

        // generate data for login
        $data = [];

        $response = $this->postJson('api/provider/login', $data);
        $response->assertStatus(422);
        $this->assertCount(2, $response['errors']);         
    }
}
