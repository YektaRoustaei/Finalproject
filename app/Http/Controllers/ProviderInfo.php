<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProviderInfo extends Controller
{
    public function __invoke(): JsonResponse
    {
        $provider = Auth::guard('sanctum')->user();
        return response()->json([
            'company_name' => $provider->company_name,
            'description' => $provider->description,
            'address' => $provider->address,
            'telephone' => $provider->telephone,
            'email' => $provider->email,
            'id' => $provider->id,

        ]);
    }
}
