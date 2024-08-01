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

    public function logout(Request $request)
    {
        auth('sanctum')->user()->tokens()->delete();
        return response()->json('Logged out successfully', 200);
    }

    public function update(Request $request)
    {
        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Validate the request data
        $validatedData = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:seekers,email,' . $user->id,
            'phonenumber' => 'sometimes|string|max:20',
            'password' => 'sometimes|string|min:6|confirmed',
            'city_id' => 'sometimes|integer|exists:cities,id',
        ]);

        try {
            // Log the validated data for debugging
            Log::info('Validated Data:', ['data' => $validatedData]);

            // If a new password is set, hash it before updating
            if (isset($validatedData['password'])) {
                $validatedData['password'] = Hash::make($validatedData['password']);
            }

            // Update the seeker's details
            $user->update($validatedData);

            // Return the updated user
            $user->load('city'); // If you want to include related models like city

            return response()->json($user, 200);
        } catch (Exception $e) {
            Log::error('Error updating seeker details: ' . $e->getMessage());
            return response()->json(['error' => 'Update failed'], 500);
        }
    }

    /**
     * Delete the authenticated seeker's account.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAccount(Request $request)
    {
        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            // Delete the seeker's account
            $user->delete();

            // Log out the user by removing their tokens
            $user->tokens()->delete();

            return response()->json(['message' => 'Account deleted successfully'], 200);
        } catch (Exception $e) {
            Log::error('Error deleting seeker account: ' . $e->getMessage());
            return response()->json(['error' => 'Account deletion failed'], 500);
        }
    }
}
