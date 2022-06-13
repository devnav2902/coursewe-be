<?php

namespace App\Http\Controllers;

use App\Http\Requests\PerformanceRequest;
use App\Models\CategoriesCourse;
use App\Models\Course;
use App\Models\CourseBill;
use App\Models\Rating;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OverviewController extends Controller
{
    private $dateFormat = 'Y-m-d';
    private $dateFormatWithoutDay = 'Y-m';
    public $lastTwelveMonths;
    public $month;
    public $year;
    public $currentMonth;
    public $currentYear;
    private $helper;

    function __construct()
    {
        $this->lastTwelveMonths = Carbon::now()->subMonths(12);
        $this->month = $this->lastTwelveMonths->month;
        $this->year = $this->lastTwelveMonths->year;
        $this->currentMonth = Carbon::now()->month;
        $this->currentYear = Carbon::now()->year;

        $this->helper = new HelperController;
    }

    function getOverview()
    {
        $instructorRevenuePercentage = 1;

        // INSTRUCTOR
        $allCoursesByInstructor = $this->allCoursesByInstructor();

        $baseQueryTotalStudents = Course::with('course_bill')
            ->select('id')
            ->withCount('course_bill');

        $totalStudents = ($this->helper)->isAdmin()
            ? $baseQueryTotalStudents->get()
            ->sum("course_bill_count")
            : $baseQueryTotalStudents
            ->where('author_id', Auth::user()->id)
            ->get()
            ->sum("course_bill_count");

        $numberOfStudentsInMonth = $this->numberOfStudentsInMonth();

        // INSTRUCTOR
        $totalRevenue = $this->baseQueryCourseBill()->sum('purchase');
        $totalRevenue = $totalRevenue * $instructorRevenuePercentage;
        $totalRevenue = number_format($totalRevenue, 0, '.', '.');

        $totalRevenueInMonth = $this->getRevenueInMonth();
        $totalRevenueInMonth = $totalRevenueInMonth * $instructorRevenuePercentage;
        $totalRevenueInMonth = number_format($totalRevenueInMonth, 0, '.', '.');

        $ratingCourses = $this->getRatingByInstructorId()->avg('rating') ?? 0;
        $numberOfRatingsInMonth = $this->numberOfRatingsInMonth();

        // ADMIN
        $allCourses = Course::setEagerLoads([])->get(['id'])->count();
        $allCoursesInMonth = $this->getAllCoursesInMonth();

        $allInstructors = $this->getAllInstructors();

        // dd(DB::getQueryLog());

        return response()->json(compact(["totalStudents", 'numberOfStudentsInMonth', 'totalRevenue', 'totalRevenueInMonth', 'ratingCourses', 'numberOfRatingsInMonth', 'allCourses', 'allInstructors', 'allCoursesInMonth', 'allCoursesByInstructor']));
    }

    private function baseQueryCourseBill($checkPermission = false)
    {

        if ($checkPermission && $this->helper->isAdmin()) {
            return CourseBill::orderBy('created_at', 'asc')
                ->select('purchase', 'course_id', 'created_at');
        }

        return CourseBill::whereHas('course', function ($q) {
            $q
                ->setEagerLoads([])
                ->where('author_id', Auth::user()->id)
                ->select('id', 'author_id', 'price_id');
        })
            ->orderBy('created_at', 'asc')
            ->select('purchase', 'course_id', 'created_at');
    }
    private function baseQueryRating()
    {
        return Rating::setEagerLoads([])
            ->with('course', function ($q) {
                $q
                    ->setEagerLoads([])
                    ->select('id')
                    ->withAvg('rating', 'rating');
            })
            ->orderBy('created_at', 'asc')
            ->where('user_id', Auth::user()->id)
            ->select('course_id', 'user_id', 'created_at', 'rating');
    }

    private function numberOfStudentsInMonth()
    {
        $baseQuery = null;
        if ($this->helper->isAdmin()) {
            $baseQuery = CourseBill::whereHas('course', function ($q) {
                $q->setEagerLoads([])->select('id');
            });
        } else {
            $baseQuery = CourseBill::whereHas('course', function ($q) {
                $q
                    ->setEagerLoads([])
                    ->select('author_id', 'id')
                    ->where('author_id', Auth::user()->id);
            });
        }

        return $baseQuery
            ->whereMonth('created_at', $this->currentMonth)
            ->whereYear('created_at', $this->currentYear)
            ->get()
            ->count();
    }

    private function getRevenueInMonth()
    {
        $current_year = Carbon::now()->year;
        $current_month = Carbon::now()->month;

        return $this->baseQueryCourseBill()
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

    // REVENUE
    private function revenueLTM()
    {
        $courseBill = $this->baseQueryCourseBill()
            ->whereDate('created_at', '>=', $this->lastTwelveMonths)
            ->whereDate('created_at', '<=', Carbon::now())
            ->get()
            ->map(function ($bill) {
                $bill->yearAndMonth = $bill->created_at->format($this->dateFormatWithoutDay);
                return $bill;
            });
        $groupedDate = $courseBill->groupBy('yearAndMonth');

        $carbonPeriod = CarbonPeriod::create(
            $this->lastTwelveMonths,
            '1 month',
            Carbon::now()
        );

        $carbonPeriod = collect($carbonPeriod)->map(function (Carbon $date) {
            return $date->format($this->dateFormatWithoutDay);
        });

        $data = collect($carbonPeriod)->map(function ($date) use ($groupedDate) {
            if (isset($groupedDate[$date])) {
                $dataByDate = $groupedDate[$date]->sum('purchase');
                return [
                    'date' => $date,
                    'revenue' => $dataByDate
                ];
            }
            return [
                'date' => $date,
                'revenue' => 0
            ];
        });

        return $data;
    }

    private function revenueByDateRange($fromDate, $toDate)
    {
        $courseBill = $this->baseQueryCourseBill()
            ->whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->get()
            ->map(function ($bill) {
                $bill->date_created = $bill->created_at->format($this->dateFormat);
                return $bill;
            });

        $carbonPeriod = CarbonPeriod::create($fromDate, $toDate);
        $groupedDate = $courseBill->groupBy('date_created');

        $data = collect($carbonPeriod)->map(function ($date) use ($groupedDate) {
            $formattedDate = $date->format($this->dateFormat);

            if (isset($groupedDate[$formattedDate])) {
                $dataByDate = $groupedDate[$formattedDate]->sum('purchase');
                return [
                    'date' => $formattedDate,
                    'revenue' => $dataByDate
                ];
            }
            return [
                'date' => $formattedDate,
                'revenue' => 0
            ];
        });

        return $data;
    }

    function getRevenue(PerformanceRequest $request)
    {
        if ($request->has('LTM')) {
            return response()->json(['revenueData' => $this->revenueLTM()]);
        }

        if ($request->has('fromDate') && $request->has('toDate')) {
            $fromDate = $request->input('fromDate');
            $toDate = $request->input('toDate');

            return response()->json(['revenueData' => $this->revenueByDateRange($fromDate, $toDate)]);
        }
    }

    // ENROLLMENT
    private function enrollmentLTM()
    {
        $courseBill = $this->baseQueryCourseBill(true)
            ->whereDate('created_at', '>=', $this->lastTwelveMonths)
            ->whereDate('created_at', '<=', Carbon::now())
            ->get()
            ->map(function ($bill) {
                $bill->yearAndMonth = $bill->created_at->format($this->dateFormatWithoutDay);
                return $bill;
            });
        $groupedDate = $courseBill->groupBy('yearAndMonth');

        $carbonPeriod = CarbonPeriod::create(
            $this->lastTwelveMonths,
            '1 month',
            Carbon::now()
        );

        $carbonPeriod = collect($carbonPeriod)->map(function (Carbon $date) {
            return $date->format($this->dateFormatWithoutDay);
        });

        $data = collect($carbonPeriod)->map(function ($date) use ($groupedDate) {
            if (isset($groupedDate[$date])) {
                $dataByDate = $groupedDate[$date]->count();
                return [
                    'date' => $date,
                    'total' => $dataByDate
                ];
            }
            return [
                'date' => $date,
                'total' => 0
            ];
        });

        return $data;
    }

    private function enrollmentByDateRange($fromDate, $toDate)
    {
        $courseBill = $this->baseQueryCourseBill(true)
            ->select('purchase', 'created_at')
            ->whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->get()
            ->map(function ($bill) {
                $bill->date_created = $bill->created_at->format($this->dateFormat);
                return $bill;
            });

        $carbonPeriod = CarbonPeriod::create($fromDate, $toDate);
        $groupedDate = $courseBill->groupBy('date_created');

        $data = collect($carbonPeriod)->map(function ($date) use ($groupedDate) {
            $formattedDate = $date->format($this->dateFormat);

            if (isset($groupedDate[$formattedDate])) {
                $dataByDate = $groupedDate[$formattedDate]->count();
                return [
                    'date' => $formattedDate,
                    'total' => $dataByDate
                ];
            }
            return [
                'date' => $formattedDate,
                'total' => 0
            ];
        });

        return $data;
    }

    function getEnrollments(PerformanceRequest $request)
    {
        if ($request->has('LTM')) {
            return response()->json(['enrollmentData' => $this->enrollmentLTM()]);
        }

        if ($request->has('fromDate') && $request->has('toDate')) {
            $fromDate = $request->input('fromDate');
            $toDate = $request->input('toDate');

            return response()->json(['enrollmentData' => $this->enrollmentByDateRange($fromDate, $toDate)]);
        }
    }

    // COURSE
    private function baseQueryGetCategoriesAndCourses($queryDate)
    {
        // parent_id can be lv2 or lv1
        $query = CategoriesCourse::with([
            'category' => function ($q) {
                $q->select('category_id', 'parent_id', 'title');
            },
            'course' => function ($q) use ($queryDate) {
                $q
                    ->setEagerLoads([])
                    ->select('created_at', 'id')
                    ->withSum([
                        'course_bill' => fn ($q) =>
                        $q
                            ->whereDate(
                                'created_at',
                                '>=',
                                $queryDate['fromDate']
                            )
                            ->whereDate('created_at', '<=', $queryDate['toDate'])
                    ], 'purchase');
            }
        ])
            ->select('category_id', 'course_id', 'created_at');

        return $query;
    }

    public function amountCoursesByCategory(PerformanceRequest $request)
    {
        $controller = new HelperController;

        $categories = $controller
            ->categoryQueryBase()
            ->get()
            ->except(["level3_title", "level3_slug", "topic_id"])
            ->unique('subcategory_id')
            ->values()
            ->toArray();

        $groupedByParentId = null;
        if ($request->has('LTM')) {
            $dateRange = ['fromDate' => $this->lastTwelveMonths, 'toDate' => Carbon::now()];
            $groupedByParentId = $this->baseQueryGetCategoriesAndCourses($dateRange);
        } else if ($request->has('fromDate') && $request->has('toDate')) {
            $fromDate = $request->input('fromDate');
            $toDate = $request->input('toDate');

            $dateRange = ['fromDate' => $fromDate, 'toDate' => $toDate];
            $groupedByParentId = $this->baseQueryGetCategoriesAndCourses($dateRange)
                ->whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $toDate);
        }

        // parent_id can be lv2 or lv1
        $groupedByParentId = $groupedByParentId
            ->get()
            ->groupBy('category.parent_id')
            ->map(function ($item) {
                return $item->unique('course_id')->values();
            });

        $dataWithCourses = [];

        collect($groupedByParentId)->each(function ($item, $parentCategoryId) use ($categories, &$dataWithCourses) {
            $existTopic = collect($categories)->where('subcategory_id', $parentCategoryId)->first();
            if ($existTopic) {
                $dataWithCourses[] = [
                    'category_id' => $existTopic->category_id,
                    'subcategory' => $existTopic->level2_title,
                    'courses' => $item
                ];
            } else {
                $firstItemToCheck = $item->first();
                $withoutTopicHasSubcategory = collect($categories)
                    ->where('category_id', $parentCategoryId)
                    ->where('subcategory_id', $firstItemToCheck->category_id)
                    ->first();

                if ($withoutTopicHasSubcategory) {
                    $dataWithCourses[] = [
                        'category_id' => $withoutTopicHasSubcategory->category_id,
                        'subcategory' => $withoutTopicHasSubcategory->level2_title,
                        'courses' => $item
                    ];
                }
            }
        });

        $groupedByCategoryId = collect($dataWithCourses)
            ->groupBy('category_id')
            ->map(function ($item) {
                $dataFlatten = $item->pluck('courses')->flatten();

                return [
                    'amountCourses' => $dataFlatten->count(),
                    'revenue' => $dataFlatten->sum('course.course_bill_sum_purchase')
                ];
            });

        $addedEmptyCategory = [];

        $uniqueCategory = collect($categories)->unique('category_id')->values();

        collect($uniqueCategory)->each(function ($item) use ($groupedByCategoryId, &$addedEmptyCategory) {
            $category_id = $item->category_id;
            $exist = isset($groupedByCategoryId[$category_id]);

            if ($exist) {
                $addedEmptyCategory[] = [
                    'amountCourses' => $groupedByCategoryId[$category_id]['amountCourses'],
                    'revenue' => $groupedByCategoryId[$category_id]['revenue'],
                    'category' => $item->level1_title
                ];
            } else {
                $addedEmptyCategory[] = [
                    'amountCourses' => 0,
                    'revenue' => 0,
                    'category' => $item->level1_title
                ];
            }
        });

        if (Auth::user()->role->name === 'admin')
            return response()->json(['amountCoursesByCategory' => $addedEmptyCategory]);
    }

    private function chartRatingLTM()
    {
        $rating = $this->baseQueryRating()
            ->whereMonth('created_at', '>=', $this->month)
            ->whereMonth('created_at', '<=', $this->currentMonth)
            ->whereYear('created_at', '>=', $this->year)
            ->whereYear('created_at', '<=', $this->currentYear)
            ->get()
            ->map(function ($bill) {
                $bill->yearAndMonth = $bill->created_at->format($this->dateFormatWithoutDay);
                return $bill;
            });


        $groupedDate = $rating->groupBy('yearAndMonth');

        $carbonPeriod = CarbonPeriod::create(
            $this->lastTwelveMonths,
            '1 month',
            Carbon::now()
        );

        $carbonPeriod = collect($carbonPeriod)->map(function (Carbon $date) {
            return $date->format($this->dateFormatWithoutDay);
        });

        $data = collect($carbonPeriod)->map(function ($date) use ($groupedDate) {
            if (isset($groupedDate[$date])) {
                $countRating = $groupedDate[$date]->count();
                $avgRatingByDate =  $groupedDate[$date]
                    ->unique('course_id')
                    ->values()
                    ->pluck('course.rating_avg_rating')
                    ->avg();
                return [
                    'date' => $date,
                    'avg_rating' => round($avgRatingByDate, 1),
                    'count_students' => $countRating
                ];
            }
            return [
                'date' => $date,
                'avg_rating' => 0,
                'count_students' => 0
            ];
        });
        return $data;
    }
    private function chartRatingByDateRange($fromDate, $toDate)
    {
        $rating = $this->baseQueryRating()

            ->whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->get()
            ->map(function ($bill) {
                $bill->date_created = $bill->created_at->format($this->dateFormat);
                return $bill;
            });

        $carbonPeriod = CarbonPeriod::create($fromDate, $toDate);
        $groupedDate = $rating->groupBy('date_created');

        $data = collect($carbonPeriod)->map(function ($date) use ($groupedDate) {
            $formattedDate = $date->format($this->dateFormat);

            if (isset($groupedDate[$formattedDate])) {
                $countRating = $groupedDate[$formattedDate]->count();
                $avgRatingByDate =  $groupedDate[$formattedDate]
                    ->unique('course_id')
                    ->values()
                    ->pluck('course.rating_avg_rating')
                    ->avg();
                return [
                    'date' => $formattedDate,
                    'avg_rating' => round($avgRatingByDate, 1),
                    'count_students' => $countRating
                ];
            }
            return [
                'date' => $formattedDate,
                'avg_rating' => 0,
                'count_students' => 0
            ];
        });
        return $data;
    }

    function getChartRating(PerformanceRequest $request)
    {
        if ($request->has('LTM')) {
            return response()->json(['chartRatingData' => $this->chartRatingLTM()]);
        }

        if ($request->has('fromDate') && $request->has('toDate')) {
            $fromDate = $request->input('fromDate');
            $toDate = $request->input('toDate');

            return response()->json(['chartRatingData' => $this->chartRatingByDateRange($fromDate, $toDate)]);
        }
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
}
