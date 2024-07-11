<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;


class SeekerInfo extends Controller
{
    public function __invoke(): JsonResponse
    {
        $provider = Auth::guard('sanctum')->user();
        return response()->json([
            'first_name' => $provider->first_name,
            'last_name' => $provider->last_name,
            'email' => $provider->email,
            'address' => $provider->address,
            'phonenumber' => $provider->phonenumber,

        ]);
    }
}
