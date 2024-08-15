<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Answers;

class AnswersController extends Controller
{
    /**
     * Store multiple answers for a job in storage.
     */
    public function store(Request $request)
    {
        // Check if the user is authenticated
        if (!Auth::guard('sanctum')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Retrieve the seeker_id from the authenticated user
        $seeker_id = Auth::guard('sanctum')->id();

        // Validate the incoming request
        $validated = $request->validate([
            'job_id' => 'required|exists:job_postings,id',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:questionnaires,id',
            'answers.*.answer' => 'required|string',
        ]);

        // Start a transaction to ensure all-or-nothing operation
        \DB::beginTransaction();
        try {
            // Iterate over the answers and store each one
            foreach ($validated['answers'] as $answerData) {
                Answers::create([
                    'seeker_id' => $seeker_id,
                    'job_id' => $validated['job_id'],
                    'question_id' => $answerData['question_id'],
                    'answer' => $answerData['answer'],
                ]);
            }

            // Commit the transaction
            \DB::commit();

            return response()->json(['message' => 'Answers stored successfully'], 201);

        } catch (\Exception $e) {
            // Rollback the transaction if something goes wrong
            \DB::rollBack();
            return response()->json(['error' => 'An error occurred while storing answers'], 500);
        }
    }

    public function getAnswers(Request $request, $job_id, $seeker_id)
    {


        // Debugging lines
        \Log::info('Job ID: ' . $job_id);
        \Log::info('Seeker ID: ' . $seeker_id);

        // Retrieve answers for the specified job and seeker
        $answers = Answers::where('job_id', $job_id)
            ->where('seeker_id', $seeker_id)
            ->get();

        // Debugging lines
        \Log::info('Answers: ', $answers->toArray());

        if ($answers->isEmpty()) {
            return response()->json(['message' => 'No answers found for this job'], 404);
        }

        return response()->json($answers, 200);
    }
}
