<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\Seeker;

class SeekerInfo extends Controller
{
    public function __invoke(): JsonResponse
    {
        $seeker = Auth::guard('sanctum')->user();

        // Fetch saved jobs and applied jobs
        $savedJobs = $seeker->savedJobs;
        $appliedJobs = $seeker->appliedJobs;

        return response()->json([
            'first_name' => $seeker->first_name,
            'last_name' => $seeker->last_name,
            'email' => $seeker->email,
            'address' => $seeker->address,
            'phonenumber' => $seeker->phonenumber,
            'saved_jobs' => $savedJobs,
            'applied_jobs' => $appliedJobs,
        ]);
    }
}
