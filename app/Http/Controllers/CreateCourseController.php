<?php

namespace App\Http\Controllers;

use App\Models\Course;

use App\Models\Price;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CreateCourseController extends Controller
{
    function create(Request $request)
    {

        $request->validate([
            'title' => 'required|max:60'
        ]);

        $price_id = Price::firstWhere('original_price', 0)->id;

        $id = Course::insertGetId([
            'title' => $request->input('title'),
            'author_id' => Auth::user()->id,
            'price_id' => $price_id,
            'instructional_level_id' => 0,
        ]);

        return response(['status' => 'success', 'id' => $id]);
    }
}
