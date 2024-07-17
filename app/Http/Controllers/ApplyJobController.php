<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AppliedJob;
use Illuminate\Support\Facades\App;

class ApplyJobController extends Controller
{
    public function apply(Request $request)
    {
        // Check if user is authenticated
        if (!auth('sanctum')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Retrieve the seeker_id from authenticated user
        $seeker_id = auth('sanctum')->id();

        try {
            // Create the applied job record
            $appliedJob = AppliedJob::create([
                'job_id' => $request->job_id,
                'seeker_id' => $seeker_id,
            ]);

            // Return success response with applied job data
            return response()->json(['message' => 'Job applied successfully', 'appliedJob' => $appliedJob], 200);

        } catch (\Exception $e) {
            // Handle any exceptions (e.g., database errors)
            report($e);
            return response()->json(['error' => 'Failed to apply for job'], 500);
        }
    }


}
