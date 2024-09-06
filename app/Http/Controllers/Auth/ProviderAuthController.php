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

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'company_name' => 'required|string|max:255',
            'description' => 'required|string',
            'telephone' => 'required|string',
            'email' => 'required|email',
            'address' => 'required|string',
        ]);

        try {
            $provider = Provider::findOrFail($id);

            $provider->update($validatedData);

            return response()->json($provider, 200);
        } catch (\Exception $e) {
            Log::error('Provider update failed: ' . $e->getMessage());
            return response()->json(['error' => 'Update failed'], 500);
        }
    }

    public function delete($id)
    {
        try {
            $provider = Provider::findOrFail($id);
            $provider->delete();
            return response()->json(['message' => 'Provider deleted successfully'], 200);
        } catch (Exception $e) {
            Log::error('Provider deletion failed: ' . $e->getMessage());
            return response()->json(['error' => 'Deletion failed'], 500);
        }
    }
}
