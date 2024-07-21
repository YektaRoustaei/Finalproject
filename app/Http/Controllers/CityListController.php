<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;

class CityListController extends Controller
{
    public function cities()
    {
        try {
            // Fetch all skills without any relationships
            $cities = City::all();
            return response()->json($cities);
        } catch (\Exception $e) {
            \Log::error('Error fetching cities listings: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch cities listings.'], 500);
        }
    }
}
