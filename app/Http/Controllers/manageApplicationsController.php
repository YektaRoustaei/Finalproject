<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AppliedJob;
use App\Models\JobPosting;

class manageApplicationsController extends Controller
{
    /**
     * Show details of applied jobs based on the provided job ID.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function showAppliedJobs(Request $request)
    {
        // Fetch the authenticated user using Sanctum
        $provider = Auth::guard('sanctum')->user();

        // Check if the user is authenticated
        if (!$provider) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Get the job ID from the request body
        $jobId = $request->input('job_id');

        // Validate the job ID
        if (!$jobId) {
            return response()->json(['error' => 'Job ID is required'], 400);
        }

        // Retrieve the job postings created by the provider
        $jobPostings = JobPosting::where('provider_id', $provider->id)->pluck('id');

        // Check if the provided job ID belongs to the authenticated provider
        if (!in_array($jobId, $jobPostings->toArray())) {
            return response()->json(['error' => 'Unauthorized access to the job posting'], 403);
        }

        // Retrieve applied jobs for the specified job ID
        $appliedJobs = AppliedJob::with(['curriculumVitae' => function($query) {
            $query->with(['seekerSkills.skill', 'jobExperiences', 'educations']);
        }, 'coverLetter', 'seeker'])
            ->where('job_id', $jobId)
            ->get();

        if ($appliedJobs->isEmpty()) {
            return response()->json(['message' => 'No applications found for this job'], 404);
        }

        // Map through the applied jobs to include related information
        $result = $appliedJobs->map(function ($appliedJob) {
            // Fetch the CV including its related seekerSkills, job experiences, and educations
            $curriculumVitae = $appliedJob->curriculumVitae;
            $seekerSkills = $curriculumVitae ? $curriculumVitae->seekerSkills->map(function ($seekerSkill) {
                return $seekerSkill->skill ? $seekerSkill->skill->name : null;
            })->filter()->values() : [];

            return [
                'applied_job_id' => $appliedJob->id,
                'cv' => $curriculumVitae,
                'skills' => $seekerSkills, // Include the skill names here
                'job_experiences' => $curriculumVitae ? $curriculumVitae->jobExperiences : [],
                'educations' => $curriculumVitae ? $curriculumVitae->educations : [],
                'cover_letter' => $appliedJob->coverLetter,
                'seeker' => $appliedJob->seeker,
                'status' => $appliedJob->status,
            ];
        });

        // Return the detailed response
        return response()->json($result);
    }


    public function accept(Request $request)
    {
        // Ensure the provider is authenticated
        $provider = Auth::guard('sanctum')->user();
        if (!$provider) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Validate the input
        $validated = $request->validate([
            'applied_job_id' => 'required|integer|exists:applied_jobs,id',
        ]);

        $appliedJobId = $validated['applied_job_id'];

        // Find the applied job by ID
        $appliedJob = AppliedJob::find($appliedJobId);

        if (!$appliedJob) {
            return response()->json(['error' => 'Applied job not found'], 404);
        }

        // Optional: Check if the jobPosting belongs to the provider
        // Assuming there's a relationship from AppliedJob to JobPosting
        if ($appliedJob->jobPosting && $appliedJob->jobPosting->provider_id !== $provider->id) {
            return response()->json(['error' => 'Unauthorized to update this application'], 403);
        }

        // Update the status to 'accepted'
        $appliedJob->status = 'accepted';
        $appliedJob->save();

        return response()->json(['message' => 'Job application accepted']);
    }

    public function reject(Request $request)
    {
        // Ensure the provider is authenticated
        $provider = Auth::guard('sanctum')->user();
        if (!$provider) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Validate the input
        $validated = $request->validate([
            'applied_job_id' => 'required|integer|exists:applied_jobs,id',
        ]);

        $appliedJobId = $validated['applied_job_id'];

        // Find the applied job by ID
        $appliedJob = AppliedJob::find($appliedJobId);

        if (!$appliedJob) {
            return response()->json(['error' => 'Applied job not found'], 404);
        }

        // Optionally, check if the job belongs to the provider
        // Assuming there's a relationship from AppliedJob to JobPosting
        if ($appliedJob->jobPosting && $appliedJob->jobPosting->provider_id !== $provider->id) {
            return response()->json(['error' => 'Unauthorized to update this application'], 403);
        }

        // Update the status to 'rejected'
        $appliedJob->status = 'rejected';
        $appliedJob->save();

        return response()->json(['message' => 'Job application rejected']);
    }

    /**
     * Move the job application to the next step.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function moveToNextStep(Request $request)
    {
        // Ensure the provider is authenticated
        $provider = Auth::guard('sanctum')->user();
        if (!$provider) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Validate the input
        $validated = $request->validate([
            'applied_job_id' => 'required|integer|exists:applied_jobs,id',
        ]);

        $appliedJobId = $validated['applied_job_id'];

        // Find the applied job by ID
        $appliedJob = AppliedJob::find($appliedJobId);

        if (!$appliedJob) {
            return response()->json(['error' => 'Applied job not found'], 404);
        }

        if ($appliedJob->jobPosting && $appliedJob->jobPosting->provider_id !== $provider->id) {
            return response()->json(['error' => 'Unauthorized to update this application'], 403);
        }

        // Update the status to 'next_step'
        $appliedJob->status = 'next_step';
        $appliedJob->save();

        return response()->json(['message' => 'Job application moved to the next step']);
    }

    /**
     * Move the job application to the final step.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function moveToFinalStep(Request $request)
    {
        // Ensure the provider is authenticated
        $provider = Auth::guard('sanctum')->user();
        if (!$provider) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Validate the input
        $validated = $request->validate([
            'applied_job_id' => 'required|integer|exists:applied_jobs,id',
        ]);

        $appliedJobId = $validated['applied_job_id'];

        // Find the applied job by ID
        $appliedJob = AppliedJob::find($appliedJobId);

        if (!$appliedJob) {
            return response()->json(['error' => 'Applied job not found'], 404);
        }

        if ($appliedJob->jobPosting && $appliedJob->jobPosting->provider_id !== $provider->id) {
            return response()->json(['error' => 'Unauthorized to update this application'], 403);
        }

        // Update the status to 'final_step'
        $appliedJob->status = 'final_step';
        $appliedJob->save();

        return response()->json(['message' => 'Job application moved to the final step']);
    }


}
