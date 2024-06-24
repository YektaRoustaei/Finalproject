<?php

namespace App\Http\Middleware\Seeker\Job;

use App\Models\AppliedJob;

class EnsureSeekerApplyIsNotDuplicated
{
    public function handle($request, $next)
    {
        $seeker = auth('sanctum')->user();
        $jobId = $request->job_id;
        $appliedJob = AppliedJob::query()->where('seeker_id', $seeker->id)->where('job_id', $jobId)->first();
        if ($appliedJob) {
            return response()->json(['error' => 'You have already applied for this job'], 400);
        }
        return $next($request);
    }
}
