<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\JobPosting;

class JobSearchController extends Controller
{
    /**
     * Search for job postings by title, provider company name, provider city, or job type.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $searchTerm = trim($request->input('search_term'));
        $city = trim($request->input('city'));
        $jobType = trim($request->input('job_type')); // New job_type parameter

        // If search parameters are provided, return search results
        if ($searchTerm || $city || $jobType) {
            return $this->searchJobs($searchTerm, $city, $jobType);
        }

        // If no search parameters are provided, return all jobs
        return $this->searchJobs(null, null, null);
    }

    /**
     * Perform job search based on provided parameters.
     *
     * @param string|null $searchTerm
     * @param string|null $city
     * @param string|null $jobType
     * @return JsonResponse
     */
    protected function searchJobs(?string $searchTerm, ?string $city, ?string $jobType): JsonResponse
    {
        $jobs = JobPosting::query()
            ->with(['provider.city', 'categories', 'jobskills.skill']) // Ensure skills relationship is included
            ->when($searchTerm, function ($query, $searchTerm) {
                $query->where('title', 'like', '%' . $searchTerm . '%')
                    ->orWhere('description', 'like', '%' . $searchTerm . '%')
                    ->orWhereHas('provider', function ($query) use ($searchTerm) {
                        $query->where('company_name', 'like', '%' . $searchTerm . '%')
                            ->orWhereHas('city', function ($query) use ($searchTerm) {
                                $query->where('city_name', 'like', '%' . $searchTerm . '%');
                            });
                    })
                    ->orWhereHas('categories', function ($query) use ($searchTerm) {
                        $query->where('name', 'like', '%' . $searchTerm . '%');
                    })
                    ->orWhereHas('jobskills.skill', function ($query) use ($searchTerm) {
                        $query->where('name', 'like', '%' . $searchTerm . '%'); // Match job skills
                    });
            })
            ->when($city, function ($query, $city) {
                $query->whereHas('provider.city', function ($query) use ($city) {
                    $query->where('city_name', 'like', '%' . $city . '%');
                });
            })
            ->when($jobType, function ($query, $jobType) {
                $query->where('type', 'like', '%' . $jobType . '%');
            })
            ->get()
            ->map(function ($job) {
                // Get categories
                $categories = $job->categories->filter()->map(function ($category) {
                    return $category ? $category->name : null; // Handle null categories
                })->filter()->toArray(); // Remove null values

                // Get skills
                $skills = $job->jobskills->filter()->map(function ($jobSkill) {
                    return $jobSkill->skill ? $jobSkill->skill->name : null; // Handle null skills
                })->filter()->toArray(); // Remove null values

                return [
                    'id' => $job->id,
                    'title' => $job->title,
                    'description' => $job->description,
                    'salary' => $job->salary,
                    'type' => $job->type,
                    'provider_id' => $job->provider_id,
                    'provider_name' => $job->provider ? $job->provider->company_name : null,
                    'provider_city' => $job->provider && $job->provider->city ? $job->provider->city->city_name : null,
                    'categories' => $categories,
                    'skills' => $skills, // Include skills
                    'created_at' => $job->created_at,
                    'updated_at' => $job->updated_at,
                ];
            });

        return response()->json($jobs);
    }
}
