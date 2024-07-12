<?php

namespace App\Http\Controllers;
use App\Models\Provider;
use Illuminate\Http\Request;

class CompanyListController extends Controller
{
    public function companyList()
    {
        $companies = Provider::all();
        return response()->json($companies);
    }
}
