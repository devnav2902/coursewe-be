<?php

namespace App\Http\Controllers;

use App\Models\CategoriesCourse;
use App\Models\Course;
use App\Models\InstructionalLevel;
use App\Models\Price;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoriesController extends Controller
{

    private $helperController;

    function __construct()
    {
        $this->helperController = new HelperController();
    }

    function featuredCoursesByCategoryId($topLevelCategoryId)
    {
        $result = $this->helperController->categoryQueryBase()
            ->where('t1.category_id', $topLevelCategoryId)
            ->get();

        $topics_id = $this->helperController->getCategoriesIdToGetCourses($result);

        $queryGetCourses = Course::whereHas('categories', function ($query) use ($topics_id) {
            $query->whereIn('categories_course.category_id', $topics_id);
        })
            ->select('title', 'id', 'author_id', 'slug', 'price_id', 'thumbnail', 'created_at', 'instructional_level_id', 'subtitle')
            ->withCount(['course_bill', 'rating', 'section', 'lecture'])
            ->withAvg('rating', 'rating')
            ->having('rating_avg_rating', ">=", 4.0)
            ->with([
                'categories:category_id,parent_id,title,slug',
                'course_outcome:order,description,id,course_id',
                'course_requirements:order,description,id,course_id'
            ])
            ->take(10);

        return response()->json(['courses' => $queryGetCourses->get()]);
    }

    function featuredCourses($limit = 10)
    {
        $queryGetCourses = Course::select('title', 'id', 'author_id', 'slug', 'price_id', 'thumbnail', 'created_at', 'instructional_level_id', 'subtitle')
            ->with([
                'categories:category_id,parent_id,title,slug',
                'course_outcome:order,description,id,course_id',
                'course_requirements:order,description,id,course_id'
            ])
            ->withCount(['course_bill', 'rating', 'section', 'lecture'])
            ->without(['course_bill', 'rating'])
            ->withAvg('rating', 'rating')
            ->having('rating_avg_rating', '>=', 4.0)
            ->take($limit);

        $courses = $queryGetCourses->get();

        return response()->json(compact('courses'));
    }

    function featuredCategories($limit)
    {
        $queryGetCourses = CategoriesCourse::whereHas(
            'course',
            function ($q) {
                $q
                    ->setEagerLoads([])
                    ->select('id', 'title')
                    ->withAvg('rating', 'rating')
                    ->having('rating_avg_rating', '>=', 4.0);
            }
        );

        $featured_courses = $queryGetCourses->get();

        $categories_id = $featured_courses
            ->pluck('category_id')
            ->unique()
            ->values()
            ->toArray();
        $helperController = new HelperController();
        $topLevelCategories = $helperController->getTopLevelCategories($categories_id, $limit);

        return response()->json(
            [
                'topLevelCategories' => $topLevelCategories
            ]
        );
    }

    function getCoursesByCategorySlug(Request $request, $slug)
    {
        $groupedCategory = $this->helperController->groupedCategory($slug);

        DB::statement("SET sql_mode=''");
        $coursesQuery =
            Course::select('course.title', 'id', 'author_id', 'course.slug', 'price_id', 'thumbnail', 'course.created_at', 'instructional_level_id', 'subtitle')
            ->selectRaw('COUNT(categories.category_id) as Total')
            ->Join('categories_course', 'course.id', '=', 'categories_course.course_id')
            ->Join('categories', 'categories_course.category_id', '=', 'categories.category_id')
            ->withAvg('rating', 'rating')
            ->with(['author:role_id,fullname,slug,email,avatar,id'])
            ->withCount(['course_bill', 'rating', 'section', 'lecture']);

        $filterQuery = $this->filterCategory($groupedCategory, $coursesQuery, $request);
        $courses = $filterQuery->paginate(5);

        return response()->json(compact('courses'));
    }

    function getBreadcrumbByCategory($slug)
    {
        $breadcrumb = null;
        $groupedCategory = $this->helperController->groupedCategory($slug);
        // is topic
        if (count($groupedCategory) === 1) $breadcrumb = $groupedCategory[0];
        else {
            // is subcategory
            $uniqueSubcategory = collect($groupedCategory)->unique('subcategory_id');

            if (count($uniqueSubcategory) === 1) {
                $subcategory = $uniqueSubcategory->first();
                $breadcrumb = collect($subcategory)
                    ->except(['topic_id', 'level3_slug', 'level3_title']);
            } else {
                // is top level
                $topLevel = $uniqueSubcategory->unique('category_id')->first();

                $breadcrumb = collect($topLevel)->only(['level1_title', 'level1_slug', 'category_id']);
            }
        }

        return response()->json(compact('breadcrumb'));
    }

    function coursesBeginner(Request $request, $slug, $amount = 5)
    {
        $groupedCategory = $this->helperController->groupedCategory($slug);
        $categories_id = $this->helperController->getCategoriesIdToGetCourses($groupedCategory);

        $coursesBeginner = Course::select('title', 'id', 'author_id', 'slug', 'price_id', 'thumbnail', 'created_at', 'instructional_level_id', 'subtitle')
            ->withCount(['course_bill', 'rating', 'lecture'])
            ->without('course_outcome', 'course_requirements')
            ->with(['author:id,fullname,slug,email,avatar,role_id'])
            ->withAvg('rating', 'rating')
            ->whereHas('categories', function ($query) use ($categories_id) {
                $query->whereIn('categories.category_id', $categories_id);
            })
            ->whereHas('instructional_level', function ($q) {
                $q->where('level', 'Beginner Level');
            })
            ->paginate($amount);

        return response()->json(compact('coursesBeginner'));
    }

    private function filterCategory($groupedCategory, $coursesQuery, $request)
    {
        $categories_id = $this->helperController->getCategoriesIdToGetCourses($groupedCategory);

        $topics = $request->input('chu-de');
        $price = $request->input('gia-ban');
        $levels = $request->input('trinh-do');
        $rating = $request->input('danh-gia');

        if (is_numeric($rating)) {
            $coursesQuery->having('rating_avg_rating', '>=', $rating);
        }

        if ($this->helperController->isNumberStringWithCommas($topics)) {
            $arrayTopics = explode(',', $topics);

            if (count(array_intersect($arrayTopics, $categories_id))) {
                $coursesQuery
                    ->whereIn('categories.category_id', $arrayTopics)
                    ->having('Total', '=', count($arrayTopics));
            }
        } else {
            $coursesQuery
                ->whereIn('categories.category_id', $categories_id);
        }

        if ($this->helperController->isNumberStringWithCommas($levels)) {
            $arrayLevels = explode(',', $levels);
            $coursesQuery->whereIn('instructional_level_id', $arrayLevels);
        }

        if ($this->helperController->isStringWithCommas($price)) {
            $countPriceType = 0;
            $arrayPrice = explode(',', $price);
            $collectPrice = collect($arrayPrice);

            $freeTypeId = Price::select('id')->firstWhere('original_price', 0);
            $hasFreeType = $collectPrice->search('free');
            $hasPaidType = $collectPrice->search('paid');

            if (is_numeric($hasFreeType)) $countPriceType++;
            if (is_numeric($hasPaidType)) $countPriceType++;

            if ($countPriceType === 1) {
                is_numeric($hasFreeType)
                    ? $coursesQuery->where('price_id', $freeTypeId->id)
                    : $coursesQuery->where('price_id', '<>', $freeTypeId->id);
            }
        }

        return $coursesQuery->groupBy('course.id');
    }

    function discoveryUnits(Request $request, $slug)
    {

        $groupedCategory = $this->helperController->groupedCategory($slug);
        $categories_id = $this->helperController->getCategoriesIdToGetCourses($groupedCategory);

        $topics = $request->input('chu-de');
        $price = $request->input('gia-ban');
        $levels = $request->input('trinh-do');
        $rating = $request->input('danh-gia');

        $isValidTopics = $this->helperController->isNumberStringWithCommas($topics) ? true : false;
        $isValidRating = is_numeric($rating) ? true : false;
        $isValidLevels = $this->helperController->isNumberStringWithCommas($levels) ? true : false;
        $isValidPrice = $this->helperController->isStringWithCommas($price) ? true : false;

        $data =
            Course::setEagerLoads([])
            ->select('id', 'price_id', 'instructional_level_id')
            ->with('categories:category_id,title,slug', 'instructional_level', 'price')
            ->withAvg('rating', 'rating')
            ->whereHas('categories', function ($q) use ($categories_id) {
                $q->whereIn('categories.category_id', $categories_id);
            })
            ->get();

        // FILTER TOPIC
        if ($isValidTopics) {
            $arrayTopics = explode(',', $topics);
            $intersectCategories = array_intersect($arrayTopics, $categories_id);
            if (count($intersectCategories) === count($arrayTopics)) {
                $data = $data->map(function ($course) use ($arrayTopics) {
                    $categories_id = $course->categories->pluck('category_id')->toArray();
                    $intersect = array_intersect($arrayTopics, $categories_id);

                    if (count($intersect) === count($arrayTopics))
                        return $course;
                })
                    ->filter()
                    ->values();
            }
        }

        $original = $data;
        $data = $this->filterExcept($request, $data);

        $amountCoursesInTopics = $this->amountCoursesInTopics($this->filterExcept($request, $data));

        $filterRating = ($isValidLevels || $isValidPrice) && !$isValidRating
            ? $this->filterRating($data)
            : $this->filterRating($this->filterExcept($request, $original, 'rating'));

        $amountCoursesByTypesPrice =
            ($isValidRating || $isValidLevels) && !$isValidPrice
            ? $this->getAmountCoursesByTypesPrice($data)
            : $this->getAmountCoursesByTypesPrice($this->filterExcept($request, $original, 'price'));

        $amountCoursesByInstructionalLevel =
            ($isValidRating || $isValidPrice) && !$isValidLevels
            ? $this->amountCoursesByInstructionalLevel($data)
            : $this->amountCoursesByInstructionalLevel($this->filterExcept($request, $original, 'levels'));

        return response(compact(
            'amountCoursesInTopics',
            'filterRating',
            'amountCoursesByTypesPrice',
            'amountCoursesByInstructionalLevel'
        ));
    }

    private function filterExcept($request, $data, $except = '')
    {
        $price = $request->input('gia-ban');
        $levels = $request->input('trinh-do');
        $rating = $request->input('danh-gia');

        $isValidRating = is_numeric($rating) ? true : false;
        $isValidLevels = $this->helperController->isNumberStringWithCommas($levels) ? true : false;
        $isValidPrice = $this->helperController->isStringWithCommas($price) ? true : false;

        // RATING
        if ($isValidRating && $except !== 'rating') {
            $data = $data->map(function ($course) use ($rating) {
                if ($course->rating_avg_rating >= $rating)
                    return $course;
            })
                ->filter()
                ->values();
        }

        // LEVEL
        if ($isValidLevels && $except !== 'levels') {
            $arrayLevels = explode(',', $levels);
            $data = $data->whereIn('instructional_level_id', $arrayLevels)
                ->filter()
                ->values();
        }

        // PRICE
        if ($isValidPrice && $except !== 'price') {
            $countPriceType = 0;
            $arrayPrice = explode(',', $price);
            $collectPrice = collect($arrayPrice);

            $freeTypeId = Price::select('id')->firstWhere('original_price', 0);
            $hasFreeType = $collectPrice->search('free');
            $hasPaidType = $collectPrice->search('paid');

            if (is_numeric($hasFreeType)) $countPriceType++;
            if (is_numeric($hasPaidType)) $countPriceType++;

            if ($countPriceType === 1) {
                $data = is_numeric($hasFreeType)
                    ?  $data->where('price_id', $freeTypeId->id)
                    : $data->where('price_id', '<>', $freeTypeId->id);
            }
        }

        return $data;
    }

    private function amountCoursesInTopics($courses)
    {
        $arrCategories = collect($courses)->pluck('categories')->collapse();

        $counted = collect($arrCategories)->countBy(function ($category) {
            return $category['category_id'];
        });

        $unique = collect($arrCategories)->unique('category_id');

        $topicsWithCourses = $unique
            ->map(function ($category) use ($counted) {
                $category['amount'] = $counted[$category['category_id']];

                return $category;
            })
            ->values();

        return $topicsWithCourses;
    }

    private function filterRating($courses)
    {
        $ratingArr = collect($courses)->pluck('rating_avg_rating');

        $data = [
            '4.5' => ['amount' => 0],
            '4.0' => ['amount' => 0],
            '3.5' => ['amount' => 0],
            '3.0' => ['amount' => 0],
        ];

        foreach ($ratingArr as $valueRating) {
            $value = floatval($valueRating);

            if ($value >= 4.5) $data['4.5']['amount'] += 1;
            if ($value >= 4) $data['4.0']['amount'] += 1;
            if ($value >= 3.5) $data['3.5']['amount'] += 1;
            if ($value >= 3.0) $data['3.0']['amount'] += 1;
        }

        return $data;
    }

    private function getAmountCoursesByTypesPrice($courses)
    {
        $priceArr = collect($courses)->pluck('price');

        $amountCoursesByTypesPrice = [
            'free' => ['amount' => 0, 'type' => 'free'],
            'paid' => ['amount' => 0, 'type' => 'paid']
        ];

        foreach ($priceArr as $value) {
            if (intval($value['original_price']) !== 0)
                $amountCoursesByTypesPrice['paid']['amount'] += 1;
            else $amountCoursesByTypesPrice['free']['amount'] += 1;
        }

        return $amountCoursesByTypesPrice;
    }

    private function amountCoursesByInstructionalLevel($courses)
    {
        $amountCoursesByInstructionalLevel = InstructionalLevel::get();
        $levelInCourses = collect($courses)->pluck('instructional_level');
        $countCoursesByLevel = $levelInCourses->countBy('level');

        $amountCoursesByInstructionalLevel->transform(function ($level) use ($countCoursesByLevel) {
            $name = $level['level'];
            $amount = 0;

            $data = ['name' => $name, 'id' => $level['id'], 'amount' => $amount];

            if (isset($countCoursesByLevel[$name])) {
                $amount = $countCoursesByLevel[$name];
                $data['amount'] = $amount;
            }

            return $data;
        });

        return $amountCoursesByInstructionalLevel;
    }

    function getCategories()
    {
        $helperController = new HelperController();
        $category_query = $helperController->queryCategory("t1.parent_id IS NULL");

        $result = DB::select($category_query);

        $grouped_categories  = collect($result)
            ->groupBy(['level1', 'level2', 'level3']);

        $slug_categories = collect($result)->mapWithKeys(function ($item) {
            return [
                $item->level1 => $item->level1_slug,
                $item->level2 => $item->level2_slug,
                $item->level3 => $item->level3_slug,
            ];
        });

        $categories = [];

        foreach ($grouped_categories as $key_top_level => $top_level) {
            $slug_top_level = $slug_categories[$key_top_level];

            // subcategory(level 2)
            $data_subcategory = [];

            foreach ($top_level as $key => $subcategory) {
                // key can be null because level 3 may be not exist
                $array_keys_subcategory = array_keys($subcategory->all()); // get keys
                $arrayRemovedEmptyKey = array_filter($array_keys_subcategory); // remove "" key
                // topics(level 3)
                $topics =
                    collect($arrayRemovedEmptyKey)
                    ->map(function ($topic) use ($slug_categories) {
                        $dataTopic =  [
                            'name' => $topic,
                            'slug' => $slug_categories[$topic],
                        ];

                        return $dataTopic;
                    });
                $slug_subcategory = $slug_categories[$key];

                $data_topics = [
                    'name' => $key,
                    'slug' => $slug_subcategory,
                    'subcategory' => count($topics) ? $topics : null
                ];

                array_push($data_subcategory, $data_topics);
            }

            $data_top_level =  [
                'name' => $key_top_level,
                'slug' => $slug_top_level,
                'subcategory' => $data_subcategory
            ];

            array_push($categories, $data_top_level);
        }

        return response()->json(compact('categories'));
    }

    function getPopularInstructors($slug)
    {
        $controller = new HelperController();
        $categoryQueryBase = $controller->categoryQueryBase();
        $groupedCategory = $categoryQueryBase
            ->where(function ($q) use ($slug) {
                $q
                    ->where('t1.slug', '=', $slug)
                    ->orWhere('t2.slug', '=', $slug)
                    ->orWhere('t3.slug', '=', $slug);
            })
            ->get();

        $categories_id = $controller->getCategoriesIdToGetCourses($groupedCategory);

        // Lỗi ONLY_FULL_GROUP_BY => Xuất hiện column(select) kh nằm trong groupBy
        DB::statement("SET sql_mode=''");
        $authors = Course::setEagerLoads([])
            ->whereHas(
                'categories',
                function ($query) use ($categories_id) {
                    $query
                        ->select('categories.category_id', 'parent_id', 'title', 'slug')
                        ->whereIn('categories.category_id', $categories_id);
                }
            )
            ->select('author_id')
            ->withAvg('rating', 'rating')
            ->groupBy('author_id')
            ->having('rating_avg_rating', '>=', 4)
            ->take(10)
            ->get()
            ->pluck(['author_id']);

        $popularInstructors = User::with(
            [
                'course' => function ($q) use ($categories_id) {
                    $q
                        ->setEagerLoads([])
                        ->whereHas(
                            'categories',
                            function ($query) use ($categories_id) {
                                $query
                                    ->select('categories.category_id', 'parent_id')
                                    ->whereIn('categories.category_id', $categories_id);
                            }
                        )
                        ->select('id', 'author_id')
                        ->withCount(['course_bill'])
                        ->withAvg('rating', 'rating')
                        ->having('rating_avg_rating', '>=', 4);
                }
            ]
        )
            ->whereIn('id', $authors)
            ->get(["id", "role_id", "fullname", "slug", "avatar"]);

        $popularInstructors->transform(function ($author) {
            $author->totalStudents = $author->course->sum('course_bill_count');
            $avgCourses = $author->course->avg('rating_avg_rating');
            $author->roundedAvgCourses = round($avgCourses, 1);
            $author->numberOfCourses = $author->course->count();

            unset($author->course);
            return $author;
        });

        return response()->json(compact('popularInstructors'));
    }
}
