<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Seeker;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SeekerAuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $seeker = Seeker::query()->create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'address' => $request->address,
                'phonenumber' => $request->phonenumber,
                'password' => Hash::make($request->password),
            ]);
        } catch (Exception $e) {
            report($e);
        }

        return response()->json($seeker);
    }
    public function login(Request $request)
    {
        $token = $request->seeker->createToken('auth_token')->plainTextToken;
        return response()->json(['access_token' => $token, 'token_type' => 'Bearer']);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json('Logged out successfully');
    }
}
