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

        // Get all job postings with their requirements
        $jobs = JobPosting::with('requirements')->get();

        // Filter jobs based on the seeker's skills, requiring at least 3 matches
        $matchedJobs = $jobs->filter(function ($job) use ($skills) {
            $jobRequirements = $job->requirements->pluck('requirement')->toArray();
            $matchingSkillsCount = count(array_intersect($skills, $jobRequirements));
            return $matchingSkillsCount >= 3;
        });

        return response()->json([
            'jobs' => $matchedJobs->values() // Use values() to reset the array keys
        ]);
    }
}
