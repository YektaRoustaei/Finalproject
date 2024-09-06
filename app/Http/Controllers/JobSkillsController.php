<?php

namespace App\Http\Controllers;

use App\Models\JobSkill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class JobSkillsController extends Controller
{
    public function jobskills()
    {
        try {
            $requirements = JobSkill::with('jobPosting')->get();
            return response()->json($requirements);
        } catch (\Exception $e) {
            Log::error('Error fetching requirements listings: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch requirements listings.'], 500);
        }
    }
}
