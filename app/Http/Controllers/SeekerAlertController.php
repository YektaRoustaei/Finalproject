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

        $jobAlert = JobAlert::where('job_id', $request->job_id)
            ->where('seeker_id', $seeker->id)
            ->first();

        if ($jobAlert) {
            $jobAlert->status = JobAlert::STATUS_NOT_INTERESTED;
            $jobAlert->save();
        } else {
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

        $curriculumVitae = $seeker->curriculumVitae()
            ->with('seekerSkills.skill')
            ->latest()
            ->first();

        if (!$curriculumVitae) {
            return response()->json(['error' => 'No Curriculum Vitae found.'], 404);
        }

        $skills = $curriculumVitae->seekerSkills->pluck('skill.name')->toArray();

        $seekerCity = $seeker->city;
        if (!$seekerCity) {
            return response()->json(['error' => 'Seeker city not found.'], 404);
        }

        $searchTerm = trim($request->query('search_term')); // Use query method for URL parameters
        $city = trim($request->query('city'));

        $appliedJobIds = AppliedJob::where('seeker_id', $seeker->id)
            ->pluck('job_id')
            ->toArray();

        $notInterestedJobIds = JobAlert::where('seeker_id', $seeker->id)
            ->where('status', JobAlert::STATUS_NOT_INTERESTED)
            ->pluck('job_id')
            ->toArray();

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

        $jobsWithDistance = $jobs->map(function ($job) use ($skills, $seekerCity) {
            $jobSkills = $job->jobSkills->pluck('skill.name')->toArray();
            $matchingSkills = array_intersect($skills, $jobSkills);
            $matchingSkillsCount = count($matchingSkills);
            $job->matchingSkills = $matchingSkills;

            $jobCity = $job->provider->city;
            if (!$jobCity) {
                Log::warning('Job City not found for job ID ' . $job->id);
                return null;
            }

            $distance = $this->calculateDistance(
                $seekerCity->latitude, $seekerCity->longitude,
                $jobCity->latitude, $jobCity->longitude
            );
            $job->distance = $distance; // Store distance in the job object

            return $matchingSkillsCount > 2 ? [
                'job' => $job,
                'matchingSkillsCount' => $matchingSkillsCount,
                'distance' => $distance,
                'isInSeekerCity' => $jobCity->city_name == $seekerCity->city_name
            ] : null;
        })->filter();

        $sortedJobs = $jobsWithDistance->sort(function ($a, $b) {
            if ($a['isInSeekerCity'] == $b['isInSeekerCity']) {
                return $a['distance'] - $b['distance'];
            }
            return $b['isInSeekerCity'] - $a['isInSeekerCity'];
        })->values();

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
        $earthRadius = 3959;

        $lat1Rad = deg2rad($lat1);
        $lon1Rad = deg2rad($lon1);
        $lat2Rad = deg2rad($lat2);
        $lon2Rad = deg2rad($lon2);

        $deltaLat = $lat2Rad - $lat1Rad;
        $deltaLon = $lon2Rad - $lon1Rad;

        $a = sin($deltaLat / 2) ** 2 + cos($lat1Rad) * cos($lat2Rad) * sin($deltaLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
