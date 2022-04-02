<?php

namespace App\Http\Controllers;

use App\Models\Price;
use Illuminate\Http\Request;

class PriceController extends Controller
{
    function getPrice()
    {
        $price = Price::get();

        return response()->json(['price' => $price]);
    }
}
