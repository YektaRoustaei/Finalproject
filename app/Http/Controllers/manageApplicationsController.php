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
        $provider = Auth::guard('sanctum')->user();

        if (!$provider) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $jobId = $request->input('job_id');

        if (!$jobId) {
            return response()->json(['error' => 'Job ID is required'], 400);
        }

        $jobPostings = JobPosting::where('provider_id', $provider->id)->pluck('id');

        if (!in_array($jobId, $jobPostings->toArray())) {
            return response()->json(['error' => 'Unauthorized access to the job posting'], 403);
        }

        $appliedJobs = AppliedJob::with(['curriculumVitae' => function($query) {
            $query->with(['seekerSkills.skill', 'jobExperiences', 'educations']);
        }, 'coverLetter', 'seeker'])
            ->where('job_id', $jobId)
            ->get();

        if ($appliedJobs->isEmpty()) {
            return response()->json(['message' => 'No applications found for this job'], 404);
        }

        $result = $appliedJobs->map(function ($appliedJob) {
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

        return response()->json($result);
    }


    public function accept(Request $request)
    {
        $provider = Auth::guard('sanctum')->user();
        if (!$provider) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'applied_job_id' => 'required|integer|exists:applied_jobs,id',
        ]);

        $appliedJobId = $validated['applied_job_id'];

        $appliedJob = AppliedJob::find($appliedJobId);

        if (!$appliedJob) {
            return response()->json(['error' => 'Applied job not found'], 404);
        }

        if ($appliedJob->jobPosting && $appliedJob->jobPosting->provider_id !== $provider->id) {
            return response()->json(['error' => 'Unauthorized to update this application'], 403);
        }

        $appliedJob->status = 'accepted';
        $appliedJob->save();

        return response()->json(['message' => 'Job application accepted']);
    }

    public function reject(Request $request)
    {
        $provider = Auth::guard('sanctum')->user();
        if (!$provider) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'applied_job_id' => 'required|integer|exists:applied_jobs,id',
        ]);

        $appliedJobId = $validated['applied_job_id'];

        $appliedJob = AppliedJob::find($appliedJobId);

        if (!$appliedJob) {
            return response()->json(['error' => 'Applied job not found'], 404);
        }

        if ($appliedJob->jobPosting && $appliedJob->jobPosting->provider_id !== $provider->id) {
            return response()->json(['error' => 'Unauthorized to update this application'], 403);
        }

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
        $provider = Auth::guard('sanctum')->user();
        if (!$provider) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'applied_job_id' => 'required|integer|exists:applied_jobs,id',
        ]);

        $appliedJobId = $validated['applied_job_id'];

        $appliedJob = AppliedJob::find($appliedJobId);

        if (!$appliedJob) {
            return response()->json(['error' => 'Applied job not found'], 404);
        }

        if ($appliedJob->jobPosting && $appliedJob->jobPosting->provider_id !== $provider->id) {
            return response()->json(['error' => 'Unauthorized to update this application'], 403);
        }

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
        $provider = Auth::guard('sanctum')->user();
        if (!$provider) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'applied_job_id' => 'required|integer|exists:applied_jobs,id',
        ]);

        $appliedJobId = $validated['applied_job_id'];

        $appliedJob = AppliedJob::find($appliedJobId);

        if (!$appliedJob) {
            return response()->json(['error' => 'Applied job not found'], 404);
        }

        if ($appliedJob->jobPosting && $appliedJob->jobPosting->provider_id !== $provider->id) {
            return response()->json(['error' => 'Unauthorized to update this application'], 403);
        }

        $appliedJob->status = 'final_step';
        $appliedJob->save();

        return response()->json(['message' => 'Job application moved to the final step']);
    }


}
