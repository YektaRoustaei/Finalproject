<?php

namespace App\Http\Controllers;

use App\Models\JobCategory;
use App\Models\JobPosting;
use App\Models\JobSkill;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Category;


class CreateJobController extends Controller
{
    public function store(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'salary' => 'required|string',
            'type' => 'required|string',
            'category_ids' => 'array',
            'jobskills' => 'array',
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
            ]);

            // Attach categories to the job
            if ($request->has('category_ids')) {
                foreach ($request->category_ids as $categoryId) {
                    JobCategory::create([
                        'job_id' => $job->id,
                        'category_id' => $categoryId
                    ]);
                }
            }

            // Handle jobskills and link them to the job posting
            if ($request->has('jobskills')) {
                foreach ($request->jobskills as $skillName) {
                    // Check if the skill exists, if not create it
                    $skill = Skill::firstOrCreate(['name' => $skillName]);

                    // Link the skill to the job posting
                    JobSkill::create([
                        'job_posting_id' => $job->id,
                        'skill_id' => $skill->id
                    ]);
                }
            }

            DB::commit();

            // Load relationships for response
            $job = $job->load('categories', 'provider'); // Ensure these relationships are defined correctly

            return response()->json([
                'message' => 'Job added',
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
            'salary' => 'sometimes|required|string',
            'type' => 'sometimes|required|string',
            'category_ids' => 'sometimes|array',
            'jobskills' => 'sometimes|array',
            'jobskills.*' => 'string', // Validate skill names
        ]);

        DB::beginTransaction();

        try {
            $job = JobPosting::findOrFail($id);
            $job->update($request->only(['title', 'description', 'salary', 'type']));

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

            // Remove categories and skills
            $job->categories()->delete();
            $job->skills()->delete();

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
