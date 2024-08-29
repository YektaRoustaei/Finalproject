<?php

namespace App\Http\Controllers;

use App\Models\JobPosting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class JobList extends Controller
{
    public function jobList(Request $request)
    {
        try {
            $searchTerm = $request->input('search_term');

            // Initialize the query builder and include the provider and city relationships
            $query = JobPosting::with(['provider.city']);

            if ($searchTerm) {
                $query->where(function($q) use ($searchTerm) {
                    // Search in job title
                    $q->where('title', 'like', '%' . $searchTerm . '%')
                        // Search in job type
                        ->orWhere('type', 'like', '%' . $searchTerm . '%')
                        // Search in provider company name
                        ->orWhereHas('provider', function($q) use ($searchTerm) {
                            $q->where('company_name', 'like', '%' . $searchTerm . '%');
                        });
                });
            }

            // Execute the query and get the results
            $jobs = $query->get();

            return response()->json($jobs);
        } catch (\Exception $e) {
            Log::error('Error fetching job listings: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch job listings.'], 500);
        }
    }

    public function show($id)
    {
        try {
            // Include 'provider' and 'city' relationship here
            $job = JobPosting::with(['provider.city'])
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
