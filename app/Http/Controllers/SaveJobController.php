<?php

namespace App\Http\Controllers;

use App\Models\SavedJob;
use Illuminate\Http\Request;

class SaveJobController extends Controller
{
    public function save(Request $request){
        $savedJob = null;
        try {
            $seeker = auth()->user();
            $savedJob = SavedJob::create([
                'job_id' => $request->job_id,
                'seeker_id' => $seeker->id,
            ]);
        } catch (\Exception $e) {
            report($e);
        }
        return response()->json(['message' => 'Job Saved successfully', 'appliedJob' => $savedJob], 200);

    }
    //
}
class UnSaveJobController extends Controller
{
    public function unsave(Request $request){
        try {
            $seeker = auth()->user();
            $savedJob = SavedJob::where([
                'job_id' => $request->job_id,
                'seeker_id' => $seeker->id,
            ])->first();
                $savedJob->delete();
                return response()->json(['message' => 'Job Unsaved successfully'], 200);

        } catch (\Exception $e) {
            report($e);
            return response()->json(['message' => 'Error unsaving job', 'error' => $e->getMessage()], 500);
        }
    }
}
