<?php

namespace App\Http\Controllers;

use App\Models\JobPosting;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReccomendationController extends Controller
{
    public function jobRecommend(Request $request)
    {
        $seeker = Auth::guard('sanctum')->user();
        if (!$seeker) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $curriculumVitae = $seeker->curriculumVitae()
            ->with('seekerSkills.skill')
            ->latest() // This orders by created_at in descending order
            ->first(); // Fetches the most recent one

        if (!$curriculumVitae) {
            return response()->json(['error' => 'No Curriculum Vitae found.'], 404);
        }

        // Get the skills from the curriculum vitae
        $skills = $curriculumVitae->seekerSkills->pluck('skill.name')->toArray();

        // Get the seeker city details
        $seekerCity = $seeker->city;
        if (!$seekerCity) {
            return response()->json(['error' => 'Seeker city not found.'], 404);
        }

        // Retrieve search parameters
        $searchTerm = trim($request->input('search_term'));
        $cityName = trim($request->input('city'));

        // Get city details if cityName is provided
        $cityDetails = $this->getCityDetails($cityName);

        // Query job postings with search functionality
        $jobsQuery = JobPosting::query()
            ->with(['jobSkills.skill', 'provider.city'])
            ->when($searchTerm, function ($query, $searchTerm) {
                $query->where('title', 'like', '%' . $searchTerm . '%')
                    ->orWhere('description', 'like', '%' . $searchTerm . '%')
                    ->orWhereHas('provider', function ($query) use ($searchTerm) {
                        $query->where('company_name', 'like', '%' . $searchTerm . '%')
                            ->orWhereHas('city', function ($query) use ($searchTerm) {
                                $query->where('city_name', 'like', '%' . $searchTerm . '%');
                            });
                    })
                    ->orWhereHas('jobSkills.skill', function ($query) use ($searchTerm) {
                        $query->where('name', 'like', '%' . $searchTerm . '%');
                    });
            });

        $jobs = $jobsQuery->get();

        // Calculate distances and sort jobs based on distance from input city or seeker city
        $jobsWithDetails = $jobs->map(function ($job) use ($cityDetails, $seekerCity, $skills) {
            $jobCity = $job->provider->city;
            if (!$jobCity) {
                Log::warning('Job City not found for job ID ' . $job->id);
                return null;
            }

            // Calculate the distance from the input city
            $distanceFromInputCity = $cityDetails
                ? $this->calculateDistance(
                    $cityDetails['latitude'], $cityDetails['longitude'],
                    $jobCity->latitude, $jobCity->longitude
                )
                : null;

            // Calculate the distance from the seeker's city
            $distanceFromSeekerCity = $this->calculateDistance(
                $seekerCity->latitude, $seekerCity->longitude,
                $jobCity->latitude, $jobCity->longitude
            );

            // Get job skills
            $jobSkills = $job->jobSkills->pluck('skill.name')->toArray();

            // Calculate matching skills
            $matchingSkills = array_intersect($skills, $jobSkills);
            $matchingSkillsCount = count($matchingSkills);

            // Attach details to job
            $job->distance_from_input_city = $distanceFromInputCity; // Distance from input city
            $job->distance_from_seeker_city = $distanceFromSeekerCity; // Distance from seeker city
            $job->matching_skills = $matchingSkills; // Matching skills
            $job->matching_skills_count = $matchingSkillsCount; // Count of matching skills

            return $job;
        })->filter(); // Remove null values

        // Determine sorting logic based on whether city input is provided
        if ($cityDetails) {
            // Sort jobs by distance from the input city
            $sortedJobs = $jobsWithDetails->sortBy('distance_from_input_city')->values();
        } else {
            // Sort jobs by matching skills count (descending) and then by distance from seeker's city
            $sortedJobs = $jobsWithDetails->sort(function ($a, $b) {
                // First sort by matching skills count (descending)
                $matchingSkillsComparison = $b->matching_skills_count - $a->matching_skills_count;
                if ($matchingSkillsComparison !== 0) {
                    return $matchingSkillsComparison;
                }

                // If matching skills count is the same, sort by distance from seeker city
                return $a->distance_from_seeker_city - $b->distance_from_seeker_city;
            })->values(); // Reset array keys
        }

        // Prepare the response with job details
        $response = $sortedJobs->map(function ($job) {
            return [
                'id' => $job->id,
                'title' => $job->title,
                'salary' => $job->salary,
                'type' => $job->type,
                'description' => $job->description,
                'provider_city' => $job->provider->city->city_name ?? 'Unknown',
                'provider_name' => $job->provider->company_name,
                'job_skills' => $job->jobSkills->pluck('skill.name')->toArray(),
                'matching_skills' => $job->matching_skills ?? [],
                'matching_skills_count' => $job->matching_skills_count ?? 0,
                'distance_from_input_city' => $job->distance_from_input_city,
                'distance_from_seeker_city' => $job->distance_from_seeker_city,
            ];
        });

        return response()->json([
            'jobs' => $response
        ]);
    }

    /**
     * Fetch city details based on city name.
     *
     * @param string|null $cityName
     * @return array|null
     */
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

    /**
     * Calculate the distance between two points using the Haversine formula.
     *
     * @param float $lat1 Latitude of the first point
     * @param float $lon1 Longitude of the first point
     * @param float $lat2 Latitude of the second point
     * @param float $lon2 Longitude of the second point
     * @return float Distance in miles
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 3959; // Radius of the Earth in miles

        // Convert latitude and longitude from degrees to radians
        $lat1Rad = deg2rad($lat1);
        $lon1Rad = deg2rad($lon1);
        $lat2Rad = deg2rad($lat2);
        $lon2Rad = deg2rad($lon2);

        // Calculate the differences
        $deltaLat = $lat2Rad - $lat1Rad;
        $deltaLon = $lon2Rad - $lon1Rad;

        // Haversine formula
        $a = sin($deltaLat / 2) ** 2 + cos($lat1Rad) * cos($lat2Rad) * sin($deltaLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
