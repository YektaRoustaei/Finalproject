<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;

class CityListController extends Controller
{
    public function cities()
    {
        try {
            $cities = City::all();
            return response()->json($cities);
        } catch (\Exception $e) {
            \Log::error('Error fetching cities listings: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch cities listings.'], 500);
        }
    }

    public function cityStatistics()
    {
        try {
            $cities = City::withCount([
                'seekers',
                'seekers as applied_jobs_count' => function($query) {
                    $query->has('appliedJobs');
                },
                'jobPostings',
                'appliedJobs as accepted_jobs_count' => function ($query) {
                    $query->where('status', 'accepted');
                },
                'appliedJobs as rejected_jobs_count' => function ($query) {
                    $query->where('status', 'rejected');
                }
            ])
                ->get()
                ->filter(function ($city) {
                    return $city->seekers_count > 0 || $city->job_postings_count > 0;
                });

            return response()->json($cities);
        } catch (\Exception $e) {
            \Log::error('Error fetching city statistics: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch city statistics.'], 500);
        }
    }


    public function cityProviderCounts()
    {
        try {
            $cities = City::withCount('providers')->get();

            $filteredCities = $cities->filter(function ($city) {
                return $city->providers_count > 0;
            });

            return response()->json($filteredCities);
        } catch (\Exception $e) {
            \Log::error('Error fetching city provider counts: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch city provider counts.'], 500);
        }
    }

}
