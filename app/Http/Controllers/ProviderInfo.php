<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request; // Ensure this import is included
use App\Models\Provider; // Ensure you have a Provider model
use Illuminate\Database\Eloquent\Collection;
use App\Models\AppliedJob;


class ProviderInfo extends Controller
{
    /**
     * Get the authenticated provider's details.
     *
     * @return JsonResponse
     */
    public function __invoke(): JsonResponse
    {
        $provider = Auth::guard('sanctum')->user()->load('city');

        $jobCount = $provider->jobPostings()->count();

        $jobPostings = $provider->jobPostings()->with(['jobskills.skill', 'categories'])->get([
            'id',
            'title',
            'description',
            'salary',
            'type'
        ]);

        return response()->json([
            'company_name' => $provider->company_name,
            'description' => $provider->description,
            'address' => $provider->city->city_name,
            'telephone' => $provider->telephone,
            'email' => $provider->email,
            'id' => $provider->id,
            'job_count' => $jobCount,
            'jobs' => $jobPostings->map(function ($jobPosting) {
                return [
                    'id' => $jobPosting->id,
                    'title' => $jobPosting->title,
                    'description' => $jobPosting->description,
                    'salary' => $jobPosting->salary,
                    'type' => $jobPosting->type,
                    'skills' => $jobPosting->jobskills->map(function ($jobSkill) {
                        return $jobSkill->skill->name;
                    }),
                    'categories' => $jobPosting->categories->map(function ($category) {
                        return $category->title;
                    })
                ];
            }),
        ]);
    }

    /**
     * Get the list of all providers with their details.
     *
     * @return JsonResponse
     */
    public function getAllProviders(Request $request): JsonResponse
    {
        $searchTerm = $request->input('search', '');

        $providersQuery = Provider::with(['city', 'jobPostings.jobskills.skill', 'jobPostings.categories']);

        if (!empty($searchTerm)) {
            $providersQuery->where(function($query) use ($searchTerm) {
                $query->where('company_name', 'like', "%{$searchTerm}%")
                    ->orWhereHas('city', function($query) use ($searchTerm) {
                        $query->where('city_name', 'like', "%{$searchTerm}%");
                    });
            });
        }

        $providers = $providersQuery->get();

        $providersData = $providers->map(function ($provider) {
            $jobPostings = $provider->jobPostings;

            $jobStatuses = [
                'hold' => 0,
                'accepted' => 0,
                'rejected' => 0,
            ];

            foreach ($jobPostings as $jobPosting) {
                $appliedJobs = AppliedJob::where('job_id', $jobPosting->id)->get();
                foreach ($appliedJobs as $appliedJob) {
                    if (array_key_exists($appliedJob->status, $jobStatuses)) {
                        $jobStatuses[$appliedJob->status]++;
                    }
                }
            }

            return [
                'company_name' => $provider->company_name,
                'description' => $provider->description,
                'address' => $provider->city->city_name,
                'telephone' => $provider->telephone,
                'email' => $provider->email,
                'id' => $provider->id,
                'job_count' => $jobPostings->count(),
                'jobs' => $jobPostings->map(function ($jobPosting) {
                    return [
                        'id' => $jobPosting->id,
                        'title' => $jobPosting->title,
                        'description' => $jobPosting->description,
                        'salary' => $jobPosting->salary,
                        'type' => $jobPosting->type,
                        'skills' => $jobPosting->jobskills->map(function ($jobSkill) {
                            return $jobSkill->skill->name;
                        }),
                        'categories' => $jobPosting->categories->map(function ($category) {
                            return $category->title;
                        })
                    ];
                }),
                'job_statuses' => $jobStatuses,
            ];
        });

        return response()->json([
            'providers' => $providersData,
        ]);
    }
}
