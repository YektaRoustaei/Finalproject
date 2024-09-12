<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\Seeker; // Ensure you include the Seeker model
use Carbon\Carbon;
use Illuminate\Http\Request; // Ensure this import is included


class SeekerInfo extends Controller
{
    /**
     * Handle the request to get seeker information.
     *
     * @return JsonResponse
     */
    public function __invoke(): JsonResponse
    {
        $seeker = Auth::guard('sanctum')->user()->load('city');

        $savedJobs = $seeker->savedJobs;

        $appliedJobs = $seeker->appliedJobs->map(function ($appliedJob) {
            return [
                'job_id' => $appliedJob->job_id,
                'status' => $appliedJob->status,
                'curriculum_vitae_id' => $appliedJob->curriculum_vitae_id,
                'cover_letter_id' => $appliedJob->cover_letter_id,
                'created_at' => $this->formatDate($appliedJob->created_at), // Include created_at in ISO 8601 format
            ];
        });

        $curriculumVitae = $seeker->curriculumVitae()
            ->with(['seekerSkills.skill', 'educations', 'jobExperiences'])
            ->get()
            ->map(function ($cv) {
                // Apply date transformation for educations and job experiences
                $cv->educations = $cv->educations->map(function ($education) {
                    $education->start_date = $this->formatDate($education->start_date);
                    $education->end_date = $this->formatDate($education->end_date, true);
                    return $education;
                });

                $cv->jobExperiences = $cv->jobExperiences->map(function ($jobExperience) {
                    $jobExperience->start_date = $this->formatDate($jobExperience->start_date);
                    $jobExperience->end_date = $this->formatDate($jobExperience->end_date, true);
                    return $jobExperience;
                });

                return $cv;
            });

        return response()->json([
            'first_name' => $seeker->first_name,
            'last_name' => $seeker->last_name,
            'email' => $seeker->email,
            'address' => $seeker->city ? $seeker->city->city_name : null, // Fetch city name as address
            'phonenumber' => $seeker->phonenumber,
            'saved_jobs' => $savedJobs,
            'applied_jobs' => $appliedJobs,
            'curriculum_vitae' => $curriculumVitae,
        ]);
    }

    /**
     * Get all seekers with their details, optionally filtered by search input.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAllSeekers(Request $request): JsonResponse
    {
        $query = Seeker::with(['city', 'savedJobs', 'appliedJobs', 'curriculumVitae']);

        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where(function($q) use ($searchTerm) {
                $q->where('first_name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('last_name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('email', 'like', '%' . $searchTerm . '%');
            });
        }

        $seekers = $query->get()->map(function ($seeker) {
            return [
                'first_name' => $seeker->first_name,
                'last_name' => $seeker->last_name,
                'email' => $seeker->email,
                'address' => $seeker->city ? $seeker->city->city_name : null,
                'phonenumber' => $seeker->phonenumber,
                'saved_jobs' => $seeker->savedJobs,
                'applied_jobs' => $seeker->appliedJobs->map(function ($appliedJob) {
                    return [
                        'job_id' => $appliedJob->job_id,
                        'status' => $appliedJob->status,
                        'curriculum_vitae_id' => $appliedJob->curriculum_vitae_id,
                        'cover_letter_id' => $appliedJob->cover_letter_id,
                        'created_at' => $appliedJob->created_at->toIso8601String(),
                    ];
                }),
                'curriculum_vitae' => $seeker->curriculumVitae->map(function ($cv) {
                    $cv->educations = $cv->educations->map(function ($education) {
                        $education->start_date = $this->formatDate($education->start_date);
                        $education->end_date = $this->formatDate($education->end_date, true);
                        return $education;
                    });

                    $cv->jobExperiences = $cv->jobExperiences->map(function ($jobExperience) {
                        $jobExperience->start_date = $this->formatDate($jobExperience->start_date);
                        $jobExperience->end_date = $this->formatDate($jobExperience->end_date, true);
                        return $jobExperience;
                    });

                    return $cv;
                }),
            ];
        });

        return response()->json($seekers);
    }

    /**
     * Format date to handle the special case of null or epoch date.
     *
     * @param  string|null  $date
     * @param  bool  $isEndDate
     * @return string
     */
    private function formatDate($date, $isEndDate = false)
    {
        if (!$date || Carbon::parse($date)->isSameDay(Carbon::createFromTimestamp(0))) {
            return $isEndDate ? 'until now' : null;
        }

        return Carbon::parse($date)->toDateString();
    }
}
