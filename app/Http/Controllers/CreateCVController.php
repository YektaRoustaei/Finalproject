<?php

namespace App\Http\Controllers;

use App\Models\CurriculumVitae;
use App\Models\SeekerSkill;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CreateCVController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'skills' => 'array',
            'skills.*.id' => 'nullable|integer|exists:skills,id',
            'skills.*.name' => 'required|string|max:255',
            'educations' => 'array',
            'educations.*.degree' => 'required|string|max:255',
            'educations.*.institution' => 'required|string|max:255',
            'educations.*.field_of_study' => 'nullable|string|max:255',
            'educations.*.start_date' => 'required|date',
            'educations.*.end_date' => 'nullable|date',
            'job_experiences' => 'array',
            'job_experiences.*.position' => 'required|string|max:255',
            'job_experiences.*.company_name' => 'required|string|max:255',
            'job_experiences.*.start_date' => 'required|date',
            'job_experiences.*.end_date' => 'nullable|date',
            'job_experiences.*.description' => 'nullable|string',
        ]);

        $user = Auth::guard('sanctum')->user();

        // Create CurriculumVitae for the Seeker
        $curriculumVitae = $user->curriculumVitae()->create([
            'seeker_id' => $user->id,
        ]);

        // Save related skills
        if ($request->has('skills')) {
            foreach ($request->skills as $skillData) {
                $skill = Skill::firstOrCreate(['name' => $skillData['name']]);
                SeekerSkill::create([
                    'curriculum_vitae_id' => $curriculumVitae->id,
                    'skill_id' => $skill->id,
                ]);
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

    public function edit(Request $request, $id)
    {
        $request->validate([
            'skills' => 'array',
            'skills.*.id' => 'nullable|integer|exists:skills,id',
            'skills.*.name' => 'required|string|max:255',
            'educations' => 'array',
            'educations.*.id' => 'nullable|integer|exists:educations,id',
            'educations.*.degree' => 'required|string|max:255',
            'educations.*.institution' => 'required|string|max:255',
            'educations.*.field_of_study' => 'nullable|string|max:255',
            'educations.*.start_date' => 'required|date',
            'educations.*.end_date' => 'nullable|date',
            'job_experiences' => 'array',
            'job_experiences.*.id' => 'nullable|integer|exists:job_experiences,id',
            'job_experiences.*.position' => 'required|string|max:255',
            'job_experiences.*.company_name' => 'required|string|max:255',
            'job_experiences.*.start_date' => 'required|date',
            'job_experiences.*.end_date' => 'nullable|date',
            'job_experiences.*.description' => 'nullable|string',
        ]);

        $curriculumVitae = CurriculumVitae::findOrFail($id);
        $user = Auth::guard('sanctum')->user();

        // Ensure the user owns the CV
        if ($curriculumVitae->seeker_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Update skills
        if ($request->has('skills')) {
            // Delete existing skills
            SeekerSkill::where('curriculum_vitae_id', $curriculumVitae->id)->delete();

            foreach ($request->skills as $skillData) {
                $skill = Skill::firstOrCreate(['name' => $skillData['name']]);
                SeekerSkill::create([
                    'curriculum_vitae_id' => $curriculumVitae->id,
                    'skill_id' => $skill->id,
                ]);
            }
        }

        // Update educations
        if ($request->has('educations')) {
            foreach ($request->educations as $educationData) {
                if (isset($educationData['id'])) {
                    $education = $curriculumVitae->educations()->findOrFail($educationData['id']);
                    $education->update($educationData);
                } else {
                    $curriculumVitae->educations()->create($educationData);
                }
            }
        }

        // Update job experiences
        if ($request->has('job_experiences')) {
            foreach ($request->job_experiences as $jobExperienceData) {
                if (isset($jobExperienceData['id'])) {
                    $jobExperience = $curriculumVitae->jobExperiences()->findOrFail($jobExperienceData['id']);
                    $jobExperience->update($jobExperienceData);
                } else {
                    $curriculumVitae->jobExperiences()->create($jobExperienceData);
                }
            }
        }

        // Return response
        return response()->json([
            'message' => 'Curriculum Vitae updated',
            'curriculum_vitae' => $curriculumVitae->load(['seekerSkills', 'educations', 'jobExperiences']),
        ]);
    }

    public function remove($id)
    {
        $curriculumVitae = CurriculumVitae::findOrFail($id);
        $user = Auth::guard('sanctum')->user();

        // Ensure the user owns the CV
        if ($curriculumVitae->seeker_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Delete related skills
        SeekerSkill::where('curriculum_vitae_id', $curriculumVitae->id)->delete();

        // Delete related educations
        $curriculumVitae->educations()->delete();

        // Delete related job experiences
        $curriculumVitae->jobExperiences()->delete();

        // Delete the CurriculumVitae
        $curriculumVitae->delete();

        // Return response
        return response()->json([
            'message' => 'Curriculum Vitae removed',
        ], 200);
    }

    public function getCurriculumVitae()
    {
        // Get the currently authenticated user
        $user = Auth::guard('sanctum')->user();

        // Fetch the CurriculumVitae associated with the user
        $curriculumVitae = CurriculumVitae::where('seeker_id', $user->id)->first();

        // If no CurriculumVitae is found, return a message
        if (!$curriculumVitae) {
            return response()->json([
                'message' => 'No Curriculum Vitae found for this user.',
            ], 404);
        }

        // Return JSON response including the CurriculumVitae id
        return response()->json([
            'message' => 'Curriculum Vitae found',
            'curriculum_vitae_id' => $curriculumVitae->id,
        ]);
    }
}
