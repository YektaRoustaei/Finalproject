<?php

namespace App\Http\Controllers;

use App\Models\JobPosting;
use Illuminate\Http\Request;

class JobList extends Controller
{
    public function jobList()
    {
        try {
            // Eager load the provider relationship
            $jobs = JobPosting::with('provider')->get();
            return response()->json($jobs);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unable to fetch job listings.'], 500);
        }
    }
}
