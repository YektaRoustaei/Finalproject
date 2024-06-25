<?php

namespace App\Http\Middleware\Seeker\Job;

use App\Models\SavedJob;
use Closure;
use Illuminate\Http\Request;

class EnsureSeekerJobSavedBefore
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure $next
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $seeker = auth()->user();
        $savedJob = SavedJob::where([
            'job_id' => $request->job_id,
            'seeker_id' => $seeker->id,
        ])->first();

        if (!$savedJob) {
            return response()->json(['message' => 'Job not saved before'], 409);
        }

        return $next($request);
    }
}
