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
}
