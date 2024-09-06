<?php

namespace App\Http\Controllers;

use App\Models\CurriculumVitae;
use App\Models\SeekerSkill;
use App\Models\Skill;
use App\Models\Education;
use App\Models\JobExperience;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CreateCVController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'skills' => 'array|nullable',
            'skills.*.id' => 'nullable|integer|exists:skills,id',
            'skills.*.name' => 'nullable|string|max:255',
            'educations' => 'array|nullable',
            'educations.*.degree' => 'required_with:educations.*.id|string|max:255',
            'educations.*.institution' => 'required_with:educations.*.id|string|max:255',
            'educations.*.field_of_study' => 'nullable|string|max:255',
            'educations.*.start_date' => 'required_with:educations.*.id|date',
            'educations.*.end_date' => 'nullable|date|after_or_equal:educations.*.start_date',
            'job_experiences' => 'array|nullable',
            'job_experiences.*.position' => 'required_with:job_experiences.*.id|string|max:255',
            'job_experiences.*.company_name' => 'required_with:job_experiences.*.id|string|max:255',
            'job_experiences.*.start_date' => 'required_with:job_experiences.*.id|date',
            'job_experiences.*.end_date' => 'nullable|date|after_or_equal:job_experiences.*.start_date',
            'job_experiences.*.description' => 'nullable|string',
        ]);

        $user = Auth::guard('sanctum')->user();

        $curriculumVitae = $user->curriculumVitae()->create([
            'seeker_id' => $user->id,
        ]);

        if ($request->has('skills')) {
            foreach ($request->skills as $skillData) {
                $skill = Skill::firstOrCreate(['name' => $skillData['name']]);
                SeekerSkill::create([
                    'curriculum_vitae_id' => $curriculumVitae->id,
                    'skill_id' => $skill->id,
                ]);
            }
        }

        if ($request->has('educations')) {
            foreach ($request->educations as $educationData) {
                // Ensure end_date is null for ongoing education
                $educationData['end_date'] = $educationData['end_date'] ?: null;
                $curriculumVitae->educations()->create($educationData);
            }
        }

        if ($request->has('job_experiences')) {
            foreach ($request->job_experiences as $jobExperienceData) {
                $jobExperienceData['end_date'] = $jobExperienceData['end_date'] ?: null;
                $curriculumVitae->jobExperiences()->create($jobExperienceData);
            }
        }

        return response()->json([
            'message' => 'Curriculum Vitae created',
            'curriculum_vitae' => $curriculumVitae->load(['seekerSkills', 'educations', 'jobExperiences']),
        ], 201);
    }

    public function update(Request $request)
    {
        $request->validate([
            'cv_id' => 'required|integer|exists:curriculum_vitaes,id',
            'skills' => 'array|nullable',
            'skills.*.id' => 'nullable|integer|exists:skills,id',
            'skills.*.name' => 'nullable|string|max:255',
            'educations' => 'array|nullable',
            'educations.*.id' => 'nullable|integer|exists:education,id',
            'educations.*.degree' => 'required_with:educations.*.id|string|max:255',
            'educations.*.institution' => 'required_with:educations.*.id|string|max:255',
            'educations.*.field_of_study' => 'nullable|string|max:255',
            'educations.*.start_date' => 'required_with:educations.*.id|date',
            'educations.*.end_date' => 'nullable|date|after_or_equal:educations.*.start_date',
            'job_experiences' => 'array|nullable',
            'job_experiences.*.id' => 'nullable|integer|exists:job_experiences,id',
            'job_experiences.*.position' => 'required_with:job_experiences.*.id|string|max:255',
            'job_experiences.*.company_name' => 'required_with:job_experiences.*.id|string|max:255',
            'job_experiences.*.start_date' => 'required_with:job_experiences.*.id|date',
            'job_experiences.*.end_date' => 'nullable|date|after_or_equal:job_experiences.*.start_date',
            'job_experiences.*.description' => 'nullable|string',
        ]);

        $user = Auth::guard('sanctum')->user();
        $cvId = $request->input('cv_id');
        $curriculumVitae = CurriculumVitae::findOrFail($cvId);

        if ($curriculumVitae->seeker_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($request->has('skills')) {
            SeekerSkill::where('curriculum_vitae_id', $curriculumVitae->id)->delete();
            foreach ($request->skills as $skillData) {
                $skill = Skill::firstOrCreate(['name' => $skillData['name']]);
                SeekerSkill::create([
                    'curriculum_vitae_id' => $curriculumVitae->id,
                    'skill_id' => $skill->id,
                ]);
            }
        }

        if ($request->has('educations')) {
            foreach ($request->educations as $educationData) {
                if (isset($educationData['id'])) {
                    $education = $curriculumVitae->educations()->find($educationData['id']);
                    if ($education) {
                        $educationData['end_date'] = $educationData['end_date'] ?: null;
                        $education->update($educationData);
                    }
                } else {
                    $educationData['end_date'] = $educationData['end_date'] ?: null;
                    $curriculumVitae->educations()->create($educationData);
                }
            }
        }

        if ($request->has('job_experiences')) {
            foreach ($request->job_experiences as $jobExperienceData) {
                if (isset($jobExperienceData['id'])) {
                    $jobExperience = $curriculumVitae->jobExperiences()->find($jobExperienceData['id']);
                    if ($jobExperience) {
                        $jobExperienceData['end_date'] = $jobExperienceData['end_date'] ?: null;
                        $jobExperience->update($jobExperienceData);
                    }
                } else {
                    $jobExperienceData['end_date'] = $jobExperienceData['end_date'] ?: null;
                    $curriculumVitae->jobExperiences()->create($jobExperienceData);
                }
            }
        }

        return response()->json([
            'message' => 'Curriculum Vitae updated',
            'curriculum_vitae' => $curriculumVitae->load(['seekerSkills', 'educations', 'jobExperiences']),
        ], 200);
    }
    public function remove(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:curriculum_vitaes,id',
        ]);

        $curriculumVitaeId = $request->input('id');
        $curriculumVitae = CurriculumVitae::findOrFail($curriculumVitaeId);
        $user = Auth::guard('sanctum')->user();

        if ($curriculumVitae->seeker_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        SeekerSkill::where('curriculum_vitae_id', $curriculumVitae->id)->delete();

        $curriculumVitae->educations()->delete();

        $curriculumVitae->jobExperiences()->delete();

        $curriculumVitae->delete();

        return response()->json([
            'message' => 'Curriculum Vitae removed',
        ], 200);
    }

    public function getCurriculumVitae()
    {
        $user = Auth::guard('sanctum')->user();

        $curriculumVitae = CurriculumVitae::where('seeker_id', $user->id)->first();

        if (!$curriculumVitae) {
            return response()->json([
                'message' => 'No Curriculum Vitae found for this user.',
            ], 404);
        }

        return response()->json([
            'message' => 'Curriculum Vitae found',
            'curriculum_vitae_id' => $curriculumVitae->id,
        ]);
    }


}
