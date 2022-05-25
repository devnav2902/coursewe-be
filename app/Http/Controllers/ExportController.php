<?php

namespace App\Http\Controllers;

use App\Exports\EnrollmentExport;
use App\Exports\RevenueExport;
use App\Http\Requests\PerformanceRequest;
use Maatwebsite\Excel\Facades\Excel;


class ExportController extends Controller
{
    function revenueExport(PerformanceRequest $request)
    {
        return (new RevenueExport($request))->download();
    }

    function enrollmentExport(PerformanceRequest $request)
    {
        $controller = new OverviewController();
        $enrollmentData = $controller->getEnrollments($request)->getData()->enrollmentData;

        if ($request->has('LTM')) {
            $month = $controller->month;
            $currentMonth = $controller->currentMonth;
            $year = $controller->year;
            $currentYear = $controller->currentYear;

            $filename =  $month . '-' . $year . '_' . $currentMonth . '-' . $currentYear . '-HOC_VIEN.xlsx';
        }

        if ($request->has('fromDate') && $request->has('toDate')) {
            $fromDate = $request->input('fromDate');
            $toDate = $request->input('toDate');
            $filename =  $fromDate . '_' . $toDate . '-HOC_VIEN.xlsx';
        }

        return Excel::download(new EnrollmentExport($enrollmentData), $filename);
    }
}
