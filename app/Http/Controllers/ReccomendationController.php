<?php

namespace App\Http\Controllers;

use App\Models\CurriculumVitae;
use App\Models\JobPosting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            ->first();

        if (!$curriculumVitae) {
            return response()->json(['error' => 'No Curriculum Vitae found.'], 404);
        }

        // Get the skills from the curriculum vitae
        $skills = $curriculumVitae->seekerSkills->pluck('skill.name')->toArray();

        // Get all job postings with their job skills and provider
        $jobs = JobPosting::with(['jobSkills.skill', 'provider'])->get();

        // Filter jobs based on the seeker's skills, requiring at least 3 matches
        $recommendedJobs = $jobs->filter(function ($job) use ($skills) {
            $jobSkills = $job->jobSkills->pluck('skill.name')->toArray();
            $matchingSkills = array_intersect($skills, $jobSkills);
            $matchingSkillsCount = count($matchingSkills);
            $job->matchingSkills = $matchingSkills; // Store matched skills in the job object
            return $matchingSkillsCount >= 3;
        });

        // Get the IDs of recommended jobs
        $recommendedJobIds = $recommendedJobs->pluck('id')->toArray();

        // Filter out recommended jobs from all jobs
        $nonRecommendedJobs = $jobs->reject(function ($job) use ($recommendedJobIds) {
            return in_array($job->id, $recommendedJobIds);
        });

        // Combine recommended jobs first, followed by the rest
        $sortedJobs = $recommendedJobs->merge($nonRecommendedJobs);

        // Prepare the response with matched skills, job skills, and company name
        $response = $sortedJobs->map(function ($job) {
            return [
                'id' => $job->id,
                'title' => $job->title,
                'salary' => $job->salary,
                'type' => $job->type,
                'description' => $job->description,
                'location' => $job->provider->address,
                'company_name' => $job->provider->company_name,
                'job_skills' => $job->jobSkills->pluck('skill.name')->toArray(), // Include job skills in the response
                'matching_skills' => $job->matchingSkills ?? [], // Include matched skills in the response
            ];
        });

        return response()->json([
            'jobs' => $response->values() // Use values() to reset the array keys
        ]);
    }
}
