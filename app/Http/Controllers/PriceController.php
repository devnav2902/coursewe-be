<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Price;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PriceController extends Controller
{
    function getPrice()
    {
        $price = Price::get();

        return response()->json(['price' => $price]);
    }

    function updatePrice(Request $request)
    {
        $priceList = Price::select('id')->get()->pluck('id');

        $request->validate([
            'course_id' => 'required',
            'price_id' => ['required', Rule::in($priceList)]
        ]);

        $course_id = $request->input('course_id');
        $price_id = $request->input('price_id');
        Course::where('id', $course_id)
            ->where('author_id', Auth::user()->id)
            ->update(
                ['price_id' => $price_id]
            );

        return response(['success' => true]);
    }
}
