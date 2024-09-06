<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminTest extends TestCase
{
    public function testAdminCanLogin()
    {
        $admin = Admin::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/admin/login', [
            'username' => $admin->username,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'Admin_token',
                'token_type',
            ]);
    }

    public function testAdminCannotLoginWithInvalidCredentials()
    {
        Admin::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/admin/login', [
            'username' => 'invalid_username',
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'The provided credentials are incorrect.'
            ]);
    }

    public function testAdminCanLogout()
    {
        $admin = Admin::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $token = $admin->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/admin/logout');

        $response->assertStatus(200)
            ->assertExactJson([
                'Logged out successfully'
            ]);
    }

    public function testAdminCannotLogoutWithoutToken()
    {
        $response = $this->postJson('/api/admin/logout');

        $response->assertStatus(401);
    }
}

