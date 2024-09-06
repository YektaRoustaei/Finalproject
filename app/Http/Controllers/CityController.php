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
        $validated = $request->validate([
            'city_name' => 'required|string|max:255',
            'Latitude' => 'required|numeric',
            'Longitude' => 'required|numeric',
        ]);

        $city = City::create($validated);

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
        Log::info('Update request data:', $request->all());

        $validated = $request->validate([
            'city_name' => 'sometimes|required|string|max:255',
            'Latitude' => 'sometimes|required|numeric',
            'Longitude' => 'sometimes|required|numeric',
        ]);

        $city = City::find($id);

        if (!$city) {
            return response()->json(['message' => 'City not found'], 404);
        }

        Log::info('City before update:', $city->toArray());

        $city->update([
            'city_name' => $validated['city_name'] ?? $city->city_name,
            'latitude' => $validated['Latitude'] ?? $city->latitude,
            'longitude' => $validated['Longitude'] ?? $city->longitude,
        ]);

        Log::info('City after update:', $city->fresh()->toArray());

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
        $city = City::find($id);

        if (!$city) {
            return response()->json(['message' => 'City not found'], 404);
        }

        $city->delete();

        return response()->json(['message' => 'City deleted successfully!'], 200);
    }

    public function index()
    {
        $cities = City::all(['city_name', 'latitude', 'longitude']);

        return response()->json([
            'cities' => $cities,
        ], 200);
    }
}
