<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SeekerInfo extends Controller
{
    public function __invoke(): JsonResponse
    {
        $seeker = Auth::guard('sanctum')->user();

        // Fetch saved jobs, applied jobs, and CVs with related details
        $savedJobs = $seeker->savedJobs;
        $appliedJobs = $seeker->appliedJobs;
        $curriculumVitae = $seeker->curriculumVitae()
            ->with(['seekerSkills.skill', 'educations', 'jobExperiences'])
            ->get();

        return response()->json([
            'first_name' => $seeker->first_name,
            'last_name' => $seeker->last_name,
            'email' => $seeker->email,
            'address' => $seeker->address,
            'phonenumber' => $seeker->phonenumber,
            'saved_jobs' => $savedJobs,
            'applied_jobs' => $appliedJobs,
            'curriculum_vitae' => $curriculumVitae,
        ]);
    }
}
