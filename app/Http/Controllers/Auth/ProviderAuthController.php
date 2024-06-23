<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;


class ProviderAuthController extends Controller
{

    public function register(Request $request)
    {
        try {
            $provider = Provider::query()->create([
                'company_name' => $request->company_name,
                'description' => $request->description,
                'address' => $request->address,
                'telephone' => $request->telephone,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
        } catch (Exception $e) {
            report($e);
        }
        return response()->json($provider);
    }
    /**
     * @throws ValidationException
     */
    public function login(Request $request)
    {
        $token = $request->provider->createToken('auth_token')->plainTextToken;
        return response()->json(['access_token' => $token, 'token_type' => 'Bearer']);
    }

    public function logout()
    {
        auth('sanctum')->user()->tokens()->delete();;
        return response()->json('Logged out successfully', 200);
    }
}
