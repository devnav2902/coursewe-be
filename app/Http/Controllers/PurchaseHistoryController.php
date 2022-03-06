<?php

namespace App\Http\Controllers;

use App\Models\CourseBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseHistoryController extends Controller
{
    function purchaseHistory()
    {
        $courseBill = CourseBill::with([
            'course' => function ($q) {
                $q->withOnly(['coupon']);
            }
        ])
            ->where('user_id', Auth::user()->id)
            ->get();

        return response(['courseBill' => $courseBill]);
    }
}
