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
            $jobs = JobPosting::with(['provider', 'jobskills.skill'])->get();
            return response()->json($jobs);
        } catch (\Exception $e) {
            Log::error('Error fetching job listings: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch job listings.'], 500);
        }
    }
}

