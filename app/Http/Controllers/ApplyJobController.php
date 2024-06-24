<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AppliedJob;
use Illuminate\Support\Facades\App;

class ApplyJobController extends Controller
{
    public function apply(Request $request){
        $appliedJob = null;
        try {
            $seeker = auth()->user();
            $appliedJob = AppliedJob::create([
                'job_id' => $request->job_id,
                'seeker_id' => $seeker->id
            ]);
        } catch (\Exception $e) {
            report($e);
        }
        return response()->json(['message' => 'Job applied successfully', 'appliedJob' => $appliedJob], 200);

    }
}
