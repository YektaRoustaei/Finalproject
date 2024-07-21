<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Seeker;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class SeekerAuthController extends Controller
{

    public function register(Request $request)
    {
        // Validate the request data


        try {
            // Create a new seeker
            $seeker = Seeker::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phonenumber' => $request->phonenumber,
                'password' => Hash::make($request->password),
                'city_id' => $request->city_id,
            ]);

            return response()->json($seeker, 201);
        } catch (Exception $e) {
            Log::error('Error registering seeker: ' . $e->getMessage());
            return response()->json(['error' => 'Registration failed'], 500);
        }
    }

    /**
     * Log in a seeker and return an authentication token.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */


    public function login(Request $request)
    {
        $seeker = $request->seeker;
        $token = $seeker->createToken('auth_token')->plainTextToken;

        return response()->json([
            'Seeker_token' => $token,
            'token_type' => 'Bearer',
            'first_name' => $seeker->first_name,
            'last_name' => $seeker->last_name,
        ], 200);
    }



    /**
     * Log out a seeker and revoke their authentication token.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        auth('sanctum')->user()->tokens()->delete();
        return response()->json('Logged out successfully', 200);
    }
}
