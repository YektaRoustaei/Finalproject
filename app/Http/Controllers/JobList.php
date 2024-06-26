<?php

namespace App\Http\Controllers;

use App\Models\JobPosting;
use Illuminate\Http\Request;


class JobList extends Controller
{
    public function jobList()
    {
        $jobs = JobPosting::all();
        return response()->json($jobs);
    }
}
