<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseBill;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OverviewController extends Controller
{
    function getOverview()
    {


        $instructorRevenuePercentage = 1;

        // INSTRUCTOR
        $allCoursesByInstructor = $this->allCoursesByInstructor();

        $totalStudents = Course::with('course_bill')
            ->where('author_id', Auth::user()->id)
            ->select('id')
            ->withCount('course_bill')
            ->get()
            ->sum("course_bill_count");

        $numberOfStudentsInMonth = $this->numberOfStudentsInMonth();
        $totalRevenue = $this->getCourseBill()->sum('purchase');
        $totalRevenue = $totalRevenue * $instructorRevenuePercentage;
        $totalRevenue = number_format($totalRevenue, 0, '.', '.');


        $totalRevenueInMonth = $this->getRevenueInMonth();
        $totalRevenueInMonth = $totalRevenueInMonth * $instructorRevenuePercentage;
        $totalRevenueInMonth = number_format($totalRevenueInMonth, 0, '.', '.');

        $ratingCourses = $this->getRatingByInstructorId()->avg('rating');
        $numberOfRatingsInMonth = $this->numberOfRatingsInMonth();

        // ADMIN
        $allCourses = Course::setEagerLoads([])->get(['id'])->count();

        $allCoursesInMonth = $this->getAllCoursesInMonth();
        $allInstructors = $this->getAllInstructors();
        DB::enableQueryLog();
        $allStudents = $this->getAllStudents();
        // dd(DB::getQueryLog());

        return response()->json(compact(["totalStudents", 'numberOfStudentsInMonth', 'totalRevenue', 'totalRevenueInMonth', 'ratingCourses', 'numberOfRatingsInMonth', 'allCourses', 'allInstructors', 'allStudents', 'allCoursesInMonth', 'allCoursesByInstructor']));
    }
    function getCourseBill()
    {
        return DB::table('course_bill')
            ->join('course', 'course.id', 'course_bill.course_id')
            ->select('purchase')
            ->where('course.author_id', Auth::user()->id);
    }

    function numberOfStudentsInMonth()
    {
        $cur_month = Carbon::now()->month;
        $cur_year = Carbon::now()->year;

        return CourseBill::whereHas('course', function ($q) {
            $q
                ->setEagerLoads([])
                ->where('author_id', Auth::user()->id);
        })
            ->whereMonth('created_at', $cur_month)
            ->whereYear('created_at', $cur_year)
            ->get()
            ->count();
    }

    function getRevenueInMonth()
    {
        $current_year = Carbon::now()->year;
        $current_month = Carbon::now()->month;

        return $this->getCourseBill()
            ->whereMonth('course_bill.created_at', $current_month)
            ->whereYear('course_bill.created_at',  $current_year)
            ->sum('purchase');
    }
    function getRatingByInstructorId()
    {
        return DB::table('course')
            ->join('rating', 'course.id', 'rating.course_id')
            ->where('author_id', Auth::user()->id)
            ->select('rating.rating');
    }

    function allCoursesByInstructor()
    {
        return Course::setEagerLoads([])
            ->where('author_id', Auth::user()->id)
            ->get(['id'])
            ->count();
    }

    function numberOfRatingsInMonth()
    {
        $current_year = Carbon::now()->year;
        $current_month = Carbon::now()->month;

        return $this
            ->getRatingByInstructorId()
            ->whereMonth('rating.created_at', $current_month)
            ->whereYear('rating.created_at',  $current_year)
            ->select('rating.rating')
            ->get()
            ->count('rating');
    }

    function chartJSYear(Request $request)
    {
        $request->validate([
            'year' => 'numeric|required',
            'currentMonth' => 'numeric'
        ]);

        $currentMonth = $request->input('currentMonth') ? $request->input('currentMonth') : 12;
        $monthRegistration = [];

        for ($i = 1; $i <= $currentMonth; $i++) {
            $total = $this->getCourseBill()
                ->whereYear('course_bill.created_at', $request->input('year'))
                ->WhereMonth('course_bill.created_at', $i)
                ->sum('purchase');

            $monthRegistration[] = $total;
        }

        return response()->json(['chartData' => $monthRegistration]);
    }
    function chartEnrollments(Request $request)
    {
        $request->validate([
            'year' => 'numeric|required',
            'currentMonth' => 'numeric'
        ]);

        $currentMonth = $request->input('currentMonth') ? $request->input('currentMonth') : 12;
        $monthRegistration = [];

        for ($i = 1; $i <= $currentMonth; $i++) {
            $total = $this->getCourseBill()
                ->whereYear('course_bill.created_at', $request->input('year'))
                ->WhereMonth('course_bill.created_at', $i)
                ->select('purchase')
                ->count('purchase');

            $monthRegistration[] = $total;
        }

        return response()->json(['chartEnrollments' => $monthRegistration]);
    }
    function chartRating(Request $request)
    {
        $request->validate([
            'year' => 'numeric|required',
            'currentMonth' => 'numeric'
        ]);

        $currentMonth = $request->input('currentMonth') ? $request->input('currentMonth') : 12;
        $monthRating = [];

        for ($i = 1; $i <= $currentMonth; $i++) {
            $total = $this->getRatingByInstructorId()
                ->whereYear('rating.created_at', $request->input('year'))
                ->WhereMonth('rating.created_at', $i)
                ->select(DB::raw("count(rating.rating) as count_student,avg(rating.rating) as avg_rating"))
                ->first();

            $monthRating[] = $total;
        }

        return response()->json(['chartRating' => $monthRating]);
    }

    public function getAllCoursesInMonth()
    {
        $current_month = Carbon::now()->month;
        $current_year = Carbon::now()->year;
        return Course::setEagerLoads([])
            ->whereMonth('created_at', $current_month)
            ->whereYear('created_at', $current_year)
            ->get(['id'])
            ->count();
    }
    public function getAllInstructors()
    {
        return User::setEagerLoads([])
            ->has('course')
            ->get(['id'])
            ->count();
    }

    public function getAllStudents()
    {
        return DB::table('course_bill')
            ->groupBy('user_id')
            ->get(['user_id'])
            ->count();
    }

    public function chartCourses(Request $request)
    {
        $request->validate([
            'year' => 'numeric|required',
            'currentMonth' => 'numeric'
        ]);

        $currentMonth = $request->input('currentMonth') ? $request->input('currentMonth') : 12;
        $coursesInMonth = [];

        for ($i = 1; $i <= $currentMonth; $i++) {
            $total = Course::setEagerLoads([])
                ->whereYear('created_at', $request->input('year'))
                ->WhereMonth('created_at', $i)
                ->get(['id'])
                ->count();

            $coursesInMonth[] = $total;
        }

        return response()->json(['chartCourses' => $coursesInMonth]);
    }
}
