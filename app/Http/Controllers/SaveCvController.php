<?php

namespace App\Http\Controllers;

use App\Models\Future;
use Illuminate\Http\Request;

class SaveCvController extends Controller
{
    /**
     * Save a CV to the futures table.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(Request $request)
    {
        $validatedData = $request->validate([
            'curriculum_vitae_id' => 'required|exists:curriculum_vitaes,id',
            'provider_id' => 'required|exists:providers,id',
            'seeker_id' => 'required|exists:seekers,id',
        ]);

        $future = Future::create($validatedData);

        return response()->json([
            'message' => 'CV saved successfully!',
            'data' => $future
        ], 201);
    }

    /**
     * Unsave a CV from the futures table.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unsave(Request $request)
    {
        $validatedData = $request->validate([
            'curriculum_vitae_id' => 'required|exists:curriculum_vitaes,id',
            'provider_id' => 'required|exists:providers,id',
            'seeker_id' => 'required|exists:seekers,id',
        ]);

        $future = Future::where($validatedData)->first();

        if ($future) {
            $future->delete();

            return response()->json([
                'message' => 'CV unsaved successfully!',
            ], 200);
        }

        return response()->json([
            'message' => 'No matching record found to unsave.',
        ], 404);
    }

    /**
     * Get all saved CVs based on provider_id with detailed curriculum vitae information.
     *
     * @param int $provider_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllByProvider($provider_id)
    {
        // Validate provider_id exists
        $providerExists = \App\Models\Provider::where('id', $provider_id)->exists();

        if (!$providerExists) {
            return response()->json([
                'message' => 'Provider not found.',
            ], 404);
        }

        // Fetch all records matching the provider_id with curriculum vitae details
        $futures = Future::with([
            'curriculumVitae.seeker',
            'curriculumVitae.seekerSkills.skill', // Load related skills
            'curriculumVitae.educations',
            'curriculumVitae.jobExperiences'
        ])
            ->where('provider_id', $provider_id)
            ->get();

        // Format the response to include skill names
        $response = $futures->map(function ($future) {
            $cv = $future->curriculumVitae;

            $skills = $cv->seekerSkills->map(function ($seekerSkill) {
                return $seekerSkill->skill->name;
            });

            return [
                'curriculum_vitae_id' => $cv->id,
                'seeker' => $cv->seeker,
                'skills' => $skills,
                'educations' => $cv->educations,
                'job_experiences' => $cv->jobExperiences,
            ];
        });

        return response()->json([
            'message' => 'Fetched all saved CVs successfully!',
            'data' => $response
        ], 200);
    }
}
