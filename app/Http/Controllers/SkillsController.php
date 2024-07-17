<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use Illuminate\Http\Request;

class SkillsController extends Controller
{
    public function allSkills()
    {
        try {
            // Fetch all skills without any relationships
            $skills = Skill::all();
            return response()->json($skills);
        } catch (\Exception $e) {
            \Log::error('Error fetching skill listings: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch skill listings.'], 500);
        }
    }



}
