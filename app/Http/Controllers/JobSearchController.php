<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\JobPosting;
use App\Models\City;

class JobSearchController extends Controller
{
    /**
     * Search for job postings by title, provider company name, provider city, or job type.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $searchTerm = trim($request->input('search_term'));
        $cityName = trim($request->input('city'));
        $jobType = trim($request->input('job_type'));

        // Fetch city details if cityName is provided
        $cityDetails = $this->getCityDetails($cityName);

        // Perform the search
        $results = $this->searchJobs($searchTerm, $cityDetails, $jobType);

        // If no results found and both searchTerm and city are provided, perform an alternative search
        if ($cityName && $searchTerm && $results->isEmpty()) {
            $results = $this->searchJobs($searchTerm, null, $jobType);
        }

        // Ensure results are in array format
        $response = [
            'jobs' => $results->values(), // Convert to array
            'city' => $cityDetails,
        ];

        return response()->json($response);
    }

    protected function getCityDetails(?string $cityName): ?array
    {
        if (!$cityName) {
            return null;
        }

        $city = City::where('city_name', 'like', '%' . $cityName . '%')->first();

        if ($city) {
            return [
                'city_name' => $city->city_name,
                'latitude' => $city->latitude,
                'longitude' => $city->longitude,
            ];
        }

        return null;
    }

    protected function searchJobs(?string $searchTerm, ?array $cityDetails, ?string $jobType)
    {
        $jobs = JobPosting::query()
            ->with(['provider.city', 'categories', 'jobskills.skill'])
            ->when($searchTerm, function ($query, $searchTerm) {
                $query->where('title', 'like', '%' . $searchTerm . '%')
                    ->orWhere('description', 'like', '%' . $searchTerm . '%')
                    ->orWhereHas('provider', function ($query) use ($searchTerm) {
                        $query->where('company_name', 'like', '%' . $searchTerm . '%')
                            ->orWhereHas('city', function ($query) use ($searchTerm) {
                                $query->where('city_name', 'like', '%' . $searchTerm . '%');
                            });
                    })
                    ->orWhereHas('categories', function ($query) use ($searchTerm) {
                        $query->where('name', 'like', '%' . $searchTerm . '%');
                    })
                    ->orWhereHas('jobskills.skill', function ($query) use ($searchTerm) {
                        $query->where('name', 'like', '%' . $searchTerm . '%');
                    });
            })
            ->when($jobType, function ($query, $jobType) {
                $query->where('type', 'like', '%' . $jobType . '%');
            })
            ->get();

        // Calculate distance if city details are provided
        if ($cityDetails) {
            $latitude = $cityDetails['latitude'];
            $longitude = $cityDetails['longitude'];

            $jobs = $jobs->map(function ($job) use ($latitude, $longitude) {
                $jobCity = $job->provider->city;

                if ($jobCity) {
                    $jobLatitude = $jobCity->latitude;
                    $jobLongitude = $jobCity->longitude;

                    // Calculate the distance
                    $distance = $this->calculateDistance($latitude, $longitude, $jobLatitude, $jobLongitude);
                    $job->distance = $distance;
                } else {
                    $job->distance = null;
                }

                return $job;
            })->sortBy('distance');
        }

        // Format the job results
        return $jobs->map(function ($job) {
            // Get categories
            $categories = $job->categories->pluck('name')->toArray();

            // Get skills
            $skills = $job->jobskills->pluck('skill.name')->toArray();

            return [
                'id' => $job->id,
                'title' => $job->title,
                'description' => $job->description,
                'salary' => $job->salary,
                'type' => $job->type,
                'provider_id' => $job->provider_id,
                'provider_name' => $job->provider ? $job->provider->company_name : null,
                'provider_city' => $job->provider && $job->provider->city ? $job->provider->city->city_name : null,
                'categories' => $categories,
                'skills' => $skills,
                'distance' => $job->distance,
                'created_at' => $job->created_at,
                'updated_at' => $job->updated_at,
            ];
        });
    }

    protected function calculateDistance(float $latitudeFrom, float $longitudeFrom, float $latitudeTo, float $longitudeTo): float
    {
        $earthRadius = 6371; // Radius of the Earth in kilometers

        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }
}
