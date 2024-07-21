<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ProviderInfo extends Controller
{
    public function __invoke(): JsonResponse
    {
        $provider = Auth::guard('sanctum')->user()->load('city');

        // Get the number of job postings
        $jobCount = $provider->jobPostings()->count();

        return response()->json([
            'company_name' => $provider->company_name,
            'description' => $provider->description,
            'address' => $provider->city->city_name, // Include the city name instead of city ID
            'telephone' => $provider->telephone,
            'email' => $provider->email,
            'id' => $provider->id,
            'job_count' => $jobCount, // Include the job count in the response
            'jobs' => $provider->jobPostings()->get(['id', 'title', 'description', 'salary', 'type']),
        ]);
    }
}
