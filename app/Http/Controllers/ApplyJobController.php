<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AppliedJob;


class ApplyJobController extends Controller
{
    public function store(Request $request){

        $request->validate([
            'job_id' => 'required|exists:jobs,id',
            'seeker_id' => 'required|exists:job_seekers,id',
        ]);

        if (auth('sanctum')->user()) {
            $appliedJob = auth('sanctum')->user()->AppliedJob()->create([
                'job_id' => $request->job_id,
                'seeker_id' => $request->seeker_id,

            ]);
        }
        return response()->json(['message' => 'Job applied successfully', 'appliedJob' => $appliedJob], 201);

    }
}
