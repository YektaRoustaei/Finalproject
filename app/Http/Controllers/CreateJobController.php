<?php

namespace App\Http\Controllers;

use App\Models\JobCategory;
use App\Models\JobPosting;
use App\Models\JobRequirement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CreateJobController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'salary' => 'required|string',
            'type' => 'required|string',
            'location' => 'nullable|string',
            'category_ids' => 'array',
            'requirements' => 'array',
            'requirements.*' => 'required|string|max:255',
        ]);

        $job = auth('sanctum')->user()->jobPostings()->create([
            'title' => $request->title,
            'description' => $request->description,
            'salary' => $request->salary,
            'type' => $request->type,
            'location' => $request->location,
        ]);

        if ($request->has('category_ids')) {
            foreach ($request->category_ids as $categoryId) {
                JobCategory::create([
                    'job_id' => $job->id,
                    'category_id' => $categoryId
                ]);
            }
        }

        if ($request->has('requirements')) {
            foreach ($request->requirements as $requirement) {
                JobRequirement::create([
                    'job_posting_id' => $job->id,
                    'requirement' => $requirement
                ]);
            }
        }

        return response()->json([
            'message' => 'Job added',
            'job' => $job->load('requirements', 'categories', 'provider')
        ], 201);
    }
}
