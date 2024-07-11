<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;


class CategoryListController extends Controller
{
    public function categoryList()
    {
        $categories = Category::all();
        return response()->json($categories);
    }
}
