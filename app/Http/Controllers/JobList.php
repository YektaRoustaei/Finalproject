<?php

namespace App\Http\Controllers;

use App\Models\JobPosting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class JobList extends Controller
{
    public function jobList()
    {
        try {
            // Include 'categories' relationship here
            $jobs = JobPosting::with(['provider.city', 'jobskills.skill', 'categories'])->get();
            return response()->json($jobs);
        } catch (\Exception $e) {
            Log::error('Error fetching job listings: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch job listings.'], 500);
        }
    }

    public function show($id)
    {
        try {
            // Include 'categories' relationship here
            $job = JobPosting::with(['provider.city', 'jobskills.skill', 'categories'])
                ->findOrFail($id);

            return response()->json($job);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Job not found: ' . $e->getMessage());
            return response()->json(['error' => 'Job not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Error fetching job details: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch job details.'], 500);
        }
    }
}
