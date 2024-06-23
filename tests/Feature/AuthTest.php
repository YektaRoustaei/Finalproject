<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureUserIsProvider;
use App\Http\Middleware\EnsureUserIsSeeker;
use App\Http\Middleware\Seeker\Authentication\Login\PrepareRequestForLoginSeeker;
use App\Http\Middleware\Seeker\Authentication\Register\PrepareRequestForRegisteringSeeker;
use App\Models\Provider;
use App\Models\Seeker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
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

    public function test_provider_can_logout_successfully()
    {
        $provider = Provider::factory()->create();

        Sanctum::actingAs($provider, ['*']);

        $response = $this->postJson('api/provider/logout');

        $response->assertStatus(200);

        $this->assertCount(0, $provider->tokens);
    }

    public function test_unauthenticated_user_cannot_logout()
    {
        $response = $this->postJson('api/provider/logout');

        $response->assertStatus(401)
                 ->assertJson(['error' => 'Unauthorized']);
    }
    public function test_middleware_rejects_non_provider_user()
    {
        $seeker = Seeker::factory()->create();

        Sanctum::actingAs($seeker, ['*']);

        $request = Request::create('api/provider/logout', 'POST');

        $middleware = new EnsureUserIsProvider();

        $response = $middleware->handle($request, function ($req) {
            return response()->json(['success' => true], 200);
        });

        $this->assertEquals(401, $response->status());
        $this->assertEquals(['error' => 'Unauthorized'], $response->getData(true));
    }


    public function test_seeker_can_register_successfully(){
        $newSeeker = Seeker::factory()->make()->toArray();
        $response = $this->postJson('api/seeker/register', $newSeeker);
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'id',
                     'first_name',
                     'last_name',
                     'email',
                     'address',
                     'phonenumber',
                     'created_at',
                     'updated_at'
                 ]);

        $this->assertDatabaseCount('seekers', 1);            
    }

    public function test_registration_fails_with_invalid_data()
    {
        $this->withoutExceptionHandling();
        $data = []; // Empty data

        $response = $this->postJson('api/seeker/register', $data);


        $response->assertStatus(422);
        $this->assertCount(6, $response['errors']);
    }
    public function test_middleware_allows_valid_request()
    {
        $request = Request::create('/register', 'POST', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'johndoe@example.com',
            'address' => '123 Main St',
            'phonenumber' => '1234567890',
            'password' => 'password123',
        ]);

        $middleware = new PrepareRequestForRegisteringSeeker();

        $response = $middleware->handle($request, function ($req) {
            return response()->json(['success' => true], 200);
        });

        $this->assertEquals(200, $response->status());
        $this->assertEquals(['success' => true], $response->getData(true));
    }

    public function test_middleware_rejects_invalid_request()
    {
        $request = Request::create('/register', 'POST', [
            'first_name' => '',
            'last_name' => 'Doe',
            'email' => 'invalidemail@gmail.com',
            'address' => '123 Main St',
            'phonenumber' => 'notanumber',
            'password' => 'short',
        ]);

        $middleware = new PrepareRequestForRegisteringSeeker();

        $response = $middleware->handle($request, function ($req) {
            return response()->json(['success' => true], 200);
        });

        $this->assertEquals(422, $response->status());
        $this->assertArrayHasKey('errors', $response->getData(true));
        $this->assertEquals('The first name field is required.', $response->getData(true)['errors'][0]['message']);
    }

    public function test_login_fails_with_invalid_credentials()
    {
        $seeker = Seeker::factory()->create([
            'email' => 'johndoe@example.com',
            'password' => Hash::make('password123'),
        ]);

        $data = [
            'email' => 'johndoe@example.com',
            'password' => 'wrongpassword',
        ];

        $response = $this->postJson('api/seeker/login', $data);

        $response->assertStatus(422)
                 ->assertJsonStructure([
                     'errors' => [
                         'email' => []
                     ],
                 ]);
    }
    public function test_middleware_allows_valid_request_seeker_login()
    {
        $request = Request::create('/login', 'POST', [
            'email' => 'johndoe@example.com',
            'password' => 'password123',
        ]);

        $middleware = new PrepareRequestForLoginSeeker();

        $response = $middleware->handle($request, function ($req) {
            return response()->json(['success' => true], 200);
        });

        $this->assertEquals(200, $response->status());
        $this->assertEquals(['success' => true], $response->getData(true));
    }

    public function test_middleware_rejects_invalid_request_seeker_login()
    {
        $request = Request::create('/login', 'POST', [
            'email' => '',
            'password' => 'short',
        ]);

        $middleware = new PrepareRequestForLoginSeeker();

        $response = $middleware->handle($request, function ($req) {
            return response()->json(['success' => true], 200);
        });

        $this->assertEquals(422, $response->status());
        $this->assertArrayHasKey('errors', $response->getData(true));
        $this->assertEquals('The email field is required.', $response->getData(true)['errors'][0]['message']);
    }

    public function test_middleware_allows_authenticated_seeker()
    {
        $seeker = Seeker::factory()->create();

        Sanctum::actingAs($seeker, ['*']);

        $request = Request::create('/logout', 'POST');

        $middleware = new EnsureUserIsSeeker();

        $response = $middleware->handle($request, function ($req) {
            return response()->json(['success' => true], 200);
        });

        $this->assertEquals(200, $response->status());
        $this->assertEquals(['success' => true], $response->getData(true));
    }


    public function test_middleware_rejects_non_seeker_user()
    {
        $provider = Provider::factory()->create();

        Sanctum::actingAs($provider, ['*']);

        $request = Request::create('/logout', 'POST');

        $middleware = new EnsureUserIsSeeker();

        $response = $middleware->handle($request, function ($req) {
            return response()->json(['success' => true], 200);
        });

        $this->assertEquals(401, $response->status());
        $this->assertEquals(['error' => 'Unauthorized'], $response->getData(true));
    }

    public function test_middleware_rejects_unauthenticated_user()
    {
        $request = Request::create('/logout', 'POST');

        $middleware = new EnsureUserIsSeeker();

        $response = $middleware->handle($request, function ($req) {
            return response()->json(['success' => true], 200);
        });

        $this->assertEquals(401, $response->status());
        $this->assertEquals(['error' => 'Unauthorized'], $response->getData(true));
    }

    public function test_seeker_can_logout_successfully()
    {
        $seeker = Seeker::factory()->create();

        Sanctum::actingAs($seeker, ['*']);

        $response = $this->postJson('api/seeker/logout');

        $response->assertStatus(200);

        $this->assertCount(0, $seeker->tokens);
    }

    public function test_unauthenticated_seeker_user_cannot_logout()
    {
        $response = $this->postJson('api/seeker/logout');

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Unauthenticated.']);
    }
}
