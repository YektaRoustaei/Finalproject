<?php

namespace App\Http\Controllers;

use App\Models\CurriculumVitae;
use App\Models\Seeker;
use App\Models\SeekerSkill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CreateCVController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'skills' => 'array',
            'educations' => 'array',
            'educations.*.degree' => 'required|string|max:255',
            'educations.*.institution' => 'required|string|max:255',
            'educations.*.field_of_study' => 'nullable|string|max:255',  // Added field_of_study validation
            'educations.*.start_date' => 'required|date',  // Added start_date validation
            'educations.*.end_date' => 'nullable|date',  // Added end_date validation
            'job_experiences' => 'array',
            'job_experiences.*.position' => 'required|string|max:255',
            'job_experiences.*.company_name' => 'required|string|max:255',
            'job_experiences.*.start_date' => 'required|date',
            'job_experiences.*.end_date' => 'nullable|date',
            'job_experiences.*.description' => 'nullable|string',  // Added description validation
        ]);

        $user = Auth::guard('sanctum')->user();

        // Create CurriculumVitae for the Seeker
        $curriculumVitae = $user->curriculumVitae()->create([
            'seeker_id' => $user->id,
        ]);

        // Save related skills
        if ($request->has('skills')) {
            foreach ($request->skills as $skillData){
                SeekerSkill::query()->create(['curriculum_vitae_id' => $curriculumVitae->id, 'skill_id' => $skillData['id']]);
            }
        }

        // Save related educations
        if ($request->has('educations')) {
            foreach ($request->educations as $educationData) {
                $curriculumVitae->educations()->create($educationData);
            }
        }

        // Save related job experiences
        if ($request->has('job_experiences')) {
            foreach ($request->job_experiences as $jobExperienceData) {
                $curriculumVitae->jobExperiences()->create($jobExperienceData);
            }
        }

        // Return response
        return response()->json([
            'message' => 'Curriculum Vitae created',
            'curriculum_vitae' => $curriculumVitae->load(['seekerSkills', 'educations', 'jobExperiences']),
        ], 201);
    }
}
