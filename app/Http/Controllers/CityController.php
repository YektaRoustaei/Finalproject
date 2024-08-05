<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\City;
use Illuminate\Support\Facades\Log; // Import Log facade

class CityController extends Controller
{
    /**
     * Store a newly created city in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'city_name' => 'required|string|max:255',
            'Latitude' => 'required|numeric',
            'Longitude' => 'required|numeric',
        ]);

        // Create a new city instance and fill it with validated data
        $city = City::create($validated);

        // Return a response
        return response()->json([
            'message' => 'City created successfully!',
            'city' => $city,
        ], 201);
    }

    /**
     * Update the specified city in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Log the incoming request data
        Log::info('Update request data:', $request->all());

        // Validate the request data
        $validated = $request->validate([
            'city_name' => 'sometimes|required|string|max:255',
            'Latitude' => 'sometimes|required|numeric',
            'Longitude' => 'sometimes|required|numeric',
        ]);

        // Find the city by ID
        $city = City::find($id);

        if (!$city) {
            return response()->json(['message' => 'City not found'], 404);
        }

        // Log the current state of the city before update
        Log::info('City before update:', $city->toArray());

        // Update the city with validated data
        $city->update([
            'city_name' => $validated['city_name'] ?? $city->city_name,
            'latitude' => $validated['Latitude'] ?? $city->latitude,
            'longitude' => $validated['Longitude'] ?? $city->longitude,
        ]);

        // Log the state of the city after update
        Log::info('City after update:', $city->fresh()->toArray());

        // Return a response
        return response()->json([
            'message' => 'City updated successfully!',
            'city' => $city,
        ], 200);
    }

    /**
     * Remove the specified city from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Find the city by ID
        $city = City::find($id);

        if (!$city) {
            return response()->json(['message' => 'City not found'], 404);
        }

        // Delete the city
        $city->delete();

        // Return a response
        return response()->json(['message' => 'City deleted successfully!'], 200);
    }

    public function index()
    {
        // Fetch all cities
        $cities = City::all(['city_name', 'latitude', 'longitude']);

        // Return the list of cities in JSON format
        return response()->json([
            'cities' => $cities,
        ], 200);
    }
}
