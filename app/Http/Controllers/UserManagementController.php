<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    function instructorManagement()
    {
        $item = User::has('course')
            ->where('role_id', 2)
            ->with(['course' => function ($q) {
                $q->setEagerLoads([])
                    ->withSum('course_bill', 'purchase')
                    ->with(['categories:category_id,title'])
                    ->withCount('course_bill');
            }])
            ->withCount('course')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $item->getCollection()->transform(function ($item) {
            $item->revenue = $item->course->sum('course_bill_sum_purchase');
            $item->totalStudents = $item->course->sum('course_bill_count');
            return $item;
        });

        return response()->json(['items' => $item]);
    }
    function userManagement()
    {
        $item = User::where('role_id', 2)
            ->has('course_bill')
            ->withCount('course_bill')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json(['items' => $item]);
    }
}
