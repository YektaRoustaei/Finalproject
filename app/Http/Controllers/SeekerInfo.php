<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SeekerInfo extends Controller
{
    /**
     * Handle the request to get seeker information.
     *
     * @return JsonResponse
     */
    public function __invoke(): JsonResponse
    {
        // Fetch the authenticated seeker
        $seeker = Auth::guard('sanctum')->user();

        // Fetch saved jobs, applied jobs, and CVs with related details
        $savedJobs = $seeker->savedJobs;
        $appliedJobs = $seeker->appliedJobs->map(function ($appliedJob) {
            return [
                'job_id' => $appliedJob->job_id,
                'status' => $appliedJob->status,
                'curriculum_vitae_id' => $appliedJob->curriculum_vitae_id,
                'cover_letter_id' => $appliedJob->cover_letter_id,
                'created_at' => $appliedJob->created_at->toIso8601String(), // Include created_at in ISO 8601 format
            ];
        });

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
