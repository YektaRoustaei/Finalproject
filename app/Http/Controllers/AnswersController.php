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
        if (!Auth::guard('sanctum')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $seeker_id = Auth::guard('sanctum')->id();

        $validated = $request->validate([
            'job_id' => 'required|exists:job_postings,id',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:questionnaires,id',
            'answers.*.answer' => 'required|string',
        ]);

        \DB::beginTransaction();
        try {
            foreach ($validated['answers'] as $answerData) {
                Answers::create([
                    'seeker_id' => $seeker_id,
                    'job_id' => $validated['job_id'],
                    'question_id' => $answerData['question_id'],
                    'answer' => $answerData['answer'],
                ]);
            }

            \DB::commit();

            return response()->json(['message' => 'Answers stored successfully'], 201);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['error' => 'An error occurred while storing answers'], 500);
        }
    }

    public function getAnswers(Request $request, $job_id, $seeker_id)
    {


        \Log::info('Job ID: ' . $job_id);
        \Log::info('Seeker ID: ' . $seeker_id);

        $answers = Answers::where('job_id', $job_id)
            ->where('seeker_id', $seeker_id)
            ->get();

        \Log::info('Answers: ', $answers->toArray());

        if ($answers->isEmpty()) {
            return response()->json(['message' => 'No answers found for this job'], 404);
        }

        return response()->json($answers, 200);
    }
}
