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
        $provider = Provider::query()->where('email',$request->email)->first();

        if (! $provider || ! Hash::check($request->password, $provider->password)) {
            Log::info('Invalid login attempt', ['seeker' => $provider, 'password_check' => Hash::check($request->password, $provider->password)]);
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
        $token = $provider->createToken('auth_token')->plainTextToken;
        return response()->json(['access_token' => $token, 'token_type' => 'Bearer']);
    }

    public function logout(Request $request)
    {
        if (auth('sanctum')->user()) {
            auth('sanctum')->user()->tokens()->delete();;
            return response()->json('Logged out successfully');
        } else {
            return response()->json('User not authenticated', 401);
        }
    }

    //
}
