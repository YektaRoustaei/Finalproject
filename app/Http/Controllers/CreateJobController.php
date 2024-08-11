<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\JobPosting;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateJobController extends Controller
{
    public function store(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'salary' => 'required|numeric',
            'type' => 'required|string',
            'expiry_date' => 'nullable|date',
            'cover_letter' => 'required|boolean',
            'question' => 'required|boolean',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'jobskills' => 'nullable|array',
            'jobskills.*' => 'string', // Validate skill names
        ]);

        DB::beginTransaction();

        try {
            // Create the job posting
            $job = auth('sanctum')->user()->jobPostings()->create([
                'title' => $request->title,
                'description' => $request->description,
                'salary' => $request->salary,
                'type' => $request->type,
                'expiry_date' => $request->expiry_date,
                'cover_letter' => $request->cover_letter,
                'question' => $request->question,
            ]);

            // Attach categories to the job
            if ($request->has('category_ids')) {
                $job->categories()->sync($request->category_ids);
            }

            // Handle jobskills and link them to the job posting
            if ($request->has('jobskills')) {
                $skillIds = [];
                foreach ($request->jobskills as $skillName) {
                    $skill = Skill::firstOrCreate(['name' => $skillName]);
                    $skillIds[] = $skill->id;
                }
                $job->skills()->sync($skillIds);
            }

            DB::commit();

            // Load relationships for response
            $job = $job->load('categories', 'provider', 'skills');

            return response()->json([
                'message' => 'Job added',
                'job_id' => $job->id,
                'job' => $job
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Job creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Job creation failed',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        // Validate incoming request
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'salary' => 'sometimes|required|numeric',
            'type' => 'sometimes|required|string',
            'expiry_date' => 'nullable|date',
            'cover_letter' => 'sometimes|required|boolean',
            'question' => 'sometimes|required|boolean',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'jobskills' => 'nullable|array',
            'jobskills.*' => 'string', // Validate skill names
        ]);

        DB::beginTransaction();

        try {
            $job = JobPosting::findOrFail($id);
            $job->update($request->only([
                'title', 'description', 'salary', 'type', 'expiry_date', 'cover_letter', 'question'
            ]));

            // Update categories if provided
            if ($request->has('category_ids')) {
                $validCategoryIds = Category::whereIn('id', $request->category_ids)->pluck('id')->toArray();
                $job->categories()->sync($validCategoryIds); // Sync the categories
            }

            // Update skills if provided
            if ($request->has('jobskills')) {
                $validSkills = [];
                foreach ($request->jobskills as $skillName) {
                    $skill = Skill::firstOrCreate(['name' => $skillName]);
                    $validSkills[] = $skill->id;
                }
                $job->skills()->sync($validSkills); // Sync the skills
            }

            DB::commit();

            $job = $job->load('categories', 'skills'); // Reload relationships for the response

            return response()->json([
                'message' => 'Job updated',
                'job' => $job
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Job update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Job update failed',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $job = JobPosting::findOrFail($id);

            // Detach categories and skills
            $job->categories()->detach();
            $job->skills()->detach();

            // Delete the job posting
            $job->delete();

            DB::commit();

            return response()->json([
                'message' => 'Job deleted'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Job deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Job deletion failed',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
