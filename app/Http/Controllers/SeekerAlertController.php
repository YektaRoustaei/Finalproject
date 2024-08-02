<?php

namespace App\Http\Controllers;

use App\Models\AppliedJob;
use App\Models\JobPosting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\JobAlert;


class SeekerAlertController extends Controller
{
    public function markNotInterested(Request $request)
    {
        $seeker = Auth::guard('sanctum')->user();

        if (!$seeker) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $request->validate([
            'job_id' => 'required|exists:job_postings,id',
        ]);

        // Check if the job alert already exists
        $jobAlert = JobAlert::where('job_id', $request->job_id)
            ->where('seeker_id', $seeker->id)
            ->first();

        if ($jobAlert) {
            // Update existing job alert status
            $jobAlert->status = JobAlert::STATUS_NOT_INTERESTED;
            $jobAlert->save();
        } else {
            // Create a new job alert
            JobAlert::create([
                'job_id' => $request->job_id,
                'seeker_id' => $seeker->id,
                'status' => JobAlert::STATUS_NOT_INTERESTED,
            ]);
        }

        return response()->json(['message' => 'Job marked as not interested.']);
    }

    public function jobRecommend(Request $request)
    {
        $seeker = Auth::guard('sanctum')->user();

        if (!$seeker) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        // Fetch the most recent Curriculum Vitae
        $curriculumVitae = $seeker->curriculumVitae()
            ->with('seekerSkills.skill')
            ->latest() // Orders by created_at in descending order
            ->first(); // Fetches the most recent one

        if (!$curriculumVitae) {
            return response()->json(['error' => 'No Curriculum Vitae found.'], 404);
        }

        // Get the skills from the curriculum vitae
        $skills = $curriculumVitae->seekerSkills->pluck('skill.name')->toArray();

        // Get the seeker city
        $seekerCity = $seeker->city;
        if (!$seekerCity) {
            return response()->json(['error' => 'Seeker city not found.'], 404);
        }

        // Retrieve search parameters from the URL
        $searchTerm = trim($request->query('search_term')); // Use query method for URL parameters
        $city = trim($request->query('city'));

        // Fetch applied jobs for the seeker
        $appliedJobIds = AppliedJob::where('seeker_id', $seeker->id)
            ->pluck('job_id')
            ->toArray();

        // Fetch jobs marked as "Not Interested"
        $notInterestedJobIds = JobAlert::where('seeker_id', $seeker->id)
            ->where('status', JobAlert::STATUS_NOT_INTERESTED)
            ->pluck('job_id')
            ->toArray();

        // Query job postings with search functionality
        $jobsQuery = JobPosting::query()
            ->with(['jobSkills.skill', 'provider.city'])
            ->whereNotIn('id', $appliedJobIds) // Exclude applied jobs
            ->whereNotIn('id', $notInterestedJobIds) // Exclude jobs marked as "Not Interested"
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
            })
            ->when($city, function ($query, $city) {
                $query->whereHas('provider.city', function ($query) use ($city) {
                    $query->where('city_name', 'like', '%' . $city . '%');
                });
            });

        $jobs = $jobsQuery->get();

        // Calculate distances and filter jobs based on skills
        $jobsWithDistance = $jobs->map(function ($job) use ($skills, $seekerCity) {
            $jobSkills = $job->jobSkills->pluck('skill.name')->toArray();
            $matchingSkills = array_intersect($skills, $jobSkills);
            $matchingSkillsCount = count($matchingSkills);
            $job->matchingSkills = $matchingSkills; // Store matched skills in the job object

            // Get job provider city
            $jobCity = $job->provider->city;
            if (!$jobCity) {
                Log::warning('Job City not found for job ID ' . $job->id);
                return null;
            }

            // Calculate the distance
            $distance = $this->calculateDistance(
                $seekerCity->latitude, $seekerCity->longitude,
                $jobCity->latitude, $jobCity->longitude
            );
            $job->distance = $distance; // Store distance in the job object

            // Return only jobs with more than 2 matching skills
            return $matchingSkillsCount > 2 ? [
                'job' => $job,
                'matchingSkillsCount' => $matchingSkillsCount,
                'distance' => $distance,
                'isInSeekerCity' => $jobCity->city_name == $seekerCity->city_name
            ] : null;
        })->filter(); // Remove null values

        // Sort jobs primarily by whether they are in the seeker's city, then by distance
        $sortedJobs = $jobsWithDistance->sort(function ($a, $b) {
            if ($a['isInSeekerCity'] == $b['isInSeekerCity']) {
                return $a['distance'] - $b['distance'];
            }
            return $b['isInSeekerCity'] - $a['isInSeekerCity'];
        })->values(); // Reset array keys

        // Prepare the response with matched skills, job skills, and company name
        $response = $sortedJobs->map(function ($item) {
            $job = $item['job'];
            return [
                'id' => $job->id,
                'title' => $job->title,
                'salary' => $job->salary,
                'type' => $job->type,
                'description' => $job->description,
                'provider_city' => $job->provider->city->city_name ?? 'Unknown',
                'provider_name' => $job->provider->company_name,
                'job_skills' => $job->jobSkills->pluck('skill.name')->toArray(),
                'matching_skills' => $job->matchingSkills ?? [],
                'distance' => $item['distance'],
            ];
        });

        return response()->json([
            'jobs' => $response
        ]);
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
