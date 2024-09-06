<?php

namespace App\Http\Controllers;

use App\Models\Questionnaire;
use Illuminate\Http\Request;

class QuestionnaireController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'job_id' => 'required|exists:job_postings,id',
            'questions' => 'required|array',
            'questions.*.question' => 'required|string|max:255',
            'questions.*.answer_type' => 'required|in:string,int',
            'questions.*.min_value' => 'required|integer',
            'questions.*.max_value' => 'nullable|integer',
        ]);

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

        return response()->json(['message' => 'Questionnaires created successfully!', 'data' => $questionnaires], 201);
    }

    public function update(Request $request, $id)
    {
        $questionnaire = Questionnaire::findOrFail($id);

        $validatedData = $request->validate([
            'job_id' => 'required|exists:job_postings,id',
            'question' => 'required|string|max:255',
            'answer_type' => 'required|in:string,int',
            'min_value' => 'required|integer',
            'max_value' => 'nullable|integer',
        ]);

        $questionnaire->update($validatedData);

        return response()->json(['message' => 'Questionnaire updated successfully!', 'data' => $questionnaire], 200);
    }

    public function destroy($id)
    {
        $questionnaire = Questionnaire::findOrFail($id);

        $questionnaire->delete();

        return response()->json(['message' => 'Questionnaire deleted successfully!'], 200);
    }

    public function showQuestionsByJobId($jobId)
    {
        $validatedJobId = \Validator::make(['job_id' => $jobId], [
            'job_id' => 'required|exists:job_postings,id',
        ]);

        if ($validatedJobId->fails()) {
            return response()->json(['message' => 'Invalid job ID'], 400);
        }

        $questions = Questionnaire::where('job_id', $jobId)->get();

        return response()->json(['data' => $questions], 200);
    }
}
