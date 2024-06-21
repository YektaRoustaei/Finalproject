<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Provider;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;


class ProviderAuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'description' => 'required|string',
            'address' => 'required|string',
            'telephone' => 'required|integer',
            'email' => 'required|string|email|max:255|unique:providers',
            'password' => 'required|string|min:8',
        ]);

        $provider = Provider::query()->create([
            'company_name' => $request->company_name,
            'description' => $request->description,
            'address' => $request->address,
            'telephone' => $request->telephone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);



        return response()->json($provider);
    }
    /**
     * @throws ValidationException
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
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
