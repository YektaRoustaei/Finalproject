<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ProviderAuthController extends Controller
{

    public function register(Request $request)
    {
        try {
            $provider = Provider::create([
                'company_name' => $request->company_name,
                'description' => $request->description,
                'telephone' => $request->telephone,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'city_id' => $request->city_id, // Ensure city_id is included in the model and fillable
            ]);
            return response()->json($provider, 201);
        } catch (Exception $e) {
            Log::error('Provider registration failed: ' . $e->getMessage());
            return response()->json(['error' => 'Registration failed'], 500);
        }
    }

    /**
     * @throws ValidationException
     */
    public function login(Request $request)
    {
        $token = $request->provider->createToken('auth_token')->plainTextToken;

        return response()->json([
            'Provider_token' => $token,
            'token_type' => 'Bearer',
            'company_name' => $request->provider->company_name,
        ]);
    }

    public function logout()
    {
        auth('sanctum')->user()->tokens()->delete();
        return response()->json('Logged out successfully', 200);
    }
}
