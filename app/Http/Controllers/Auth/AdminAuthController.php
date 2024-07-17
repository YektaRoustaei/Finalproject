<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Validation\ValidationException;


class AdminAuthController extends Controller
{
    /**
     * @throws ValidationException
     */
    public function login(Request $request)
    {

        $token = $request->admin->createToken('auth_token')->plainTextToken;
        return response()->json(['Admin_token' => $token, 'token_type' => 'Bearer']);



    }
    public function logout()
    {
        auth('sanctum')->user()->tokens()->delete();
        return response()->json('Logged out successfully', 200);
    }
}
