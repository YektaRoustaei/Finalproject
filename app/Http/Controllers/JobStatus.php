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
        $seekerId = Auth::guard('sanctum')->id();

        $isApplied = AppliedJob::where('job_id', $jobId)
            ->where('seeker_id', $seekerId)
            ->exists();

        $isSaved = SavedJob::where('job_id', $jobId)
            ->where('seeker_id', $seekerId)
            ->exists();

        return response()->json([
            'applied_before' => $isApplied,
            'saved_before' => $isSaved,
        ]);
    }
}
