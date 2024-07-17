<?php

namespace App\Http\Middleware\Seeker\Job;

use App\Models\SavedJob;
use Closure;
use Illuminate\Http\Request;

class EnsureSeekerJobNotSavedBefore
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure $next
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $seeker_id = auth('sanctum')->id();
        $savedJob = SavedJob::where([
            'job_id' => $request->job_id,
            'seeker_id' => $seeker_id,
        ])->first();

        if ($savedJob) {
            return response()->json(['message' => 'Job has already been saved'], 409);
        }

        return $next($request);
    }
}
