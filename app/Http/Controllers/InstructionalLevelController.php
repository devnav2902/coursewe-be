<?php

namespace App\Http\Controllers;

use App\Models\InstructionalLevel;

class InstructionalLevelController extends Controller
{
    function get()
    {
        $instructionalLevel = InstructionalLevel::get();

        return response()->json(compact(['instructionalLevel']));
    }
}
