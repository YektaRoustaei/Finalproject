<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AppliedJob;
use App\Models\SavedJob;

class JobStatus extends Controller
{
    public function getJobStatus($jobId)
    {
        // Get the authenticated seeker's ID
        $seekerId = Auth::guard('sanctum')->id();

        // Check if the job has been applied for
        $isApplied = AppliedJob::where('job_id', $jobId)
            ->where('seeker_id', $seekerId)
            ->exists();

        // Check if the job has been saved
        $isSaved = SavedJob::where('job_id', $jobId)
            ->where('seeker_id', $seekerId)
            ->exists();

        // Return the status as JSON
        return response()->json([
            'applied_before' => $isApplied,
            'saved_before' => $isSaved,
        ]);
    }
}
