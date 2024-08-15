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
            'questions' => 'required|array',
            'questions.*.question' => 'required|string|max:255',
            'questions.*.answer_type' => 'required|in:string,int',
            'questions.*.min_value' => 'required|integer',
            'questions.*.max_value' => 'nullable|integer',
        ]);

        // Create questionnaires
        $questionnaires = [];
        foreach ($validatedData['questions'] as $questionData) {
            $questionnaires[] = Questionnaire::create([
                'job_id' => $validatedData['job_id'],
                'question' => $questionData['question'],
                'answer_type' => $questionData['answer_type'],
                'min_value' => $questionData['min_value'],
                'max_value' => $questionData['max_value'] ?? null,
            ]);
        }

        // Return a response
        return response()->json(['message' => 'Questionnaires created successfully!', 'data' => $questionnaires], 201);
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

    // Retrieve questions by job ID
    public function showQuestionsByJobId($jobId)
    {
        // Validate the job ID
        $validatedJobId = \Validator::make(['job_id' => $jobId], [
            'job_id' => 'required|exists:job_postings,id',
        ]);

        if ($validatedJobId->fails()) {
            return response()->json(['message' => 'Invalid job ID'], 400);
        }

        // Retrieve questions related to the job ID
        $questions = Questionnaire::where('job_id', $jobId)->get();

        // Return a response
        return response()->json(['data' => $questions], 200);
    }
}
