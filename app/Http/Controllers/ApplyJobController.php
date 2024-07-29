<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AppliedJob;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class ApplyJobController extends Controller
{
    /**
     * Apply for a job.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apply(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'job_id' => 'required|integer|exists:job_postings,id', // Validate job_id
            'curriculum_vitae_id' => 'nullable|integer|exists:curriculum_vitaes,id', // Validate CV ID if provided
            'cover_letter_id' => 'nullable|integer|exists:cover_letters,id', // Validate Cover Letter ID if provided
        ]);

        // Check if user is authenticated
        if (!Auth::guard('sanctum')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Retrieve the seeker_id from the authenticated user
        $seeker_id = Auth::guard('sanctum')->id();

        try {
            // Create the applied job record with status set to 'hold'
            $appliedJob = AppliedJob::create([
                'job_id' => $validatedData['job_id'],
                'seeker_id' => $seeker_id,
                'status' => 'hold', // Set status to 'hold'
                'curriculum_vitae_id' => $validatedData['curriculum_vitae_id'] ?? null, // Save CV ID if provided
                'cover_letter_id' => $validatedData['cover_letter_id'] ?? null, // Save Cover Letter ID if provided
            ]);

            // Return success response with applied job data
            return response()->json([
                'message' => 'Job applied successfully',
                'appliedJob' => $appliedJob
            ], 201);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Resource not found'], 404);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            // Handle any exceptions (e.g., database errors)
            report($e); // Log the exception for debugging
            return response()->json(['error' => 'Failed to apply for job'], 500);
        }
    }
}
