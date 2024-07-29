<?php

namespace App\Http\Controllers;

use App\Models\CoverLetter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CoverLetterController extends Controller
{
    /**
     * Store a new cover letter.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'content' => 'nullable|string',
        ]);

        $user = Auth::guard('sanctum')->user();

        // Create a new CoverLetter
        $coverLetter = CoverLetter::create([
            'seeker_id' => $user->id,
            'content' => $request->input('content'),
        ]);

        return response()->json([
            'message' => 'Cover Letter created successfully',
            'cover_letter_id' => $coverLetter->id, // Return the ID of the newly created cover letter
        ], 201);
    }
}
