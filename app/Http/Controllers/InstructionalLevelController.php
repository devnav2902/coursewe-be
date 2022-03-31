<?php

namespace App\Http\Controllers;

use App\Models\InstructionalLevel;
use Illuminate\Http\Request;

class InstructionalLevelController extends Controller
{
    function get()
    {
        $instructionalLevel = InstructionalLevel::get();

        return response()->json(compact(['instructionalLevel']));
    }
}
