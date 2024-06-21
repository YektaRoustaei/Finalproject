<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Seeker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SeekerAuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:seekers',
            'address' => 'required|string',
            'phonenumber' => 'required|integer',
            'password' => 'required|string|min:8',
        ]);

        $seeker = Seeker::query()->create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'address' => $request->address,
            'phonenumber' => $request->phonenumber,
            'password' => Hash::make($request->password),
        ]);

        return response()->json($seeker);
    }
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $seeker = Seeker::where('email', $request->email)->first();

        if (! $seeker || ! Hash::check($request->password, $seeker->password)) {
            Log::info('Invalid login attempt', ['seeker' => $seeker, 'password_check' => Hash::check($request->password, $seeker->password)]);
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $seeker->createToken('auth_token')->plainTextToken;

        return response()->json(['access_token' => $token, 'token_type' => 'Bearer']);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json('Logged out successfully');
    }
}
