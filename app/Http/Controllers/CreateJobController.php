<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CategoryJob;
use App\Models\JobCategory;
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
        ]);
            $job = auth('sanctum')->user()->JobPostings()->create([
                'title' => $request->title,
                'description' => $request->description,
                'salary' => $request->salary,
                'type' => $request->type,
                'location' => $request->location,
            ]);
            foreach (request('category_ids') as $categoryId){
                JobCategory::query()->create([
                   'job_id' => $job->id,
                   'category_id' => $categoryId
                ]);
            }

            // Return a response with a message and the job data
            return response()->json([
                'message' => 'Job added',
                'job' => $job
            ], 201);
        }
}
