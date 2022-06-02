<?php

namespace App\Http\Controllers;

use App\Models\CourseBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseHistoryController extends Controller
{
    function purchaseHistory()
    {
        $courseBills = CourseBill::with([
            'course' => function ($q) {
                $q->withOnly(['coupon']);
            }
        ])
            ->orderBy('created_at', 'desc')
            ->where('user_id', Auth::user()->id)
            ->get();

        return response(['courseBills' => $courseBills]);
    }
}
