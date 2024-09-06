<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\JobPosting;
use App\Models\City;
use App\Models\Synonym;

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
        $cityName = trim($request->input('city'));
        $jobType = trim($request->input('job_type'));

        $cityDetails = $this->getCityDetails($cityName);
        $synonyms = $this->getSynonyms($searchTerm);

        $results = $this->searchJobs($searchTerm, $synonyms, $cityDetails, $jobType);

        if ($cityName && $searchTerm && $results->isEmpty()) {
            $results = $this->searchJobs($searchTerm, null, $cityDetails, $jobType);
        }

        // Paginate the results
        $perPage = 10;
        $currentPage = $request->input('page', 1);
        $paginatedJobs = $results->forPage($currentPage, $perPage);

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedJobs,
            $results->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $response = [
            'jobs' => $paginator->map(function ($job) {
                return [
                    'id' => $job->id,
                    'title' => $job->title,
                    'salary' => $job->salary,
                    'type' => $job->type,
                    'description' => $job->description,
                    'provider_city' => $job->provider->city->city_name ?? 'Unknown',
                    'provider_name' => $job->provider->company_name,
                    'job_skills' => $job->jobSkills->pluck('skill.name')->toArray(),
                    'matching_skills' => $job->matching_skills ?? [],
                    'matching_skills_count' => $job->matching_skills_count ?? 0,
                    'distance_from_input_city' => $job->distance_from_input_city,
                    'distance_from_seeker_city' => $job->distance_from_seeker_city,
                ];
            }),
            'current_page' => $paginator->currentPage(),
            'total_pages' => $paginator->lastPage(),
            'total_jobs' => $paginator->total(),
        ];

        return response()->json($response);
    }

    protected function getCityDetails(?string $cityName): ?array
    {
        if (!$cityName) {
            return null;
        }

        $city = City::where('city_name', 'like', '%' . $cityName . '%')->first();

        if ($city) {
            return [
                'city_name' => $city->city_name,
                'latitude' => $city->latitude,
                'longitude' => $city->longitude,
            ];
        }

        return null;
    }

    protected function getSynonyms(string $searchTerm): array
    {
        $synonyms = Synonym::where('title', $searchTerm)
            ->first();

        if ($synonyms) {
            return [
                $synonyms->title,
                $synonyms->synonym1,
                $synonyms->synonym2,
                $synonyms->synonym3,
                $synonyms->synonym4,
                $synonyms->synonym5,
            ];
        }

        return [];
    }

    protected function searchJobs(string $searchTerm, ?array $synonyms, ?array $cityDetails, ?string $jobType)
    {
        $jobsQuery = JobPosting::query()
            ->with(['provider.city', 'categories', 'jobskills.skill'])
            ->when($searchTerm || $synonyms, function ($query) use ($searchTerm, $synonyms) {
                $searchTerms = [$searchTerm];

                if ($synonyms) {
                    $searchTerms = array_merge($searchTerms, array_filter($synonyms));
                }

                $query->where(function ($query) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $query->orWhere('title', 'like', '%' . $term . '%')
                            ->orWhere('description', 'like', '%' . $term . '%')
                            ->orWhereHas('provider', function ($query) use ($term) {
                                $query->where('company_name', 'like', '%' . $term . '%')
                                    ->orWhereHas('city', function ($query) use ($term) {
                                        $query->where('city_name', 'like', '%' . $term . '%');
                                    });
                            })
                            ->orWhereHas('categories', function ($query) use ($term) {
                                $query->where('name', 'like', '%' . $term . '%');
                            })
                            ->orWhereHas('jobskills.skill', function ($query) use ($term) {
                                $query->where('name', 'like', '%' . $term . '%');
                            });
                    }
                });
            })
            ->when($jobType, function ($query, $jobType) {
                $query->where('type', 'like', '%' . $jobType . '%');
            });

        $jobs = $jobsQuery->get();

        if ($jobs->isEmpty()) {
            $jobs = collect();
        }

        return $jobs;
    }

    protected function calculateDistance(float $latitudeFrom, float $longitudeFrom, float $latitudeTo, float $longitudeTo): float
    {
        $earthRadius = 6371;

        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $longitudeFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }
}
