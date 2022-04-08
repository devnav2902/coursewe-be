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

    function amountCoursesByInstructionalLevel($slug)
    {
        $helperController = new HelperController();
        $coursesBySlug = $helperController->getCoursesByCategorySlug($slug, false);

        $levels = InstructionalLevel::get();
        $levelInCourses = $coursesBySlug->pluck('instructional_level');
        $countCoursesByLevel = $levelInCourses->countBy('level');

        return $levels->transform(function ($level) use ($countCoursesByLevel) {
            $name = $level['level'];
            $amount = 0;

            $data = ['name' => $name, 'id' => $level['id'], 'amount' => $amount];

            if (isset($countCoursesByLevel[$name])) {
                $amount = $countCoursesByLevel[$name];
                $data['amount'] = $amount;
            }

            return $data;
        });
    }
}
