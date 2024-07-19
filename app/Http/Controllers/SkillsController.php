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

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:skills,name',
        ]);

        try {
            $skill = Skill::create(['name' => $request->name]);
            return response()->json($skill, 201);
        } catch (\Exception $e) {
            \Log::error('Error creating skill: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to create skill.'], 500);
        }
    }
}
