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

        // Load job postings with job skills, skills, and categories
        $jobPostings = $provider->jobPostings()->with(['jobskills.skill', 'categories'])->get([
            'id',
            'title',
            'description',
            'salary',
            'type'
        ]);


        return response()->json([
            'company_name' => $provider->company_name,
            'description' => $provider->description,
            'address' => $provider->city->city_name,
            'telephone' => $provider->telephone,
            'email' => $provider->email,
            'id' => $provider->id,
            'job_count' => $jobCount,
            'jobs' => $jobPostings->map(function ($jobPosting) {

                return [
                    'id' => $jobPosting->id,
                    'title' => $jobPosting->title,
                    'description' => $jobPosting->description,
                    'salary' => $jobPosting->salary,
                    'type' => $jobPosting->type,
                    'skills' => $jobPosting->jobskills->map(function ($jobSkill) {
                        return $jobSkill->skill->name;
                    }),
                    'categories' => $jobPosting->categories->map(function ($category) {
                        return $category->title;
                    })
                ];
            }),
        ]);
    }
}
