<?php

namespace App\Http\Controllers;

use App\Models\Questionnaire;
use Illuminate\Http\Request;

class QuestionnaireController extends Controller
{
    // Store a new questionnaire
    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'job_id' => 'required|exists:job_postings,id',
            'question' => 'required|string|max:255',
            'answer_type' => 'required|in:string,int',
            'min_value' => 'required|integer',
            'max_value' => 'nullable|integer',
        ]);

        // Create a new questionnaire
        $questionnaire = Questionnaire::create($validatedData);

        // Return a response
        return response()->json(['message' => 'Questionnaire created successfully!', 'data' => $questionnaire], 201);
    }

    // Update an existing questionnaire
    public function update(Request $request, $id)
    {
        // Find the questionnaire by ID
        $questionnaire = Questionnaire::findOrFail($id);

        // Validate the request data
        $validatedData = $request->validate([
            'job_id' => 'required|exists:job_postings,id',
            'question' => 'required|string|max:255',
            'answer_type' => 'required|in:string,int',
            'min_value' => 'required|integer',
            'max_value' => 'nullable|integer',
        ]);

        // Update the questionnaire
        $questionnaire->update($validatedData);

        // Return a response
        return response()->json(['message' => 'Questionnaire updated successfully!', 'data' => $questionnaire], 200);
    }

    // Delete a questionnaire
    public function destroy($id)
    {
        // Find the questionnaire by ID
        $questionnaire = Questionnaire::findOrFail($id);

        // Delete the questionnaire
        $questionnaire->delete();

        // Return a response
        return response()->json(['message' => 'Questionnaire deleted successfully!'], 200);
    }
}
